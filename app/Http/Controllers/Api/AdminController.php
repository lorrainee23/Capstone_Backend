<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_violators' => Violator::count(),
            'total_transactions' => Transaction::count(),
            'pending_transactions' => Transaction::where('status', 'Pending')->count(),
            'paid_transactions' => Transaction::where('status', 'Paid')->count(),
            'total_revenue' => Transaction::where('status', 'Paid')->sum('fine_amount'),
            'pending_revenue' => Transaction::where('status', 'Pending')->sum('fine_amount'),
            'repeat_offenders' => Violator::whereHas('transactions', function($query) {
                $query->havingRaw('COUNT(*) > 1');
            })->count(),
            'active_enforcers' => User::where('role', 'Enforcer')->where('status', 'active')->count(),
        ];

        // Weekly violation trends
        $weeklyTrends = Transaction::selectRaw('DATE(date_time) as date, COUNT(*) as count')
            ->whereBetween('date_time', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most common violations
        $commonViolations = Violation::withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(5)
            ->get();

        // Enforcer performance
        $enforcerPerformance = User::where('role', 'Enforcer')
            ->withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'weekly_trends' => $weeklyTrends,
                'common_violations' => $commonViolations,
                'enforcer_performance' => $enforcerPerformance,
            ]
        ]);
    }

    /**
     * Get all users (Admin and Enforcers)
     */
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,Enforcer',
            'status' => 'required|in:active,inactive,deactivate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:Admin,Enforcer',
            'status' => 'required|in:active,inactive,deactivate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'first_name', 'middle_name', 'last_name', 'email', 'role', 'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get all violations
     */
    public function getViolations()
    {
        $violations = Violation::withCount('transactions')->get();

        return response()->json([
            'status' => 'success',
            'data' => $violations
        ]);
    }

    /**
     * Create violation type
     */
    public function createViolation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'fine_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $violation = Violation::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Violation type created successfully',
            'data' => $violation
        ], 201);
    }

    /**
     * Update violation type
     */
    public function updateViolation(Request $request, $id)
    {
        $violation = Violation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'fine_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $violation->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Violation type updated successfully',
            'data' => $violation
        ]);
    }

    /**
     * Delete violation type
     */
    public function deleteViolation($id)
    {
        $violation = Violation::findOrFail($id);
        
        // Check if violation has transactions
        if ($violation->transactions()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete violation type that has associated transactions'
            ], 400);
        }

        $violation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Violation type deleted successfully'
        ]);
    }

    /**
     * Get repeat offenders
     */
    public function getRepeatOffenders()
    {
        $repeatOffenders = Violator::withCount('transactions')
            ->having('transactions_count', '>', 1)
            ->with(['transactions' => function($query) {
                $query->latest();
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $repeatOffenders
        ]);
    }

    /**
     * Generate reports
     */
    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:violations,enforcers,revenue,offenders',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        switch ($request->type) {
            case 'violations':
                $data = Violation::withCount(['transactions' => function($query) use ($startDate, $endDate) {
                    $query->whereBetween('date_time', [$startDate, $endDate]);
                }])->get();
                break;

            case 'enforcers':
                $data = User::where('role', 'Enforcer')
                    ->withCount(['transactions' => function($query) use ($startDate, $endDate) {
                        $query->whereBetween('date_time', [$startDate, $endDate]);
                    }])
                    ->get();
                break;

            case 'revenue':
                $data = Transaction::whereBetween('date_time', [$startDate, $endDate])
                    ->selectRaw('DATE(date_time) as date, SUM(fine_amount) as total_revenue, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'offenders':
                $data = Violator::withCount(['transactions' => function($query) use ($startDate, $endDate) {
                    $query->whereBetween('date_time', [$startDate, $endDate]);
                }])
                ->having('transactions_count', '>', 0)
                ->orderBy('transactions_count', 'desc')
                ->get();
                break;
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Send notification
     */
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_role' => 'required|in:Admin,Enforcer,Violator',
            'title' => 'required|string|max:100',
            'message' => 'required|string',
            'type' => 'required|in:info,alert,reminder,warning',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = Notification::create([
            'sender_id' => $request->user()->id,
            'sender_role' => $request->user()->role,
            'target_role' => $request->target_role,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent successfully',
            'data' => $notification
        ], 201);
    }
} 