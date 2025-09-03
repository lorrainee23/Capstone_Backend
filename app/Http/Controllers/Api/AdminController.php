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

class AdminController extends Controller
{
    /**
     * Dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_violators'      => Violator::count(),
            'total_transactions'   => Transaction::count(),
            'pending_transactions' => Transaction::where('status', 'Pending')->count(),
            'paid_transactions'    => Transaction::where('status', 'Paid')->count(),
            'total_revenue'        => Transaction::where('status', 'Paid')->sum('fine_amount'),
            'pending_revenue'      => Transaction::where('status', 'Pending')->sum('fine_amount'),
            'repeat_offenders'     => Violator::withCount('transactions')
                                        ->having('transactions_count', '>', 1)
                                        ->count(),
            'active_enforcers'     => User::where('role', 'Enforcer')
                                        ->where('status', 'active')
                                        ->count(),
        ];

        $weeklyTrends = Transaction::selectRaw('DATE(date_time) as date, COUNT(*) as count')
            ->whereBetween('date_time', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $commonViolations = Violation::withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(5)
            ->get();

        $enforcerPerformance = User::where('role', 'Enforcer')
            ->withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'stats'                => $stats,
                'weekly_trends'        => $weeklyTrends,
                'common_violations'    => $commonViolations,
                'enforcer_performance' => $enforcerPerformance,
            ]
        ]);
    }

    /* ==============================
     * USERS MANAGEMENT
     * ============================== */
    
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
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:Admin,Enforcer',
            'status'      => 'required|in:active,inactive,deactivate',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'status'      => $request->status,
        ]);

        return response()->json(['status' => 'success', 'message' => 'User created', 'data' => $user], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $id,
            'role'        => 'required|in:Admin,Enforcer',
            'status'      => 'required|in:active,inactive,deactivate',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['first_name', 'middle_name', 'last_name', 'email', 'role', 'status']));

        return response()->json(['status' => 'success', 'message' => 'User updated', 'data' => $user]);
    }

    public function archiveUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['status' => 'success', 'message' => 'User archived']);
    }

    public function getArchivedUsers(Request $request)
    {
        $query = User::onlyTrashed();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['status' => 'success', 'message' => 'User restored', 'data' => $user]);
    }

    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['status' => 'success', 'message' => 'User permanently deleted']);
    }

    /* ==============================
     * VIOLATORS MANAGEMENT
     * ============================== */

    public function getViolators(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $page    = $request->input('page', 1);

        $violators = Violator::whereHas('transactions')
            ->withCount('transactions')
            ->withSum('transactions', 'fine_amount')
            ->with(['transactions.violation', 'transactions.vehicle', 'transactions.apprehendingOfficer'])
            ->paginate($perPage, ['*'], 'page', $page);

        $violators->getCollection()->transform(function ($violator) {
            $violator->total_amount = $violator->transactions_sum_fine_amount ?? 0;
            unset($violator->transactions_sum_fine_amount);
            return $violator;
        });

        return response()->json(['status' => 'success', 'data' => $violators]);
    }
    public function updateViolator(Request $request)
{   
    $id = $request->id;
    $violator = Violator::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'first_name'     => 'required|string|max:255',
        'middle_name'    => 'nullable|string|max:255',
        'last_name'      => 'required|string|max:255',
        'email'          => 'nullable|email|unique:violators,email,' . $id,
        'mobile_number'  => 'required|string|max:20',
        'gender'         => 'required|boolean',
        'license_number' => 'nullable|string|max:50',
        'barangay'       => 'nullable|string|max:100',
        'city'           => 'nullable|string|max:100',
        'province'       => 'nullable|string|max:100',
        'professional'   => 'nullable|boolean',
        'payment_status' => 'nullable|in:Pending,Paid',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

    // Update violator fields
    $violator->update($request->only([
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'mobile_number',
        'gender',
        'license_number',
        'barangay',
        'city',
        'province',
        'professional',
    ]));

    $violator['gender'] = (bool) $violator['gender'];
    $violator['professional'] = (bool) $violator['professional'];

    if ($request->has('status')) {
        $violator->transactions()->update(['status' => $request->status]);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Violator and all transactions updated successfully',
        'data' => $violator
    ]);
}
    public function archiveViolator($id)
    {
        $violator = Violator::findOrFail($id);
        $violator->delete();

        return response()->json(['status' => 'success', 'message' => 'Violator archived']);
    }

    public function getArchivedViolators()
    {
        $violators = Violator::onlyTrashed()->withCount('transactions')->get();

        return response()->json(['status' => 'success', 'data' => $violators]);
    }

    public function restoreViolator($id)
    {
        $violator = Violator::onlyTrashed()->findOrFail($id);
        $violator->restore();

        return response()->json(['status' => 'success', 'message' => 'Violator restored', 'data' => $violator]);
    }

    public function forceDeleteViolator($id)
    {
        $violator = Violator::onlyTrashed()->findOrFail($id);
        $violator->forceDelete();

        return response()->json(['status' => 'success', 'message' => 'Violator permanently deleted']);
    }

    /* ==============================
     * VIOLATIONS MANAGEMENT
     * ============================== */

    public function getViolations()
    {
        $violations = Violation::withCount('transactions')->get();

        return response()->json(['status' => 'success', 'data' => $violations]);
    }

    public function createViolation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'description' => 'required|string',
            'fine_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $violation = Violation::create($request->all());

        return response()->json(['status' => 'success', 'message' => 'Violation created', 'data' => $violation], 201);
    }

    public function updateViolation(Request $request, $id)
    {
        $violation = Violation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'description' => 'required|string',
            'fine_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $violation->update($request->all());

        return response()->json(['status' => 'success', 'message' => 'Violation updated', 'data' => $violation]);
    }

    public function archiveViolation($id)
    {
        $violation = Violation::findOrFail($id);
        $violation->delete();

        return response()->json(['status' => 'success', 'message' => 'Violation archived']);
    }

    public function getArchivedViolations()
    {
        $violations = Violation::onlyTrashed()->withCount('transactions')->get();

        return response()->json(['status' => 'success', 'data' => $violations]);
    }

    public function restoreViolation($id)
    {
        $violation = Violation::onlyTrashed()->findOrFail($id);
        $violation->restore();

        return response()->json(['status' => 'success', 'message' => 'Violation restored', 'data' => $violation]);
    }

    public function forceDeleteViolation($id)
    {
        $violation = Violation::onlyTrashed()->findOrFail($id);

        if ($violation->transactions()->count() > 0) {
            return response()->json(['status' => 'error', 'message' => 'Cannot permanently delete violation with transactions'], 400);
        }

        $violation->forceDelete();

        return response()->json(['status' => 'success', 'message' => 'Violation permanently deleted']);
    }

    /* ==============================
     * TRANSACTIONS & REPORTS
     * ============================== */

    public function getTransactions(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $page    = $request->input('page', 1);

        $transactions = Transaction::with([
            'violator' => function ($q) {
                $q->withCount('transactions');
            },
            'violation',
            'vehicle',
            'apprehendingOfficer'
        ])
            ->orderBy('id', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $totalsByViolator = Transaction::selectRaw('violator_id, SUM(fine_amount) as total_amount')
            ->groupBy('violator_id')
            ->pluck('total_amount', 'violator_id');

        $transactions->getCollection()->transform(function ($transaction) use ($totalsByViolator) {
            if ($transaction->violator) {
                $transaction->violator->total_amount = $totalsByViolator[$transaction->violator->id] ?? 0;
            }
            return $transaction;
        });

        return response()->json(['status' => 'success', 'data' => $transactions]);
    }

    public function getRepeatOffenders()
    {
        $repeatOffenders = Violator::withCount('transactions')
            ->having('transactions_count', '>', 1)
            ->with(['transactions' => function ($query) {
                $query->latest();
            }])
            ->get();

        return response()->json(['status' => 'success', 'data' => $repeatOffenders]);
    }

    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'       => 'required|in:violations,enforcers,revenue,offenders',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        switch ($request->type) {
            case 'violations':
                $data = Violation::withCount(['transactions' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date_time', [$startDate, $endDate]);
                }])->get();
                break;

            case 'enforcers':
                $data = User::where('role', 'Enforcer')
                    ->withCount(['transactions' => function ($query) use ($startDate, $endDate) {
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
                $data = Violator::withCount(['transactions' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date_time', [$startDate, $endDate]);
                }])
                ->having('transactions_count', '>', 0)
                ->orderBy('transactions_count', 'desc')
                ->get();
                break;
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /* ==============================
     * NOTIFICATIONS
     * ============================== */

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_role' => 'required|in:Admin,Enforcer,Violator',
            'title'       => 'required|string|max:100',
            'message'     => 'required|string',
            'type'        => 'required|in:info,alert,reminder,warning',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $notification = Notification::create([
            'sender_id'   => $request->user()->id,
            'sender_role' => $request->user()->role,
            'target_role' => $request->target_role,
            'title'       => $request->title,
            'message'     => $request->message,
            'type'        => $request->type,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Notification sent', 'data' => $notification], 201);
    }

    public function toggleUserStatus(Request $request)
{
    $user = User::findOrFail($request->id);

    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:users,id',
        'status' => 'required|in:active,inactive,deactivate',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    $user->status = $request->status;
    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => 'User status updated',
        'data' => $user
    ]);
}

}
