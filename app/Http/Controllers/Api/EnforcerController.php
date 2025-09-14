<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\POSUEmail;
use App\Models\Notification;
use App\Models\Enforcer;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\Transaction;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Traits\UserPermissionsTrait;
use Illuminate\Support\Facades\Mail;

class EnforcerController extends Controller
{
    use UserPermissionsTrait;

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = $request->identifier;
        $password   = $request->password;

        $enforcer = Enforcer::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if ($enforcer && Hash::check($password, $enforcer->password)) {
            if (!$enforcer->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive'
                ], 401);
            }

            $token = $enforcer->createToken('enforcer-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user'      => $enforcer,
                    'token'     => $token,
                    'user_type' => 'Enforcer'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user('sanctum');
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    
    /**
     * Get enforcer dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_apprehensions' => $user->transactions()->count(),
            'today_apprehensions' => $user->transactions()->whereDate('date_time', today())->count(),
            'pending_apprehensions' => $user->transactions()->where('status', 'Pending')->count(),
            'paid_apprehensions' => $user->transactions()->where('status', 'Paid')->count(),
        ];

        // Recent transactions
        $recentTransactions = $user->transactions()
            ->with(['violator', 'violation'])
            ->latest()
            ->limit(10)
            ->get();

        // Weekly performance
        $weeklyPerformance = $user->transactions()
            ->selectRaw('DATE(date_time) as date, COUNT(*) as count')
            ->whereBetween('date_time', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'recent_transactions' => $recentTransactions,
                'weekly_performance' => $weeklyPerformance,
            ]
        ]);
    }

    /**
     * Get all violation types
     */
    public function getViolationTypes()
    {
        $violations = Violation::orderBy('id', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $violations
        ]);
    }


    /**
     * Search violator by license number or plate number
     */
    public function searchViolator(Request $request)
{
    $search = $request->get('search');

    if (!$search || strlen($search) < 2) {
        return response()->json([
            'status' => 'error',
            'message' => 'Search term must be at least 2 characters'
        ], 422);
    }

    $violators = Violator::withCount('transactions')
        ->with('vehicles')
        ->where(function($query) use ($search) {
            $query->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('license_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('vehicles', function($q) use ($search) {
                      $q->where('plate_number', 'LIKE', "%{$search}%");
                  });
        })
        ->get();

    if ($violators->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'No violators found'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $violators
    ]);
}

    /**
     * Record a new violation
     */
    public function recordViolation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'      => 'required|string|max:100',
            'middle_name'     => 'nullable|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'nullable|email',
            'mobile_number'   => 'required|string|size:11',
            'professional'    => 'required|boolean',
            'gender'          => 'required|boolean',
            'license_number'  => 'required|string|size:11',
            'violation_id'    => 'required|exists:violations,id',
            'location'        => 'required|string|max:100',
            'vehicle_type'    => 'required|in:Motor,Motorcycle,Van,Car,SUV,Truck,Bus',
            'plate_number'    => 'required|string|max:7',
            'make'            => 'required|string|max:100',
            'model'           => 'required|string|max:100',
            'color'           => 'required|string|max:100', 
            'barangay'        => 'required|string|max:255',
            'city'            => 'required|string|max:255',
            'province'        => 'required|string|max:255',
            'owner_first_name'      => 'required|string|max:100',
            'owner_middle_name'     => 'nullable|string|max:100',
            'owner_last_name'       => 'required|string|max:100',
            'owner_barangay'        => 'required|string|max:255',
            'owner_city'            => 'required|string|max:255',
            'id_photo'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create or get violator
            $violator = Violator::firstOrCreate(
                ['license_number' => $request->license_number],
                [
                    'first_name'  => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name'   => $request->last_name,
                    'email'       => $request->email,
                    'gender'      => $request->gender,
                    'mobile_number' => $request->mobile_number,
                    'id_photo'    => $request->hasFile('id_photo')
                        ? $request->file('id_photo')->store('id_photos', 'public')
                        : null,
                    'barangay'     => $request->barangay,
                    'city'         => $request->city,
                    'province'     => $request->province,
                    'professional' => $request->professional,
                ]
            );

            // Create or get vehicle
            $vehicle = Vehicle::firstOrCreate(
                [
                    'plate_number' => $request->plate_number,
                    'violators_id' => $violator->id
                ],
                [
                    'owner_first_name'   => $request->owner_first_name,
                    'owner_middle_name'  => $request->owner_middle_name,
                    'owner_last_name'    => $request->owner_last_name,
                    'make'         => $request->make,
                    'model'        => $request->model,
                    'color'        =>$request->color,
                    'owner_barangay'     => $request->owner_barangay,
                    'owner_city'         => $request->owner_city,
                    'owner_province'     => $request->owner_province,
                    'vehicle_type' => $request->vehicle_type,
                ]
            );

            $violation = Violation::findOrFail($request->violation_id);

            // Create transaction
            $transaction = Transaction::create([
                'violator_id'          => $violator->id,
                'vehicle_id'           => $vehicle->id,
                'violation_id'         => $violation->id,
                'apprehending_officer' => auth()->id(),
                'status'               => 'Pending',
                'location'             => $request->location,
                'date_time'            => now(),
                'fine_amount'          => $violation->fine_amount,
            ]);

            $allowedRoles = ['System','Management','Head','Deputy','Admin','Enforcer','Violator'];
            $userType = class_basename(auth()->user());
            $senderRole = in_array($userType, $allowedRoles) ? $userType : 'System';
            $senderRole = ucfirst(strtolower($senderRole));
            
            // Create Notificaiton for Violator
            Notification::create([
                'sender_id'     => auth()->id(),
                'sender_role'   => $senderRole,
                'target_type'   => 'Violator',
                'target_id'     => $violator->id,
                'violator_id'   => $violator->id,
                'transaction_id'=> $transaction->id,
                'title'         => 'New Violation Recorded',
                'message'       => "You have been cited for {$violation->name}. Fine: ₱{$violation->fine_amount}. Please pay within 7 days to avoid penalties.",
                'type'          => 'info',
            ]);

            // Check offense count (for license suspension)
            $violationCount = $violator->transactions()->count();
            if ($violationCount >= 3 && !$violator->license_suspended_at) {
                $violator->license_suspended_at = now();
                $violator->save();

                // Notify Violator
                Notification::create([
                    'sender_id'   => auth()->id(),
                    'sender_role' => $senderRole,
                    'target_type' => 'Violator',
                    'violator_id' => $violator->id,
                    'target_id'   => $violator->id,
                    'title'       => 'License Suspension',
                    'message'     => "You now have {$violationCount} recorded violations. Your license is now subject to suspension.",
                    'type'        => 'alert',
                ]);

                // Notify Management (Head, Deputy, Admin)
                Notification::create([
                    'sender_id'   => auth()->id(),
                    'sender_role' => $senderRole,
                    'target_type' => 'Management',
                    'title'       => 'License Suspension Issued',
                    'message'     => "{$violator->first_name} {$violator->last_name} now has {$violationCount} recorded violations. Their license has been suspended.",
                    'type'        => 'alert',
                ]);
            }

             // Create Notificaiton for Head,Deputy,Admin
                Notification::create([
                    'sender_id'   => auth()->id(),
                    'sender_role' => $senderRole,
                    'target_type' => 'Management',
                    'title'       => 'Violation Recorded',
                    'message'     => "A new violation ({$violation->name}) was recorded for {$violator->first_name} {$violator->last_name}.",
                    'type'        => 'info',
                ]);

            // Create Notificaiton for Enforcer
            Notification::create([
                'sender_id'   => auth()->id(),
                'sender_role' => $senderRole,
                'target_type' => 'Enforcer',
                'target_id'   => auth()->id(),
                'title'       => 'Violation Successfully Recorded',
                'message'     => "You have successfully recorded a violation for {$violator->first_name} {$violator->last_name} ({$violation->name}).",
                'type'        => 'info',
            ]);

            // SEND CITATION EMAIL - ADD THIS SECTION RIGHT BEFORE DB::commit()
            if ($request->email) {
                try {
                    $violatorName = trim($violator->first_name . ' ' . ($violator->middle_name ? $violator->middle_name . ' ' : '') . $violator->last_name);
                    $vehicleInfo = $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->color . ')';
                    $violatorAddress = $violator->barangay . ', ' . $violator->city . ', ' . $violator->province;
                    
                    Mail::to($request->email)->send(
                        new POSUEmail('citation', [
                            'violator_name' => $violatorName,
                            'ticket_number' => $transaction->ticket_number ?? 'CT-' . date('Y') . '-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT),
                            'violation_type' => $violation->name,
                            'fine_amount' => $violation->fine_amount,
                            'violation_date' => $transaction->date_time->format('F j, Y'),
                            'violation_datetime' => $transaction->date_time->format('F j, Y - g:i A'),
                            'location' => $request->location,
                            'license_number' => $violator->license_number,
                            'vehicle_info' => $vehicleInfo,
                            'plate_number' => $vehicle->plate_number,
                            'violator_address' => $violatorAddress,
                        ])
                    );
                } catch (\Exception $emailError) {
                    // Log email error 
                    Log::error('Failed to send citation email: ' . $emailError->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Violation recorded successfully' . ($request->email ? ' and citation email sent' : ''),
                'data' => [
                    'transaction' => $transaction->load(['violator', 'vehicle', 'violation']),
                    'violator'    => $violator,
                    'vehicle'     => $vehicle
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record violation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enforcer's transactions 
     */
    public function getTransactions(Request $request)
    {
        $user = $request->user();

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $transactions = Transaction::with([
            'violator' => function ($q) {
                $q->withCount('transactions');
            },
            'violation',
            'vehicle'
        ])
            ->orderBy('ticket_number', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Get all violators with transactions
     */
    public function getViolators(Request $request)
    {
        $perPage = $request->input('per_page', 1000);
        $page = $request->input('page', 1);

        $violators = Violator::whereHas('transactions')
            ->withCount('transactions')
            ->with('vehicles')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => $violators
        ]);
    }

   
    /**
     * Get enforcer's performance statistics
     */
    public function getPerformanceStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_apprehensions' => $user->transactions()->count(),
            'this_month' => $user->transactions()->whereMonth('date_time', now()->month)->count(),
            'this_week' => $user->transactions()->whereBetween('date_time', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'today' => $user->transactions()->whereDate('date_time', today())->count(),
            'pending_count' => $user->transactions()->where('status', 'Pending')->count(),
            'paid_count' => $user->transactions()->where('status', 'Paid')->count(),
            'total_revenue' => $user->transactions()->where('status', 'Paid')->sum('fine_amount'),
        ];

        // Monthly performance for the last 6 months
        $monthlyPerformance = $user->transactions()
            ->selectRaw('YEAR(date_time) as year, MONTH(date_time) as month, COUNT(*) as count')
            ->whereBetween('date_time', [now()->subMonths(6), now()])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'monthly_performance' => $monthlyPerformance,
            ]
        ]);
    }
    /**
 * Get enforcer's profile
 */
