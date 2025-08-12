<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $search = $request->search;

        $violator = Violator::where('license_number', $search)
            ->orWhere('plate_number', $search)
            ->orWhere('email', $search)
            ->with(['transactions' => function($query) {
                $query->latest();
            }])
            ->first();

        if (!$violator) {
            return response()->json([
                'status' => 'error',
                'message' => 'Violator not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'violator' => [
                    'id' => $violator->id,
                    'first_name' => $violator->first_name,
                    'middle_name' => $violator->middle_name,
                    'last_name' => $violator->last_name,
                    'full_name' => $violator->full_name,
                    'email' => $violator->email,
                    'mobile_number' => $violator->mobile_number,
                    'license_number' => $violator->license_number,
                    'plate_number' => $violator->plate_number,
                    'model' => $violator->model,
                    'address' => $violator->address,
                    'total_fines' => $violator->total_fines,
                    'unpaid_fines' => $violator->unpaid_fines,
                    'is_repeat_offender' => $violator->isRepeatOffender(),
                    'violation_history' => $violator->transactions,
                ]
            ]
        ]);
    }

    /**
     * Record a new violation
     */
    public function recordViolation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'mobile_number' => 'required|string|size:11',
            'license_number' => 'required|string|max:20',
            'plate_number' => 'required|string|max:20',
            'model' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'gender' => 'required|boolean',
            'violations_id' => 'required|exists:violations,id',
            'location' => 'required|string|max:100',
            'vehicle_type' => 'required|in:Motor,Van,Motorcycle',
            'id_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // id_photo instead of receipt
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if violator already exists by email or mobile number
            $violator = null;
            if ($request->email) {
                $violator = Violator::where('email', $request->email)->first();
            }
            if (!$violator) {
                $violator = Violator::where('mobile_number', $request->mobile_number)->first();
            }

            if (!$violator) {
                // Create new violator record
                $violator = Violator::create([
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'mobile_number' => $request->mobile_number,
                    'license_number' => $request->license_number,
                    'plate_number' => $request->plate_number,
                    'model' => $request->model,
                    'address' => $request->address,
                    'gender' => $request->gender,
                    'id_photo' => $request->hasFile('id_photo') ? $request->file('id_photo')->store('id_photos', 'public') : null,
                    'password' => null
                ]);
            } else {
                // Update existing violator information
                $updateData = [
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'mobile_number' => $request->mobile_number,
                    'license_number' => $request->license_number,
                    'plate_number' => $request->plate_number,
                    'model' => $request->model,
                    'address' => $request->address,
                    'gender' => $request->gender
                ];
                if ($request->email) {
                    $updateData['email'] = $request->email;
                }
                $violator->update($updateData);
            }

            // Handle id_photo upload (replace receipt logic)
            if ($request->hasFile('id_photo')) {
                $idPhotoPath = $request->file('id_photo')->store('id_photos', 'public');
                $violator->update(['id_photo' => $idPhotoPath]);
            }

            // Get violation details
            $violation = Violation::findOrFail($request->violations_id);

            // Create transaction
            $transaction = Transaction::create([
                'violators_id' => $violator->id,
                'violations_id' => $request->violations_id,
                'apprehending_officer' => auth()->id(),
                'status' => 'Pending',
                'location' => $request->location,
                'date_time' => now(),
                'fine_amount' => $violation->fine_amount,
                'vehicle_type' => $request->vehicle_type
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Violation recorded successfully',
                'data' => [
                    'transaction' => $transaction->load(['violator', 'violation']),
                    'violator' => $violator
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
        
        $query = $user->transactions()->with(['violator', 'violation']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('date_time', $request->date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('violator', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(15);

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
} 