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
        $violator = $request->user();
        
        $stats = [
            'total_violations' => $violator->transactions()->count(),
            'pending_violations' => $violator->pendingTransactions()->count(),
            'paid_violations' => $violator->paidTransactions()->count(),
            'total_fines' => $violator->total_fines,
            'unpaid_fines' => $violator->unpaid_fines,
            'is_repeat_offender' => $violator->isRepeatOffender(),
        ];

        // Recent violations
        $recentViolations = $violator->transactions()
            ->with(['violation', 'apprehendingOfficer'])
            ->latest()
            ->limit(5)
            ->get();

        // Violation history by month
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
        $violator = $request->user();
        
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
        $violator = auth()->user();
        
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
        $violator = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|size:11',
            'address' => 'required|string|max:255',
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
            'first_name', 'middle_name', 'last_name', 'mobile_number', 'address'
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

        $violator = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $violator->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $violator->password = Hash::make($request->new_password);
        $violator->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Upload receipt for violation
     */
    public function uploadReceipt(Request $request)
{
    $violator = $request->user();

    $transactionId = $request->input('transaction_id');

    $validator = Validator::make($request->all(), [
        'receipt' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'transaction_id' => 'required|exists:transactions,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Make sure the transaction belongs to this violator
    $transaction = $violator->transactions()->findOrFail($transactionId);

    // Handle receipt upload
    $receiptPath = $request->file('receipt')->store('receipts', 'public');
    
    $transaction->receipt = $receiptPath;
    $transaction->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Receipt uploaded successfully',
        'data' => $transaction->load(['violation', 'apprehendingOfficer'])
    ]);
}
    /**
     * Get notifications for violator
     */
    public function getNotifications(Request $request)
    {
        $notifications = Notification::where('target_role', 'Violator')
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    /**
     * Get violator's statistics
     */
    public function getStatistics(Request $request)
    {
        $violator = $request->user();
        
        $stats = [
            'total_violations' => $violator->transactions()->count(),
            'pending_violations' => $violator->pendingTransactions()->count(),
            'paid_violations' => $violator->paidTransactions()->count(),
            'total_fines' => $violator->total_fines,
            'unpaid_fines' => $violator->unpaid_fines,
            'is_repeat_offender' => $violator->isRepeatOffender(),
        ];

        // Violations by type
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

        // Violations by month (last 12 months)
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
        $violator = $request->user();

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
                    'plate_number' => $violator->plate_number,
                    'model' => $violator->model,
                    'address' => $violator->address,
                    'id_photo' => $violator->id_photo,
                    'total_fines' => $violator->total_fines,
                    'unpaid_fines' => $violator->unpaid_fines,
                    'is_repeat_offender' => $violator->isRepeatOffender(),
                ]
            ]
        ]);
    }
} 