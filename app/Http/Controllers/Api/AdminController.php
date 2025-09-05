<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\Transaction;
use App\Models\Notification;
use App\Exports\ArrayExport;
use App\Models\Report;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Swagger\Client\Configuration;
use Swagger\Client\Api\ConvertDocumentApi;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Array_;

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
     // Get All notifications
     public function getNotifications()
{
    $notifications = Notification::where('target_role', 'Admin')
        ->orderBy('created_at', 'desc')
        ->take(15)
        ->get(['id', 'title', 'message', 'read_at', 'created_at']);

    return response()->json([
        'status' => 'success',
        'data' => $notifications
    ]);
}

    // Mark a single notification as read
    public function markNotificationAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_at = Carbon::now();
        $notification->save();

        return response()->json(['status' => 'success', 'message' => 'Notification marked as read']);
    }

    // Mark a single notification as unread
    public function markNotificationAsUnread($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_at = null;
        $notification->save();

        return response()->json(['status' => 'success', 'message' => 'Notification marked as unread']);
    }

    // Mark all notifications as read for the logged-in user role
    public function markAllNotificationsAsRead(Request $request)
    {
        Notification::where('target_role', $request->user()->role)
                    ->whereNull('read_at')
                    ->update(['read_at' => Carbon::now()]);

        return response()->json(['status' => 'success', 'message' => 'All notifications marked as read']);
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
     /* ==============================
     * NEW METHODS FOR DASHBOARD STATS & ADVANCED REPORTS
     * ============================== */
    
/**
 * Generate advanced reports
 */
public function generateAdvancedReport(Request $request)
{
    // Validator
    $validator = Validator::make($request->all(), [
        'period' => 'required|in:today,yesterday,last_7_days,last_30_days,last_3_months,last_6_months,last_year,year_to_date,custom',
        'start_date' => 'required_if:period,custom|date',
        'end_date' => 'required_if:period,custom|date|after:start_date',
        'type' => 'nullable|string',
        'export_formats' => 'nullable|array',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    $dateRange = $this->calculateDateRange($request->period, $request->start_date, $request->end_date);

    // Helper to safely access array keys
    $getKey = fn($array, $key, $default = 'N/A') => is_array($array) && array_key_exists($key, $array) ? $array[$key] : $default;

    // Total Paid Penalty
    $totalPenalty = Transaction::where('status', 'Paid')
        ->whereBetween('date_time', [$dateRange['start'], $dateRange['end']])
        ->sum('fine_amount');

    // All Violators
    $allViolators = Transaction::with(['violator', 'violation', 'vehicle', 'apprehendingOfficer'])
        ->whereBetween('date_time', [$dateRange['start'], $dateRange['end']])
        ->get()
        ->map(function ($tx) {
            $violator = $tx->violator;
            $vehicle = $tx->vehicle;
            $officer = $tx->apprehendingOfficer;

            $violatorName = $violator 
                ? trim(($violator->first_name ?? '') . ' ' . ($violator->middle_name ?? '') . ' ' . ($violator->last_name ?? '')) 
                : 'N/A';

            $violatorAddress = $violator 
                ? trim(($violator->barangay ?? '') . ' ' . ($violator->city ?? '') . ', ' . ($violator->province ?? '')) 
                : 'N/A';

            $ownerName = $vehicle 
                ? trim(($vehicle->owner_first_name ?? '') . ' ' . ($vehicle->owner_middle_name ?? '') . ' ' . ($vehicle->owner_last_name ?? '')) 
                : 'N/A';

            $ownerAddress = $vehicle 
                ? trim(($vehicle->owner_barangay ?? '') . ' ' . ($vehicle->owner_city ?? '') . ', ' . ($vehicle->owner_province ?? '')) 
                : 'N/A';

            $officerName = $officer 
                ? trim(($officer->first_name ?? '') . ' ' . ($officer->middle_name ?? '') . ' ' . ($officer->last_name ?? '')) 
                : 'N/A';

            return [
                'violator_name'    => $violatorName,
                'violator_address' => $violatorAddress,
                'violation'        => $tx->violation->name ?? 'N/A',
                'owner_name'       => $ownerName,
                'owner_address'    => $ownerAddress,
                'vehicle_type'     => $vehicle->vehicle_type ?? 'N/A',
                'vehicle_make'     => $vehicle->make ?? 'N/A',
                'vehicle_model'    => $vehicle->model ?? 'N/A',
                'plate_no'         => $vehicle->plate_number ?? 'N/A',
                'ticket_no'        => $tx->ticket_number ?? 'N/A',
                'ticket_date'      => $tx->date_time ? $tx->date_time->format('F j, Y') : 'N/A',
                'ticket_time'      => $tx->date_time ? $tx->date_time->format('g:i A') : 'N/A',
                'officer_name'     => $officerName,
                'officer_office'   => $officer->office ?? 'N/A',
                'remarks'          => $tx->remarks ?? '',
                'penalty_amount'   => (float) ($tx->fine_amount ?? 0),
            ];
        })->values();

    // Violations Mapping
    $violations = Violation::pluck('name', 'id')->toArray();

    // Common Violations
    $commonViolations = Transaction::select('violation_id')
        ->whereBetween('date_time', [$dateRange['start'], $dateRange['end']])
        ->groupBy('violation_id')
        ->selectRaw('violation_id, COUNT(*) as count')
        ->orderByDesc('count')
        ->take(10)
        ->get()
        ->map(function ($item) use ($violations) {
            return [
                'violation_id'   => $item->violation_id,
                'violation_name' => $violations[$item->violation_id] ?? 'N/A',
                'count'          => (int) ($item->count ?? 0),
            ];
        })->values();

    // Enforcer Performance
    $enforcerPerformance = User::where('role', 'Enforcer')
        ->with(['transactions' => function ($q) use ($dateRange) {
            $q->whereBetween('date_time', [$dateRange['start'], $dateRange['end']]);
        }])
        ->get()
        ->map(function ($enforcer) {
            $transactions = $enforcer->transactions;
            $totalTransactions = $transactions->count();
            $paidTransactions = $transactions->where('status', 'Paid')->count();

            return [
                'enforcer_name'     => trim(($enforcer->first_name ?? '') . ' ' . ($enforcer->last_name ?? '')),
                'violations_issued' => $totalTransactions,
                'collection_rate'   => $totalTransactions > 0 ? round(($paidTransactions / $totalTransactions) * 100, 1) : 0,
                'total_fines'       => (float) $transactions->sum('fine_amount'),
            ];
        })
        ->filter(fn($item) => $item['violations_issued'] > 0)
        ->values();

    $type = $request->input('type');
    $reportMap = [
        'total_revenue'        => [['Total Revenue' => (float) $totalPenalty]],
        'all_violators'        => $allViolators->toArray(),
        'common_violations'    => $commonViolations->toArray(),
        'enforcer_performance' => $enforcerPerformance->toArray(),
    ];

    $selectedData = $type && isset($reportMap[$type]) ? $reportMap[$type] : $reportMap;

    // Prepare Rows for Export safely
    $rows = [];
    if ($type === 'all_violators') {
        $rows = $allViolators->map(fn($item) => [
            'Violator Name'    => $getKey($item, 'violator_name'),
            'Violator Address' => $getKey($item, 'violator_address'),
            'Violation Name'   => $getKey($item, 'violation'),
            'Owner Name'       => $getKey($item, 'owner_name'),
            'Owner Address'    => $getKey($item, 'owner_address'),
            'Vehicle Type'     => $getKey($item, 'vehicle_type'),
            'Vehicle Make'     => $getKey($item, 'vehicle_make'),
            'Vehicle Model'    => $getKey($item, 'vehicle_model'),
            'Plate Number'     => $getKey($item, 'plate_no'),
            'Ticket Number'    => $getKey($item, 'ticket_no'),
            'Ticket Date'      => $getKey($item, 'ticket_date'),
            'Ticket Time'      => $getKey($item, 'ticket_time'),
            'Officer Name'     => $getKey($item, 'officer_name'),
            'Officer Office'   => $getKey($item, 'officer_office'),
            'Remarks'          => $getKey($item, 'remarks', ''),
            'Penalty Amount'   => $getKey($item, 'penalty_amount', 0),
        ])->toArray();
    } elseif ($type === 'common_violations') {
        $rows = $commonViolations->map(fn($item) => [
            'ID'             => $getKey($item, 'violation_id'),
            'Violation Name' => $getKey($item, 'violation_name'),
            'Count'          => $getKey($item, 'count', 0),
        ])->toArray();
    } elseif ($type === 'enforcer_performance') {
        $rows = $enforcerPerformance->map(fn($item) => [
            'Enforcer Name'       => $getKey($item, 'enforcer_name'),
            'Violations Issued'   => $getKey($item, 'violations_issued', 0),
            'Collection Rate (%)' => $getKey($item, 'collection_rate', 0),
            'Total Fines'         => $getKey($item, 'total_fines', 0),
        ])->toArray();
    } elseif ($type === 'total_revenue') {
        $rows = [['Total Revenue' => (float) $totalPenalty]];
    }

    // Export logic (Excel, Word, PDF)
    $timestamp = now()->format('Ymd_His');
    $files = [];
    $formats = (array) $request->input('export_formats', []);
    foreach ($formats as $format) {
        if ($format === 'excel') {
            $export = new ArrayExport($rows);
            $filename = ($type ?: 'report') . "_{$timestamp}.xlsx";
            Excel::store($export, "reports/{$filename}", 'public');

            $files[] = [
                'filename' => $filename,
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'url' => route('download.report', ['filename' => $filename]),
                'path' => "reports/{$filename}",
            ];
        } elseif ($format === 'word') {
            $html = view('reports.simple', [
                'type' => $type ?: 'combined',
                'period' => $request->period,
                'rows' => $rows,
                'totalPenalty' => $totalPenalty,
                'dateRange' => $dateRange,
            ])->render();

            $pdfPath = storage_path("app/report_{$timestamp}.pdf");
            Pdf::loadHTML($html)->save($pdfPath);

            $docxPath = $this->convertPdfToWordCloudmersive($pdfPath);
            $binary = file_get_contents($docxPath);
            $storedPath = 'reports/' . ($type ?: 'report') . '_' . $timestamp . '.docx';
            Storage::disk('public')->put($storedPath, $binary);

            $files[] = [
                'filename' => ($type ?: 'report') . '_' . $timestamp . '.docx',
                'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'url' => route('download.report', ['filename' => ($type ?: 'report') . '_' . $timestamp . '.docx']),
                'path' => $storedPath,
            ];

            @unlink($docxPath);
            @unlink($pdfPath);
        } elseif ($format === 'pdf') {
            $html = view('reports.simple', [
                'type' => $type ?: 'combined',
                'period' => $request->period,
                'rows' => $rows,
                'totalPenalty' => $totalPenalty,
                'dateRange' => $dateRange,
            ])->render();

            $binary = Pdf::loadHTML($html)->output();
            $storedPath = 'reports/' . ($type ?: 'report') . '_' . $timestamp . '.pdf';
            Storage::disk('public')->put($storedPath, $binary);

            $files[] = [
                'filename' => ($type ?: 'report') . '_' . $timestamp . '.pdf',
                'mimeType' => 'application/pdf',
                'url' => route('download.report', ['filename' => ($type ?: 'report') . '_' . $timestamp . '.pdf']),
                'path' => $storedPath,
            ];
        }
    }

    // Store report
    $report = Report::create([
        'type' => $type ?: 'combined',
        'period' => $request->period,
        'report_content' => $selectedData,
        'summary' => [
            'total_penalty' => (float) $totalPenalty,
            'total_violators' => $allViolators->count(),
            'common_violations' => $commonViolations->toArray(),
            'enforcer_performance' => $enforcerPerformance->toArray(),
        ],
        'files' => $files,
    ]);

    return response()->json([
        'status' => 'success',
        'data' => [
            'report' => $selectedData,
            'files' => $files,
            'report_id' => $report->id,
        ]
    ]);
}


public function convertPdfToWordCloudmersive($pdfPath)
{
    $config = Configuration::getDefaultConfiguration()->setApiKey(
        'Apikey',
        env('CLOUDMERSIVE_API_KEY')
    );

    $apiInstance = new ConvertDocumentApi(new Client(), $config);

    try {
        $inputFile = new \SplFileObject($pdfPath);
        $result = $apiInstance->convertDocumentPdfToDocx($inputFile);

        $filename = storage_path('app/public/report_' . time() . '.docx');
        file_put_contents($filename, $result);
        return $filename;

    } catch (\Exception $e) {
        throw new \Exception('Cloudmersive conversion failed: ' . $e->getMessage());
    }
}

public function getReportHistory(Request $request)
{
    $includeDeleted = $request->query('include_deleted', false);

    $reports = $includeDeleted
        ? Report::withTrashed()->latest()->get() 
        : Report::latest()->get();

    return response()->json([
        'data' => $reports
    ]);
}

public function restoreReport($id)
{
    $report = Report::withTrashed()->findOrFail($id);

    if ($report->trashed()) {
        $report->restore();
        return response()->json([
            'message' => 'Report restored successfully.',
            'data' => $report
        ]);
    }

    return response()->json([
        'message' => 'Report is not deleted.'
    ], 400);
}

public function clearReportHistory()
{
    $reports = Report::all();

    foreach ($reports as $report) {
        if ($report->files) {
            foreach ($report->files as $file) {
                if (isset($file['path']) && Storage::exists($file['path'])) {
                    Storage::delete($file['path']);
                }
            }
        }
    }
    Report::query()->delete(); 

    return response()->json([
        'message' => 'Report history cleared successfully (files deleted).'
    ]);
}

/**
 * Calculate start and end datetime for a period.
 */
private function calculateDateRange($period, $start = null, $end = null)
{
    switch ($period) {
        case 'today':
            return ['start' => now()->startOfDay(), 'end' => now()->endOfDay()];
        case 'yesterday':
            return ['start' => now()->subDay()->startOfDay(), 'end' => now()->subDay()->endOfDay()];
        case 'last_7_days':
            return ['start' => now()->subDays(6)->startOfDay(), 'end' => now()->endOfDay()];
        case 'last_30_days':
            return ['start' => now()->subDays(29)->startOfDay(), 'end' => now()->endOfDay()];
        case 'last_3_months':
            return ['start' => now()->subMonths(3)->startOfDay(), 'end' => now()->endOfDay()];
        case 'last_6_months':
            return ['start' => now()->subMonths(6)->startOfDay(), 'end' => now()->endOfDay()];
        case 'last_year':
            return ['start' => now()->subYear()->startOfDay(), 'end' => now()->endOfDay()];
        case 'year_to_date':
            return ['start' => now()->startOfYear(), 'end' => now()];
        case 'custom':
            return ['start' => Carbon::parse($start)->startOfDay(), 'end' => Carbon::parse($end)->endOfDay()];
        default:
            return ['start' => now()->subDays(6)->startOfDay(), 'end' => now()->endOfDay()];
    }
}
}
