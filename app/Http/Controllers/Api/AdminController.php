<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Traits\UserPermissionsTrait;

// Models
use App\Models\Admin;
use App\Models\Head;
use App\Models\Deputy;
use App\Models\Enforcer;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Report;

// Exports & External Packages
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use App\Mail\POSUEmail;
use App\Models\Vehicle;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Swagger\Client\Configuration;
use Swagger\Client\Api\ConvertDocumentApi;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    use UserPermissionsTrait;

    public function dashboard(Request $request)
{
    $period = $request->get('period', 'all');
    $now = now();

    $transactionQuery = Transaction::query();

    if ($period === 'year') {
        $transactionQuery->whereYear('date_time', $now->year);
    } elseif ($period === 'month') {
        $transactionQuery->whereYear('date_time', $now->year)
                         ->whereMonth('date_time', $now->month);
    } elseif ($period === 'week') {
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $transactionQuery->whereBetween('date_time', [$startOfWeek, $endOfWeek]);
    }

    $totalViolators = Violator::whereHas('transactions', function($q) use ($period, $now) {
        if ($period === 'year') {
            $q->whereYear('date_time', $now->year);
        } elseif ($period === 'month') {
            $q->whereYear('date_time', $now->year)
              ->whereMonth('date_time', $now->month);
        } elseif ($period === 'week') {
            $q->whereBetween('date_time', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        }
    })->count();

    $getRepeatOffendersQuery = function() use ($period, $now) {
        return Violator::whereHas('transactions', function($q) use ($period, $now) {
            if ($period === 'year') {
                $q->whereYear('date_time', $now->year);
            } elseif ($period === 'month') {
                $q->whereYear('date_time', $now->year)
                  ->whereMonth('date_time', $now->month);
            } elseif ($period === 'week') {
                $q->whereBetween('date_time', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            }
        });
    };

    $repeatOffenders = $getRepeatOffendersQuery()
        ->withCount(['transactions' => function($q) use ($period, $now) {
            if ($period === 'year') {
                $q->whereYear('date_time', $now->year);
            } elseif ($period === 'month') {
                $q->whereYear('date_time', $now->year)
                  ->whereMonth('date_time', $now->month);
            } elseif ($period === 'week') {
                $q->whereBetween('date_time', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            }
        }])
        ->having('transactions_count', '>', 1)
        ->count();

    $stats = [
        'total_violators'      => $totalViolators,
        'total_transactions'   => $transactionQuery->count(),
        'pending_transactions' => (clone $transactionQuery)->where('status', 'Pending')->count(),
        'paid_transactions'    => (clone $transactionQuery)->where('status', 'Paid')->count(),
        'total_revenue'        => (clone $transactionQuery)->where('status', 'Paid')->sum('fine_amount'),
        'pending_revenue'      => (clone $transactionQuery)->where('status', 'Pending')->sum('fine_amount'),
        'repeat_offenders'     => $repeatOffenders,
        'active_enforcers'     => Enforcer::where('status', 'active')->count(),
        'active_admins'        => Admin::where('status', 'active')->count(),
        'active_deputies'      => Deputy::where('status', 'active')->count(),
        'active_heads'         => Head::where('status', 'active')->count(),
    ];

    // Trends 
    $weeklyTrends = [];
    $monthlyTrends = [];
    $yearlyTrends = [];

        $weeklyTrends = Transaction::selectRaw('DATE(date_time) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date', 'asc')
    ->get();

// Monthly trends - all months from all years  
$monthlyTrends = Transaction::selectRaw('MONTH(date_time) as month, YEAR(date_time) as year, COUNT(*) as count')
    ->groupBy('year', 'month')
    ->orderBy('year', 'asc')
    ->orderBy('month', 'asc')
    ->get();

// Yearly trends - all years
$yearlyTrends = Transaction::selectRaw('YEAR(date_time) as year, COUNT(*) as count')
    ->groupBy('year')
    ->orderBy('year', 'asc')
    ->get();

    $commonViolations = Violation::withCount('transactions')
        ->orderBy('transactions_count', 'desc')
        ->limit(5)
        ->get();

    $enforcerPerformance = Enforcer::with('transactions')->get();

    // Trend percentage (affected by period)
    if ($period === 'all') {
        $percentage = 0;
        $trendDirection = 'same';
        
    } else {
        $currentPeriodQuery = Transaction::query();
        $previousPeriodQuery = Transaction::query();

        if ($period === 'year') {
            $currentPeriodQuery->whereYear('date_time', $now->year);
            $previousPeriodQuery->whereYear('date_time', $now->year - 1);
            
        } elseif ($period === 'month') {
            $currentPeriodQuery->whereYear('date_time', $now->year)
                               ->whereMonth('date_time', $now->month);
            $prevMonth = $now->copy()->subMonth();
            $previousPeriodQuery->whereYear('date_time', $prevMonth->year)
                                ->whereMonth('date_time', $prevMonth->month);
                                
        } elseif ($period === 'week') {
            $currentPeriodQuery->whereBetween('date_time', [
                $now->copy()->startOfWeek(), 
                $now->copy()->endOfWeek()
            ]);
            $previousPeriodQuery->whereBetween('date_time', [
                $now->copy()->subWeek()->startOfWeek(), 
                $now->copy()->subWeek()->endOfWeek()
            ]);
        }

        $currentTransactions = $currentPeriodQuery->count();
        $previousTransactions = $previousPeriodQuery->count();

        $percentage = $previousTransactions > 0
            ? round((($currentTransactions - $previousTransactions) / $previousTransactions) * 100, 2)
            : ($currentTransactions > 0 ? 100 : 0);

        $trendDirection = $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'same');
    }

    return response()->json([
        'status' => 'success',
        'data'   => [
            'stats'                => $stats,
            'weekly_trends'        => $weeklyTrends,
            'monthly_trends'       => $monthlyTrends,
            'yearly_trends'        => $yearlyTrends,
            'common_violations'    => $commonViolations,
            'enforcer_performance' => $enforcerPerformance,
            'trends'               => [
                'transactions' => [
                    'percentage' => $percentage,
                    'direction'  => $trendDirection
                ]
            ],
        ]
    ]);
}

    /* ==============================
     * USERS MANAGEMENT (All User Types)
     * ============================== */
    public function getUsers(Request $request)
    {
        $authUser = $request->user('sanctum');
        $currentUserType = $this->getUserType($authUser);

        if (!$currentUserType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to determine user type'
            ], 403);
        }

        $viewableUserTypes = $this->getViewableUserTypes($authUser);

        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $users = collect();

        $applyFilters = function ($query) use ($status, $search) {
            if ($status) {
                $query->where('status', $status);
            }
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            return $query;
        };

        $viewableUserTypes = $this->getViewableUserTypes($authUser);
        
        foreach ($viewableUserTypes as $userType) {
            $modelClass = $this->getModelClass($userType);
            $userCollection = $applyFilters($modelClass::query())->get()->map(function ($user) use ($userType) {
                $user->user_type = $userType;
                return $user;
            });
            $users = $users->merge($userCollection);
        }

        // Pagination
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedUsers = $users->slice($offset, $perPage);

        $paginationData = [
            'data' => $paginatedUsers->values(),
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $users->count(),
            'last_page' => ceil($users->count() / $perPage),
        ];

        return response()->json(['status' => 'success', 'data' => $paginationData]);
    }

    public function createUser(Request $request)
    {
        $authUser = $request->user('sanctum');
        $currentUserType = $this->getUserType($authUser);
        
        if (!$currentUserType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to determine user type'
            ], 403);
        }

        $userType = $request->input('user_type') ?? $request->input('role');
        $manageableTypes = $this->getManageableUserTypes($authUser);
        
        if (!in_array($userType, $manageableTypes)) {
            return response()->json([
                'status' => 'error',
                'message' => "You are not authorized to create {$userType} users. You can only create: " . implode(', ', $manageableTypes)
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name'   => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'last_name'    => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:admins,username|unique:heads,username|unique:deputies,username|unique:enforcers,username',
            'office'       => 'required|string|max:255',
            'password'     => 'required|string|min:6',
            'status'       => 'required|in:active,inactive,deactivate',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_type'    => 'required|in:' . implode(',', $manageableTypes),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'office'     =>$request->office,
            'password'   => Hash::make($request->password),
            'status'     => $request->status,
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
            $userData['image'] = $imagePath;
        }

        $modelClass = $this->getModelClass($userType);
        $user = $modelClass::create($userData);

        // If email is provided
        if (!empty($request->email)) {
            $user->email = $request->email;
            $user->save();

            try {
                $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
                $loginUrl = env('FRONTEND_LOGIN_URL', 'http://localhost:8080/login');
                
                Mail::to($user->email)->send(
                    new POSUEmail('welcome', [
                        'user_name' => $user->first_name,
                        'full_name' => $fullName,
                        'email' => $user->email,
                        'account_type' => $userType,
                        'registration_date' => now()->format('F j, Y'),
                        'login_url' => $loginUrl,
                        'temporary_password' => $request->password,
                    ])
                );
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email: ' . $e->getMessage());
            }
        }

        // Remove password from response
        unset($user->password);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully' . (!empty($request->email) ? ' and welcome email sent' : ''),
            'data' => array_merge($user->toArray(), ['user_type' => $userType])
        ], 201);
    }

    /**
     * Update user based on type
     */
    public function updateUser(Request $request, $userType, $id)
    {
        $authUser = $request->user('sanctum');

         if (!$this->canManageUserType($authUser, $userType)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to update this type of user'
            ], 403);
        }

        $modelClass = self::getModelClass($userType);
        $user = $modelClass::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'username'    => 'required|string|max:255|unique:' . $user->getTable() . ',username,' . $id,
            'email'       => 'required|email|unique:' . $user->getTable() . ',email,' . $id,
            'status'      => 'required|in:active,inactive,deactivate',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $imagePath = $request->file('image')->store('users', 'public');
            $updateData['image'] = $imagePath;
        }

        $user->first_name = $request->input('first_name', $user->first_name);
        $user->middle_name = $request->input('middle_name', $user->middle_name);
        $user->last_name  = $request->input('last_name', $user->last_name);
        $user->email      = $request->input('email', $user->email);
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->status = $request->input('status', $user->status);
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'User updated successfully', 'data' => $user]);
    }

    /* ==============================
     * ADVANCED REPORTS 
     * ============================== */
    
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
                    'plate_number'         => $vehicle->plate_number ?? 'N/A',
                    'ticket_no'        => $tx->ticket_number ?? 'N/A',
                    'ticket_date'      => $tx->date_time ? $tx->date_time->format('F j, Y') : 'N/A',
                    'ticket_time'      => $tx->date_time ? $tx->date_time->format('g:i A') : 'N/A',
                    'officer_name'     => $officerName,
                    'officer_office'   => $officer->office ?? 'N/A',
                    'remarks'          => $tx->status ?? 'N/A',
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
        $enforcerPerformance = Enforcer::with(['transactions' => function ($q) use ($dateRange) {
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
                'Plate Number'     => $getKey($item, 'plate_number'),
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

    /**
     * Archive user based on type
     */
    public function archiveUser($userType, $id)
    {
        $authUser = request()->user('sanctum');

        // Check if authenticated user can archive this type of user
        if (!$this->canManageUserType($authUser, $userType)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to archive this type of user'
            ], 403);
        }

        $currentUserType = $this->getUserType($authUser);
        $manageableTypes = $authUser->getManageableUserTypes($authUser);
        
        if (!in_array($userType, $manageableTypes)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid user type'], 400);
        }

        $modelClass = $this->getModelClass($userType);
        $user = $modelClass::findOrFail($id);
        $user->delete();

        return response()->json(['status' => 'success', 'message' => 'User archived successfully']);
    }

    /**
     * Get archived users
     */
    public function getArchivedUsers(Request $request)
    {
        $authUser = $request->user('sanctum');
        $currentUserType = $this->getUserType($authUser);
        $userType = $request->input('user_type', 'all');
        $search = $request->input('search');
        $users = collect();

        $applyFilters = function($query) use ($search) {
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            return $query;
        };

        $manageableTypes = $authUser->getManageableUserTypes($authUser);
        
        foreach ($manageableTypes as $type) {
            if ($userType === 'all' || $userType === $type) {
                $modelClass = $this->getModelClass($type);
                $archivedUsers = $applyFilters($modelClass::onlyTrashed())->get()->map(function($user) use ($type) {
                    $user->user_type = $type;
                    return $user;
                });
                $users = $users->merge($archivedUsers);
            }
        }

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    /**
     * Restore user
     */
    public function restoreUser($userType, $id)
    {
        $authUser = request()->user('sanctum');

        // Check if authenticated user can restore this type of user
        if (!$this->canManageUserType($authUser, $userType)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to restore this type of user'
            ], 403);
        }

        $currentUserType = $this->getUserType($authUser);
        $manageableTypes = $authUser->getManageableUserTypes($authUser);
        
        if (!in_array($userType, $manageableTypes)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid user type'], 400);
        }

        $modelClass = $this->getModelClass($userType);
        $user = $modelClass::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['status' => 'success', 'message' => 'User restored successfully', 'data' => $user]);
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(Request $request)
    {
        $authUser = $request->user('sanctum');
        $currentUserType = $this->getUserType($authUser);
        $manageableTypes = $authUser->getManageableUserTypes($authUser);

        $validator = Validator::make($request->all(), [
            'user_type' => 'required|in:' . implode(',', $manageableTypes),
            'id' => 'required|integer',
            'status' => 'required|in:active,inactive,deactivate',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        if (!$this->canManageUserType($authUser, $request->user_type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to change the status of this type of user'
            ], 403);
        }

        $modelClass = $this->getModelClass($request->user_type);
        $user = $modelClass::findOrFail($request->id);

        $user->status = $request->status;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User status updated successfully',
            'data' => $user
        ]);
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
            'password'       => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

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

        if ($request->filled('password')) {
        $data['password'] = bcrypt($request->password);
        }

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

    /* ==============================
     * VEHICLES MANAGEMENT
     * ============================== */

    public function getVehicles(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $page    = $request->input('page', 1);

        
        $vehicles = Vehicle::with('violator') 
            ->paginate($perPage, ['*'], 'page', $page);

        $vehicles->getCollection()->transform(function ($vehicle) {
            if ($vehicle->violator) {
                $vehicle->owner_full_name = $vehicle->violator->full_name ?? $vehicle->ownerName();
            } else {
                $vehicle->owner_full_name = $vehicle->ownerName();
            }
            return $vehicle;
        });

        return response()->json([
            'status' => 'success',
            'data'   => $vehicles
        ]);
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found.'
            ], 404);
        }

        // Validate incoming data
        $validated = $request->validate([
            'owner_first_name' => 'sometimes|string|max:255',
            'owner_middle_name' => 'sometimes|string|max:255|nullable',
            'owner_last_name' => 'sometimes|string|max:255',
            'plate_number' => 'sometimes|string|max:50',
            'make' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:50',
            'owner_barangay' => 'sometimes|string|max:255|nullable',
            'owner_city' => 'sometimes|string|max:255|nullable',
            'owner_province' => 'sometimes|string|max:255|nullable',
            'vehicle_type' => 'sometimes|string|max:50',
        ]);

        // Update vehicle
        $vehicle->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle
        ]);
    }


    /* ==============================
     * VIOLATIONS MANAGEMENT
     * ============================== */

    public function getViolations()
    {
        $violations = Violation::withCount('transactions')->get();

        return response()->json(['status' => 'success', 'data' => $violations]);
    }
    public function getViolation($id)
    {
        $violation = Violation::withCount('transactions')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $violation
        ]);
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

    /* ==============================
     * TRANSACTIONS 
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

    public function updateTransaction(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|string|in:Paid,Pending',
        ]);

        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found.'
            ], 404);
        }

        $transaction->status = $request->input('status', 'Paid');
        $transaction->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully.',
            'data' => $transaction
        ]);
    }

    /* ==============================
     * NOTIFICATIONS
     * ============================== */

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_type' => 'required|in:admin,head,deputy,enforcer,violator',
            'title'       => 'required|string|max:100',
            'message'     => 'required|string',
            'type'        => 'required|in:info,alert,reminder,warning',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $authUser = $request->user('sanctum');
        $senderRole = ucfirst($this->getUserType($authUser));
        $senderName = trim($authUser->first_name . ' ' . ($authUser->middle_name ? $authUser->middle_name . ' ' : '') . $authUser->last_name);

        $notification = Notification::create([
            'sender_id'   => $authUser->id,
            'sender_role' => $senderRole,
            'sender_name' => $senderName,
            'target_type' => $request->target_type,
            'target_id'   => $request->target_id,
            'title'       => $request->title,
            'message'     => $request->message,
            'type'        => $request->type,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Notification sent', 'data' => $notification], 201);
    }

    public function getAllNotifications(Request $request)
    {
        $authUser = $request->user('sanctum');
        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User is not a Official not logged in'
            ], 403);
        }

    $notifications = Notification::where(function($query) use ($authUser) {
                $query->where('target_type', 'Management')
                      ->orWhere(function($subQuery) use ($authUser) {
                          $subQuery->where('sender_id', $authUser->id)
                                   ->where('sender_role', ucfirst($this->getUserType($authUser)));
                      });
            })
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get(['id','title', 'message', 'type','read_at', 'created_at', 'sender_id', 'sender_role', 'sender_name', 'target_type', 'target_id', 'violator_id', 'transaction_id']);

        return response()->json([
            'status' => 'success',
            'data'   => $notifications
        ]);
    }

    public function getSentNotifications(Request $request)
    {
        $authUser = $request->user('sanctum');
        if (!$authUser || !in_array($authUser->role, ['Admin', 'Deputy'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Admin access required'
            ], 403);
        }

        $notifications = Notification::where('sender_id', $authUser->id)
            ->where('sender_role', ucfirst($this->getUserType($authUser)))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markNotificationAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_at = Carbon::now();
        $notification->save();

        return response()->json(['status' => 'success', 'message' => 'Notification marked as read']);
    }

    public function markNotificationAsUnread($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_at = null;
        $notification->save();

        return response()->json(['status' => 'success', 'message' => 'Notification marked as unread']);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        Notification::where('target_role', $request->user()->role)
                    ->whereNull('read_at')
                    ->update(['read_at' => Carbon::now()]);

        return response()->json(['status' => 'success', 'message' => 'All notifications marked as read']);
    }
    
    public function getAllUsers()
    {
        $admins = Admin::all(['id', 'first_name', 'last_name', 'email']);
        $deputies = Deputy::all(['id', 'first_name', 'last_name', 'email']);
        $enforcers = Enforcer::all(['id', 'first_name', 'last_name', 'email']);

        $users = [
            'admins' => $admins,
            'deputies' => $deputies,
            'enforcers' => $enforcers,
        ];

        return response()->json($users);
    }
}