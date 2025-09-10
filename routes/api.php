<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\EnforcerController;
use App\Http\Controllers\Api\ViolatorController;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'violatorRegister']);

// Protected routes
Route::middleware(['auth:sanctum', 'check.multiguard:admin,deputy,head,enforcer,violator'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Admin routes
Route::prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::post('toggle-status', [AdminController::class, 'toggleUserStatus']);

    // Transactions 
    Route::get('/transactions', [AdminController::class, 'getTransactions']);
    Route::put('/transactions/{id}/update', [AdminController::class, 'updateTransaction']);

    // Users 
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{userType}/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{userType}/{id}', [AdminController::class, 'archiveUser']); 
    Route::get('/users/archived', [AdminController::class, 'getArchivedUsers']);
    Route::post('/users/{userType}/{id}/restore', [AdminController::class, 'restoreUser']); 
    Route::delete('/users/{userType}/{id}/force-delete', [AdminController::class, 'forceDeleteUser']);

    // Violators 
    Route::get('/violators', [AdminController::class, 'getViolators']);
    Route::put('/update-violator', [AdminController::class, 'updateViolator']);
    Route::delete('/violators/{id}', [AdminController::class, 'archiveViolator']);
    Route::get('/violators/archived', [AdminController::class, 'getArchivedViolators']);
    Route::post('/violators/{id}/restore', [AdminController::class, 'restoreViolator']);
    Route::delete('/violators/{id}/force-delete', [AdminController::class, 'forceDeleteViolator']);

    // Violations 
    Route::get('/violations', [AdminController::class, 'getViolations']);
    Route::post('/violations', [AdminController::class, 'createViolation']);
    Route::put('/violations/{id}', [AdminController::class, 'updateViolation']);
    Route::get('/violation/{id}', [AdminController::class, 'getViolation']);

    // Reports 
    Route::get('/quick-stats', [AdminController::class, 'getStats']);
    Route::post('/generate-report', [AdminController::class, 'generateAdvancedReport']);
    Route::get('/history', [AdminController::class, 'getReportHistory']);
    Route::delete('/history/clear', [AdminController::class, 'clearReportHistory']);
    Route::post('/history/restore/{id}', [AdminController::class, 'restoreReport']);
    
    // Download
Route::get('/download-report/{filename}', function ($filename) {
    $filePath = storage_path('app/public/reports/' . $filename);

    if (!file_exists($filePath)) {
        abort(404, "File not found");
    }

    // Open in browser if possible (PDF will preview, DOCX usually downloads)
    return response()->file($filePath);
})->name('download.report');

    // Notifications (unchanged)
    Route::get('/notifications', [AdminController::class, 'getAllNotifications']);
    Route::get('/specific-notifications', [AdminController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [AdminController::class, 'markNotificationAsRead']);
    Route::post('/notifications/{id}/unread', [AdminController::class, 'markNotificationAsUnread']);
    Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead']);
    Route::post('/send-notifications', [AdminController::class, 'sendNotification']);
});
    
    // Enforcer routes
    Route::prefix('enforcer')->group(function () {
        Route::get('/dashboard', [EnforcerController::class, 'dashboard']);
        Route::get('/violators', [EnforcerController::class, 'getViolators']);
        Route::get('/violation-types', [EnforcerController::class, 'getViolationTypes']);
        Route::post('/violations', [EnforcerController::class, 'recordViolation']);
        Route::get('/transactions', [EnforcerController::class, 'getTransactions']);
        Route::get('/transactions/{id}', [EnforcerController::class, 'getTransaction']);
        Route::get('/performance', [EnforcerController::class, 'getPerformanceStats']);
        Route::get('/profile', [EnforcerController::class, 'getProfile']);
        Route::post('/change', [EnforcerController::class, 'changePassword']);
        Route::post('/update', [EnforcerController::class, 'updateProfile']);
        Route::post('/search-violator', [EnforcerController::class, 'searchViolator']);
         Route::get('/notifications', [EnforcerController::class, 'getNotifications']);
        Route::put('/notifications/{id}/read', [EnforcerController::class, 'markNotificationAsRead']);
        Route::put('/notifications/read-all', [EnforcerController::class, 'markAllNotificationsAsRead']);
        Route::delete('/notifications/{id}', [EnforcerController::class, 'deleteNotification']);
        Route::put('/notifications/{id}/restore', [EnforcerController::class, 'restoreNotification']);
    });
    });
    
    // Violator routes (no role middleware needed, handled by auth:sanctum)
    Route::prefix('violator')->group(function () {
        Route::get('/dashboard', [ViolatorController::class, 'dashboard']);
        Route::get('/history', [ViolatorController::class, 'getViolationHistory']);
        Route::get('/violations/{id}', [ViolatorController::class, 'getViolationDetails']);
        Route::post('/profile', [ViolatorController::class, 'updateProfile']);
        Route::post('/change-password', [ViolatorController::class, 'changePassword']);
        Route::post('/upload-receipt', [ViolatorController::class, 'uploadReceipt']);
        Route::get('/notifications', [ViolatorController::class, 'getNotifications']);
        Route::post('/notifications/{id}/read', [ViolatorController::class, 'markAsRead']);
        Route::post('/notifications/{id}/unread', [ViolatorController::class, 'markAsUnread']);
        Route::post('/notifications/mark-all-read', [ViolatorController::class, 'markAllAsRead']);
        Route::get('/statistics', [ViolatorController::class, 'getStatistics']);
        Route::get('/profile', [ViolatorController::class, 'getProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});
