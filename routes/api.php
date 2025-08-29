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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/violator/register', [AuthController::class, 'violatorRegister']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Admin routes
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/violations', [AdminController::class, 'getViolations']);
        Route::post('/violations', [AdminController::class, 'createViolation']);
        Route::put('/violations/{id}', [AdminController::class, 'updateViolation']);
        Route::delete('/violations/{id}', [AdminController::class, 'deleteViolation']);
        Route::get('/repeat-offenders', [AdminController::class, 'getRepeatOffenders']);
        Route::post('/reports', [AdminController::class, 'generateReport']);
        Route::post('/notifications', [AdminController::class, 'sendNotification']);
    });
    
    // Enforcer routes
    Route::middleware('role:Enforcer')->prefix('enforcer')->group(function () {
        Route::get('/dashboard', [EnforcerController::class, 'dashboard']);
        Route::get('/violators', [EnforcerController::class, 'getViolators']);
        Route::get('/violation-types', [EnforcerController::class, 'getViolationTypes']);
        Route::post('/violations', [EnforcerController::class, 'recordViolation']);
        Route::get('/transactions', [EnforcerController::class, 'getTransactions']);
        Route::get('/transactions/{id}', [EnforcerController::class, 'getTransaction']);
        Route::put('/transactions/{id}', [EnforcerController::class, 'updateTransaction']);
        Route::get('/performance', [EnforcerController::class, 'getPerformanceStats']);
        Route::post('/change', [EnforcerController::class, 'changePassword']);
        Route::post('/update', [EnforcerController::class, 'updateProfile']);
        Route::get('/search-violator', [EnforcerController::class, 'searchViolator']);
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
        Route::get('/statistics', [ViolatorController::class, 'getStatistics']);
        Route::get('/profile', [ViolatorController::class, 'getProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});
