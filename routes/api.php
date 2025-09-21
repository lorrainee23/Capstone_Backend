<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\EnforcerController;
use App\Http\Controllers\Api\ViolatorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'loginViolator']);
Route::post('/admin/login', [AuthController::class, 'loginOfficials']);
Route::post('/enforcer-login', [EnforcerController::class, 'login']);
Route::post('/register', [AuthController::class, 'violatorRegister']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/forgot-password-violator', [AuthController::class, 'forgotPasswordViolator']);
Route::post('/forgot-password-officials', [AuthController::class, 'forgotPasswordOfficials']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

/*
|--------------------------------------------------------------------------
| Admin / Deputy / Head Routes
|--------------------------------------------------------------------------
| 
*/
Route::middleware(['auth:sanctum', 'check.multiguard:admin,deputy,head'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    
    // Profile 
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
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

    // Violators
    Route::get('/violators', [AdminController::class, 'getViolators']);
    Route::put('/update-violator', [AdminController::class, 'updateViolator']);
    Route::delete('/violators/{id}', [AdminController::class, 'archiveViolator']);
    Route::get('/violators/archived', [AdminController::class, 'getArchivedViolators']);
    Route::post('/violators/{id}/restore', [AdminController::class, 'restoreViolator']);

    // Vehicles
    Route::get('/vehicles', [AdminController::class, 'getVehicles']);
    Route::put('/vehicles/{id}', [AdminController::class, 'updateVehicle']);

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

    // Audit Logs
    Route::get('/logs', [AdminController::class, 'getAuditLogs']);

    // Download
    Route::get('/download-report/{filename}', function ($filename) {
        $filePath = storage_path('app/public/reports/' . $filename);
        if (!file_exists($filePath)) {
            abort(404, "File not found");
        }
        return response()->file($filePath);
    })->name('download.report');

    // Notifications
    Route::get('/notifications', [AdminController::class, 'getReceivedNotifications']); 
    Route::get('/notifications/all', [AdminController::class, 'getAllNotifications']);
    Route::get('/notifications/sent', [AdminController::class, 'getSentNotifications']);
    Route::post('/notifications/{id}/read', [AdminController::class, 'markNotificationAsRead']);
    Route::post('/notifications/{id}/unread', [AdminController::class, 'markNotificationAsUnread']);
    Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead']);
    Route::post('/send-notifications', [AdminController::class, 'sendNotification']);
    Route::get('/get-users', [AdminController::class, 'getAllUsers']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Enforcer Routes
|--------------------------------------------------------------------------
| 
*/
Route::middleware(['auth:sanctum', 'check.multiguard:enforcer'])->prefix('enforcer')->group(function () {

    // Dashboard
    Route::get('/dashboard', [EnforcerController::class, 'dashboard']);

    // Violators
    Route::get('/violators', [EnforcerController::class, 'getViolators']);
    Route::post('/search-violator', [EnforcerController::class, 'searchViolator']);

    // Violations
    Route::get('/violation-types', [EnforcerController::class, 'getViolationTypes']);
    Route::post('/violations', [EnforcerController::class, 'recordViolation']);

    // Transactions
    Route::get('/transactions', [EnforcerController::class, 'getTransactions']);
    Route::get('/transactions/{id}', [EnforcerController::class, 'getTransaction']);

    // Performance
    Route::get('/performance', [EnforcerController::class, 'getPerformanceStats']);

    // Profile
    Route::get('/profile', [EnforcerController::class, 'getProfile']);
    Route::post('/change', [EnforcerController::class, 'changePassword']);
    Route::post('/update', [EnforcerController::class, 'updateProfile']);

    // Notifications
    Route::get('/notifications', [EnforcerController::class, 'getNotifications']);
    Route::put('/notifications/{id}/read', [EnforcerController::class, 'markNotificationAsRead']);
    Route::put('/notifications/read-all', [EnforcerController::class, 'markAllNotificationsAsRead']);
    Route::delete('/notifications/{id}', [EnforcerController::class, 'deleteNotification']);
    Route::put('/notifications/{id}/restore', [EnforcerController::class, 'restoreNotification']);

    Route::post('/enforcer-logout', [EnforcerController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Violator Routes
|--------------------------------------------------------------------------
| 
*/
Route::middleware(['auth:sanctum', 'check.multiguard:violator'])->prefix('violator')->group(function () {

    // Dashboard
    Route::get('/dashboard', [ViolatorController::class, 'dashboard']);

    // Violations
    Route::get('/history', [ViolatorController::class, 'getViolationHistory']);
    Route::get('/violations/{id}', [ViolatorController::class, 'getViolationDetails']);
    Route::post('/upload-receipt', [ViolatorController::class, 'uploadReceipt']);

    // Profile
    Route::get('/profile', [ViolatorController::class, 'getProfile']);
    Route::post('/profile', [ViolatorController::class, 'updateProfile']);
    Route::post('/change-password', [ViolatorController::class, 'changePassword']);

    // Notifications
    Route::get('/notifications', [ViolatorController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [ViolatorController::class, 'markAsRead']);
    Route::post('/notifications/{id}/unread', [ViolatorController::class, 'markAsUnread']);
    Route::post('/notifications/mark-all-read', [ViolatorController::class, 'markAllAsRead']);

    // Statistics
    Route::get('/statistics', [ViolatorController::class, 'getStatistics']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});