public function getProfile(Request $request)
{
    $user = $request->user();

    return response()->json([
        'status' => 'success',
        'data' => [
            'id'          => $user->id,
            'first_name'  => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name'   => $user->last_name,
            'full_name'   => $user->full_name, // from accessor
            'username'    => $user->username,
            'email'       => $user->email,
            'image'       => $user->image_url, // from accessor
            'status'      => $user->status,
            'created_at'  => $user->created_at,
            'updated_at'  => $user->updated_at,
        ]
    ]);
}
   /**
 * Update enforcer's profile (name + image)
 */
public function updateProfile(Request $request)
{
    $validator = Validator::make($request->all(), [
        'first_name'   => 'required|string|max:100',
        'middle_name'  => 'nullable|string|max:100',
        'last_name'    => 'required|string|max:100',
        'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = $request->user();

    $user->first_name  = $request->first_name;
    $user->middle_name = $request->middle_name;
    $user->last_name   = $request->last_name;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('profile_images', 'public');

        $user->image = '/storage/' . $imagePath;
    }

    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'data' => $user
    ]);
}
        /**
     * Change enforcer's password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed', // requires new_password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update to new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }
    /**
     * Get enforcer's notifications
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        $includeDeleted = $request->input('include_deleted', false);

        $notificationsQuery = Notification::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'Enforcer')
                ->where('target_id', $user->id);
            })
            ->orWhere(function ($q) {
                $q->where('target_type', 'Enforcer')
                ->whereNull('target_id');
            })
            ->orWhere('target_type', 'All');
        })
        ->orderBy('created_at', 'desc')
        ->with('sender'); 

        if ($includeDeleted) {
            $notificationsQuery->withTrashed();
        }

        $notifications = $notificationsQuery->paginate($perPage, ['*'], 'page', $page);

        $unreadCount = Notification::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'Enforcer')
                ->where('target_id', $user->id);
            })
            ->orWhere(function ($q) {
                $q->where('target_type', 'Enforcer')
                ->whereNull('target_id');
            })
            ->orWhere('target_type', 'All');
        })
        ->whereNull('read_at')
        ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]
        ]);
    }


public function markNotificationAsRead(Request $request, $notificationId)
{
    $user = $request->user();
    
    $notification = Notification::where('id', $notificationId)
        ->where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'Enforcer')
                  ->where('target_id', $user->id);
            })->orWhere(function ($q) {
                $q->where('target_type', 'Enforcer')
                  ->whereNull('target_id');
            })->orWhere('target_type', 'All');
        })
        ->first();

    if (!$notification) {
        return response()->json([
            'status' => 'error',
            'message' => 'Notification not found'
        ], 404);
    }

    $notification->read_at = now();
    $notification->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Notification marked as read'
    ]);
}

public function markAllNotificationsAsRead(Request $request)
{
    $user = $request->user();
    
    Notification::where(function ($query) use ($user) {
        $query->where(function ($q) use ($user) {
            $q->where('target_type', 'Enforcer')
              ->where('target_id', $user->id);
        })->orWhere(function ($q) {
            $q->where('target_type', 'Enforcer')
              ->whereNull('target_id');
        })->orWhere('target_type', 'All');
    })
    ->whereNull('read_at')
    ->update(['read_at' => now()]);

    return response()->json([
        'status' => 'success',
        'message' => 'All notifications marked as read'
    ]);
}

public function deleteNotification(Request $request, $notificationId)
{
    $user = $request->user();
    
    $notification = Notification::where('id', $notificationId)
        ->where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'Enforcer')
                  ->where('target_id', $user->id);
            })->orWhere(function ($q) {
                $q->where('target_type', 'Enforcer')
                  ->whereNull('target_id');
            })->orWhere('target_type', 'All');
        })
        ->first();

    if (!$notification) {
        return response()->json([
            'status' => 'error',
            'message' => 'Notification not found'
        ], 404);
    }

    $notification->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Notification deleted successfully'
    ]);
}
public function restoreNotification(Request $request, $notificationId)
{
    $user = $request->user();

    // Include trashed to find soft-deleted notifications
    $notification = Notification::withTrashed()
        ->where('id', $notificationId)
        ->where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'Enforcer')
                  ->where('target_id', $user->id);
            })
            ->orWhere(function ($q) {
                $q->where('target_type', 'Enforcer')
                  ->whereNull('target_id');
            })
            ->orWhere('target_type', 'All');
        })
        ->first();

    if (!$notification) {
        return response()->json([
            'status' => 'error',
            'message' => 'Notification not found'
        ], 404);
    }

    $notification->restore();

    return response()->json([
        'status' => 'success',
        'message' => 'Notification restored'
    ]);
}
} 