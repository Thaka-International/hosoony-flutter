<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PerformanceEvaluationController;
use App\Http\Controllers\PhoneAuthController;

// Public routes
Route::get('/', [PwaController::class, 'index'])->name('home');

// Phone authentication routes
Route::get('/phone-login', [PhoneAuthController::class, 'showLoginForm'])->name('phone.login');
Route::post('/phone-auth/send-code', [PhoneAuthController::class, 'sendCode'])->name('phone.send-code');
Route::post('/phone-auth/verify-code', [PhoneAuthController::class, 'verifyCode'])->name('phone.verify-code');
Route::post('/phone-auth/resend-code', [PhoneAuthController::class, 'resendCode'])->name('phone.resend-code');
Route::post('/phone-logout', [PhoneAuthController::class, 'logout'])->name('phone.logout');

// Traditional login routes (fallback)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Payment webhooks (public routes)
Route::post('/webhooks/paypal', [PaymentController::class, 'paypalWebhook'])->name('payment.paypal.webhook');
Route::post('/webhooks/fastlane', [PaymentController::class, 'fastlaneWebhook'])->name('payment.fastlane.webhook');

// Payment callbacks (public routes)
Route::get('/payment/paypal/success', [PaymentController::class, 'paypalSuccess'])->name('payment.paypal.success');
Route::get('/payment/paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('payment.paypal.cancel');
Route::get('/payment/fastlane/success', [PaymentController::class, 'fastlaneSuccess'])->name('payment.fastlane.success');
Route::get('/payment/fastlane/cancel', [PaymentController::class, 'fastlaneCancel'])->name('payment.fastlane.cancel');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Student routes
    Route::prefix('student')->middleware(['role:student'])->group(function () {
        Route::get('/dashboard', [PwaController::class, 'studentDashboard'])->name('student.dashboard');
        Route::get('/tasks', [PwaController::class, 'studentTasks'])->name('student.tasks');
        Route::get('/ranking', [PwaController::class, 'studentRanking'])->name('student.ranking');
        Route::get('/points', [PwaController::class, 'studentPoints'])->name('student.points');
        Route::get('/schedule', [PwaController::class, 'studentSchedule'])->name('student.schedule');
        Route::get('/subscription', [PwaController::class, 'studentSubscription'])->name('student.subscription');
        Route::get('/recommendations', [PwaController::class, 'studentRecommendations'])->name('student.recommendations');
        
        // Payment routes
        Route::get('/payment', [PaymentController::class, 'showPaymentForm'])->name('student.payment');
        Route::post('/payment/paypal', [PaymentController::class, 'processPayPalPayment'])->name('student.payment.paypal');
        Route::post('/payment/fastlane', [PaymentController::class, 'processFastlanePayment'])->name('student.payment.fastlane');
        Route::get('/payment/status', [PaymentController::class, 'checkPaymentStatus'])->name('student.payment.status');
    });

    // Teacher routes
    Route::prefix('teacher')->middleware(['role:teacher,teacher_support,sub_admin'])->group(function () {
        Route::get('/dashboard', [PwaController::class, 'teacherDashboard'])->name('teacher.dashboard');
        Route::get('/timeline', [PwaController::class, 'teacherTimeline'])->name('teacher.timeline');
        Route::get('/attendance', [PwaController::class, 'teacherAttendance'])->name('teacher.attendance');
        Route::get('/segments', [PwaController::class, 'teacherSegments'])->name('teacher.segments');
        Route::get('/reports', [PwaController::class, 'teacherReports'])->name('teacher.reports');
        Route::get('/bulk-entry', [PwaController::class, 'teacherBulkEntry'])->name('teacher.bulk-entry');
        
        // Performance evaluation routes
        Route::get('/sessions/{session}/evaluate', [PerformanceEvaluationController::class, 'showEvaluationForm'])->name('teacher.session.evaluate');
        Route::post('/sessions/{session}/evaluations', [PerformanceEvaluationController::class, 'storeEvaluation'])->name('teacher.session.evaluation.store');
        Route::post('/sessions/{session}/evaluations/bulk', [PerformanceEvaluationController::class, 'bulkEvaluate'])->name('teacher.session.evaluation.bulk');
        Route::get('/evaluations/{evaluation}', [PerformanceEvaluationController::class, 'getEvaluation'])->name('teacher.evaluation.show');
        Route::get('/students/{student}/performance', [PerformanceEvaluationController::class, 'getStudentPerformanceHistory'])->name('teacher.student.performance');
        Route::get('/classes/{class}/performance', [PerformanceEvaluationController::class, 'getClassPerformanceSummary'])->name('teacher.class.performance');
    });
});
