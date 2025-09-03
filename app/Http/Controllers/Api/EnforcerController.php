<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\Transaction;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EnforcerController extends Controller
{
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
     * Get all violators apprehended by this enforcer
     */
    public function getViolators(Request $request)
    {
        $user = $request->user();
        $violatorIds = $user->transactions()->pluck('violators_id')->unique();
        $violators = \App\Models\Violator::whereIn('id', $violatorIds)->get();
        return response()->json([
            'status' => 'success',
            'data' => $violators
        ]);
    }

    /**
     * Get all violation types
     */
    public function getViolationTypes()
    {
        $violations = Violation::all();

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
            ->where('first_name', 'LIKE', "%{$search}%")
            ->orWhere('last_name', 'LIKE', "%{$search}%")
            ->orWhere('license_number', 'LIKE', "%{$search}%")
            ->limit(5)
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
            'license_number'  => 'required|string|size:16',
            'violation_id'    => 'required|exists:violations,id',
            'location'        => 'required|string|max:100',
            'vehicle_type'    => 'required|in:Motor,Van,Motorcycle,Truck,Bus',
            'plate_number'    => 'required|string|max:10',
            'make'            => 'required|string|max:100',
            'model'           => 'required|string|max:100',
            'barangay'        => 'required|string|max:255',
            'city'            => 'required|string|max:255',
            'province'        => 'required|string|max:255',
            'owner_first_name'      => 'required|string|max:100',
            'owner_middle_name'     => 'nullable|string|max:100',
            'owner_last_name'       => 'required|string|max:100',
            'owner_barangay'        => 'required|string|max:255',
            'owner_city'            => 'required|string|max:255',
            'owner_province'        => 'required|string|max:255',
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
                    'password'    => null,
                ]
            );

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
                    'owner_barangay'     => $request->owner_barangay,
                    'owner_city'         => $request->owner_city,
                    'owner_province'     => $request->owner_province,
                    'vehicle_type' => $request->vehicle_type,
                ]
            );
            

            $violation = Violation::findOrFail($request->violation_id);

            $transaction = Transaction::create([
                'violator_id'         => $violator->id,
                'vehicle_id'          => $vehicle->id,
                'violation_id'        => $violation->id,
                'apprehending_officer' => auth()->id(),
                'status'              => 'Pending',
                'location'            => $request->location,
                'date_time'           => now(),
                'fine_amount'         => $violation->fine_amount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Violation recorded successfully',
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
            ->where('apprehending_officer', $user->id)
            ->orderBy('ticket_number', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Get specific transaction details
     */
    public function getTransaction($id)
    {
        $transaction = Transaction::with(['violator', 'violation', 'apprehendingOfficer'])
            ->findOrFail($id);

        // Check if the enforcer owns this transaction
        if ($transaction->apprehending_officer !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    /**
     * Update transaction (mark as paid)
     */
    public function updateTransaction(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Check if the enforcer owns this transaction
        if ($transaction->apprehending_officer !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Paid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction->status = $request->status;
        $transaction->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully',
            'data' => $transaction->load(['violator', 'violation'])
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

        $user->image = $imagePath;
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
} 