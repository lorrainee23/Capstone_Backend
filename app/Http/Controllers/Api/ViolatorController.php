<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Violator;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ViolatorController extends Controller
{
    /**
     * Get violator dashboard
     */
    public function dashboard(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }
        
        $stats = [
            'total_violations' => $violator->transactions()->count(),
            'pending_violations' => $violator->pendingTransactions()->count(),
            'paid_violations' => $violator->paidTransactions()->count(),
            'total_fines' => $violator->total_fines,
            'unpaid_fines' => $violator->unpaid_fines,
            'is_repeat_offender' => $violator->isRepeatOffender(),
        ];

        $recentViolations = $violator->transactions()
            ->with(['violation', 'apprehendingOfficer'])
            ->latest()
            ->limit(5)
            ->get();

        $monthlyViolations = $violator->transactions()
            ->selectRaw('YEAR(date_time) as year, MONTH(date_time) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'recent_violations' => $recentViolations,
                'monthly_violations' => $monthlyViolations,
            ]
        ]);
    }

    /**
     * Get violator's violation history
     */
    public function getViolationHistory(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }
        
        $query = $violator->transactions()->with(['violation', 'apprehendingOfficer']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('date_time', $request->date);
        }

        $transactions = $query->latest()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Get specific violation details
     */
    public function getViolationDetails($id)
    {
        $violator = auth('violator')->user();
        
        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }
        
        $transaction = $violator->transactions()
            ->with(['violation', 'apprehendingOfficer'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    /**
     * Update violator profile
     */
    public function updateProfile(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|size:11',
            'id_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $violator->update($request->only([
            'first_name', 'middle_name', 'last_name', 'mobile_number'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => [
                'violator' => [
                    'id' => $violator->id,
                    'first_name' => $violator->first_name,
                    'middle_name' => $violator->middle_name,
                    'last_name' => $violator->last_name,
                    'full_name' => $violator->full_name,
                    'email' => $violator->email,
                    'mobile_number' => $violator->mobile_number,
                ]
            ]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The new password field confirmation does not match',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $violator->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $violator->password = Hash::make($request->new_password);
        $violator->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get notifications for violator
     */
    public function getNotifications(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }

        $notifications = Notification::where('target_type', 'Violator')
            ->where('target_id', $violator->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_at = now();
        $notification->save();

        return response()->json(['status' => 'success']);
    }

    public function markAsUnread($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_at = null;
        $notification->save();

        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }

        Notification::where('target_type', 'Violator')
            ->where('target_id', $violator->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }
    
    /**
     * Get violator's statistics
     */
    public function getStatistics(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
        }
        
        $stats = [
            'total_violations' => $violator->transactions()->count(),
            'pending_violations' => $violator->pendingTransactions()->count(),
            'paid_violations' => $violator->paidTransactions()->count(),
            'total_fines' => $violator->total_fines,
            'unpaid_fines' => $violator->unpaid_fines,
            'is_repeat_offender' => $violator->isRepeatOffender(),
        ];

        $violationsByType = $violator->transactions()
            ->with('violation')
            ->get()
            ->groupBy('violation.name')
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_fines' => $group->sum('fine_amount'),
                ];
            });

        $violationsByMonth = $violator->transactions()
            ->selectRaw('YEAR(date_time) as year, MONTH(date_time) as month, COUNT(*) as count, SUM(fine_amount) as total_fines')
            ->whereBetween('date_time', [now()->subMonths(12), now()])
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'violations_by_type' => $violationsByType,
                'violations_by_month' => $violationsByMonth,
            ]
        ]);
    }

    /**
     * Get violator's profile
     */
    public function getProfile(Request $request)
    {
        $violator = auth('violator')->user();

        if (!($violator instanceof Violator)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: only violators can access this endpoint'
            ], 403);
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
                    'gender' => $violator->gender,
                    'gender_text' => $violator->gender_text,
                    'license_number' => $violator->license_number,
                    'plate_number' => $violator->plate_number ?? null,
                    'model' => $violator->model ?? null,
                    'address' => $violator->full_address ?? null,
                    'id_photo' => $violator->id_photo,
                    'total_fines' => $violator->total_fines,
                    'unpaid_fines' => $violator->unpaid_fines,
                    'is_repeat_offender' => $violator->isRepeatOffender(),
                ]
            ]
        ]);
    }
}
