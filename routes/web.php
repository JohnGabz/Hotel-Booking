<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Webhook\PaymentController as WebhookPaymentController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PhysicalRoomController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StaffController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/faqs', [PageController::class, 'faqs'])->name('faqs');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{room:slug}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/rooms/{room:slug}/availability', [RoomController::class, 'availability'])->name('rooms.availability');
Route::get('/bookings/search', [BookingController::class, 'search'])->name('bookings.search');
Route::post('/rooms/{room:slug}/book', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking}/success', [BookingController::class, 'success'])->name('bookings.success')->middleware('signed');
Route::get('/bookings/{booking}/status', [BookingController::class, 'statusApi'])->name('bookings.status-api');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/notifications/unread', [App\Http\Controllers\NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    Route::get('/email/verify', [AuthController::class, 'showVerifyEmailNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        Auth::guard('web')->setUser($request->user()->fresh());

        return redirect()->route('dashboard')->with('success', 'Email verified successfully.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::middleware('verified')->group(function () {
        Route::post('/rooms/{room:slug}/review', [ReviewController::class, 'store'])->name('reviews.store');
        Route::get('/dashboard', [BookingController::class, 'dashboard'])->name('dashboard');
        Route::post('/bookings/{booking}/payment-proof', [BookingController::class, 'uploadPaymentProof'])->name('bookings.payment-proof');
        Route::post('/bookings/{booking}/pay', [App\Http\Controllers\PaymentController::class, 'create'])->name('bookings.pay');
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/request-refund', [BookingController::class, 'requestRefund'])->name('bookings.request-refund');
    });

    Route::post('/rooms/{room}/physical-rooms', [PhysicalRoomController::class, 'store'])->name('physical-rooms.store');
    Route::delete('/physical-rooms/{physicalRoom}', [PhysicalRoomController::class, 'destroy'])->name('physical-rooms.destroy');
    Route::post('/bookings/{booking}/assign-room', [AdminController::class, 'assignPhysicalRoom'])->name('bookings.assign-room');

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
        Route::get('/rooms', [AdminController::class, 'rooms'])->name('rooms');
        Route::get('/guests', [AdminController::class, 'guests'])->name('guests');
        Route::get('/reports/export', ReportExportController::class)->name('reports.export');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/feedbacks', [AdminController::class, 'feedbacks'])->name('feedbacks');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/bookings/walkin', [AdminController::class, 'adminStoreWalkin'])->name('bookings.walkin');
        Route::post('/rooms', [AdminController::class, 'storeRoom'])->name('rooms.store');
        Route::put('/rooms/{room}', [AdminController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{room}', [AdminController::class, 'destroyRoom'])->name('rooms.destroy');
        Route::post('/rooms/{room}/status', [AdminController::class, 'updateRoomStatus'])->name('rooms.status');
        Route::post('/reviews/{review}/approve', [AdminController::class, 'approveReview'])->name('reviews.approve');
        Route::post('/bookings/{booking}/payment-status', [AdminController::class, 'updatePaymentStatus'])->name('bookings.payment-status');
        Route::post('/bookings/{booking}/refund-request', [AdminController::class, 'processRefundRequest'])->name('bookings.refund-request');
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        Route::post('/site-content', [AdminController::class, 'updateSiteContent'])->name('site-content.update');
        Route::post('/site-content/{section}', [AdminController::class, 'updateSiteContentSection'])->name('site-content.section.update');
        Route::get('/settings/landing/{section}/edit', [AdminController::class, 'landingSectionEdit'])->name('settings.landing.edit');
    });
});

Route::post('/admin/dev-reset', [AdminController::class, 'resetDatabase'])
    ->middleware('auth')
    ->name('admin.dev.reset');

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/reviews/submit-via-token/{token}', [App\Http\Controllers\ReviewTokenController::class, 'show'])->name('reviews.submit-via-token');
    Route::post('/reviews/submit-via-token/{token}', [App\Http\Controllers\ReviewTokenController::class, 'store'])->name('reviews.submit-via-token.store');
});

Route::post('/webhooks/payments', [WebhookPaymentController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.payments');
Route::post('/webhooks/xendit', [WebhookPaymentController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.xendit');
Route::post('/availability/check', [AvailabilityController::class, 'check'])->name('availability.check');
