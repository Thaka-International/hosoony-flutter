<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanionsController;
use App\Http\Controllers\Api\V1\DailyTasksController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OperationsController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PerformanceEvaluationController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1 routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Operations routes (public for health checks)
    Route::get('/ops/scheduler/last-run', [OperationsController::class, 'schedulerLastRun']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Daily tasks routes
        Route::get('/students/{id}/daily-tasks', [DailyTasksController::class, 'getDailyTasks']);
        Route::post('/daily-logs/submit', [DailyTasksController::class, 'submitDailyLog']);
        Route::get('/students/{id}/daily-logs', [DailyTasksController::class, 'getStudentDailyLogs']);

        // Reports routes
        Route::get('/reports/daily/{class_id}', [ReportsController::class, 'dailyReport']);
        Route::post('/reports/monthly/generate', [ReportsController::class, 'generateMonthlyReport']);
        Route::get('/reports/monthly/{id}/export', [ReportsController::class, 'exportMonthlyReport']);

        // Subscription routes
        Route::get('/students/{id}/subscription', [SubscriptionController::class, 'getSubscription']);
        Route::patch('/students/{id}/subscription', [SubscriptionController::class, 'updateSubscription']);

        // Payment routes
        Route::post('/students/{id}/payments', [PaymentController::class, 'createPayment']);
        Route::get('/students/{id}/payments', [PaymentController::class, 'getStudentPayments']);

        // Notification routes
        Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification']);

        // Device registration routes (for FCM tokens)
        Route::post('/devices/register', [DeviceController::class, 'register']);
        Route::post('/devices/unregister', [DeviceController::class, 'unregister']);

        // Performance evaluation routes
        Route::get('/sessions/{session}/evaluations', [PerformanceEvaluationController::class, 'getSessionEvaluations']);
        Route::post('/sessions/{session}/evaluations', [PerformanceEvaluationController::class, 'storeEvaluation']);
        Route::post('/sessions/{session}/evaluations/bulk', [PerformanceEvaluationController::class, 'bulkEvaluate']);
        Route::get('/evaluations/{evaluation}', [PerformanceEvaluationController::class, 'getEvaluation']);
        Route::get('/students/{student}/performance', [PerformanceEvaluationController::class, 'getStudentPerformanceHistory']);
        Route::get('/classes/{class}/performance', [PerformanceEvaluationController::class, 'getClassPerformanceSummary']);
        Route::get('/students/{student}/recommendations', [PerformanceEvaluationController::class, 'getStudentRecommendations']);

        // Companions routes
        Route::post('/classes/{class}/companions/generate', [CompanionsController::class, 'generate']);
        Route::patch('/classes/{class}/companions/{target_date}/lock', [CompanionsController::class, 'lock']);
        Route::post('/classes/{class}/companions/{target_date}/publish', [CompanionsController::class, 'publish']);
        Route::get('/me/companions', [CompanionsController::class, 'getMyCompanions']);
    });
});

// Legacy route (keeping for compatibility)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
