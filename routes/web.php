<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VendorRegistrationController;
use App\Http\Controllers\Auth\CustomerRegistrationController;
use App\Http\Controllers\Auth\SessionController;

// Public customer-facing pages. Employees are kept out (bounced to their panel)
// so staff accounts can't wander onto the discovery/booking site.
Route::middleware(['employee.panel.only'])->group(function () {
    Route::get('/', [\App\Http\Controllers\CustomerDiscoveryController::class, 'index'])->name('home');
    Route::get('/discover', [\App\Http\Controllers\CustomerDiscoveryController::class, 'index'])->name('discover');
    Route::get('/vendors/{vendor:slug}', [\App\Http\Controllers\CustomerDiscoveryController::class, 'show'])->name('vendor.show');
});
Route::get('/vendors/{vendor:slug}/queue-status', [\App\Http\Controllers\CustomerDiscoveryController::class, 'queueStatus'])->name('vendor.queue-status');
// Latest 5 reviews, optionally filtered by star rating (?rating=N)
Route::get('/vendors/{vendor:slug}/reviews-list', [\App\Http\Controllers\CustomerDiscoveryController::class, 'reviewsList'])->name('vendor.reviews.list');

// PWA manifest (dynamic so icon URLs are absolute; branded as the project + our icon)
Route::get('/manifest.webmanifest', [\App\Http\Controllers\ManifestController::class, 'site'])->name('manifest.site');

// Guest-accessible booking (no login required)
Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('bookings.store');

// Guest-accessible vendor reviews (no login required)
Route::post('/vendors/{vendor:slug}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('vendor.reviews.store');

Route::middleware(['auth'])->group(function () {
    // Approval-pending holding screen (must sit OUTSIDE the subscription.active
    // vendor group so a pending vendor can reach it without a redirect loop).
    Route::get('/vendor/approval-pending', [\App\Http\Controllers\Vendor\ApprovalController::class, 'pending'])
        ->name('vendor.approval.pending');

    Route::get('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'show'])->name('otp.verify');
    Route::post('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])
        ->middleware('throttle:5,1'); // 5 attempts per min
    Route::get('/resend-otp', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])
        ->middleware('throttle:3,5') // 3 resends per 5 mins
        ->name('otp.resend');
});

// For saving FCM token (accessible for guests and authenticated users via JS)
Route::post('/fcm/token', [\App\Http\Controllers\FcmTokenController::class, 'save'])->name('fcm.token.save');

// Guest Authentication & Registration
Route::middleware(['redirect.role.auth'])->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:5,1'); // brute-force guard

    Route::get('/register/vendor', [VendorRegistrationController::class, 'create'])->name('register.vendor');
    Route::post('/register/vendor', [VendorRegistrationController::class, 'store']);

    Route::get('/register', [CustomerRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [CustomerRegistrationController::class, 'store']);
});

Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

// Payment
Route::middleware(['auth'])->group(function () {
    Route::get('/payment/razorpay', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payment.razorpay');
    Route::post('/payment/razorpay/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
});

// Vendor Panel
Route::middleware(['auth', 'subscription.active'])->prefix('vendor')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('vendor.dashboard');
    Route::get('/bookings', [\App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('vendor.bookings.index');
    Route::post('/bookings', [\App\Http\Controllers\Vendor\BookingController::class, 'store'])->name('vendor.bookings.store');
    Route::patch('/bookings/{booking}/complete', [\App\Http\Controllers\Vendor\BookingController::class, 'complete'])->name('vendor.bookings.complete');
    Route::delete('/bookings/{booking}', [\App\Http\Controllers\Vendor\BookingController::class, 'destroy'])->name('vendor.bookings.destroy');
    Route::post('/next-token', [\App\Http\Controllers\Vendor\BookingController::class, 'nextToken'])->name('vendor.next-token');
    Route::post('/skip-token/{booking}', [\App\Http\Controllers\Vendor\BookingController::class, 'skipToken'])->name('vendor.skip-token');
    
    Route::get('/profile', [\App\Http\Controllers\Vendor\ProfileController::class, 'edit'])->name('vendor.profile.edit');
    Route::post('/profile', [\App\Http\Controllers\Vendor\ProfileController::class, 'update'])->name('vendor.profile.update');
    Route::get('/plans', [\App\Http\Controllers\Vendor\ProfileController::class, 'plans'])->name('vendor.plans');
    Route::post('/status/toggle', [\App\Http\Controllers\Vendor\ProfileController::class, 'toggleStatus'])->name('vendor.status.toggle');

    Route::get('/reviews', [\App\Http\Controllers\Vendor\ReviewController::class, 'index'])->name('vendor.reviews.index');
    Route::post('/reviews/{review}/report', [\App\Http\Controllers\Vendor\ReviewController::class, 'report'])->name('vendor.reviews.report');

    Route::resource('/employees', \App\Http\Controllers\Vendor\EmployeeController::class, ['as' => 'vendor']);
    Route::post('/plans/{plan}/checkout', [\App\Http\Controllers\PaymentController::class, 'planCheckout'])->name('vendor.plan.checkout');
    Route::post('/plans/callback', [\App\Http\Controllers\PaymentController::class, 'planCallback'])->name('vendor.plan.callback');
});

// Admin Panel
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/plans', \App\Http\Controllers\Admin\PlanController::class, ['as' => 'admin']);
    Route::resource('/vendors', \App\Http\Controllers\Admin\VendorController::class, ['as' => 'admin'])->only(['index', 'show', 'destroy', 'update']);
    Route::post('/vendors/{vendor}/approve',   [\App\Http\Controllers\Admin\VendorController::class, 'approve'])->name('admin.vendors.approve');
    Route::post('/vendors/{vendor}/reject',    [\App\Http\Controllers\Admin\VendorController::class, 'reject'])->name('admin.vendors.reject');
    Route::post('/vendors/{vendor}/suspend',   [\App\Http\Controllers\Admin\VendorController::class, 'suspend'])->name('admin.vendors.suspend');
    Route::post('/vendors/{vendor}/reinstate', [\App\Http\Controllers\Admin\VendorController::class, 'reinstate'])->name('admin.vendors.reinstate');
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::post('/reviews/{review}/unreport', [\App\Http\Controllers\Admin\ReviewController::class, 'unreport'])->name('admin.reviews.unreport');

    Route::get('/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('admin.settlements.index');
    Route::get('/settlements/{id}', [\App\Http\Controllers\Admin\SettlementController::class, 'show'])->name('admin.settlements.show');
    Route::post('/settlements/{id}/mark-paid', [\App\Http\Controllers\Admin\SettlementController::class, 'markAsPaid'])->name('admin.settlements.markAsPaid');
});

// Employee Panel
Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/mark-done', [\App\Http\Controllers\Employee\DashboardController::class, 'markDone'])->name('mark-done');
    Route::post('/cancel', [\App\Http\Controllers\Employee\DashboardController::class, 'cancel'])->name('cancel');
    Route::post('/toggle-pause', [\App\Http\Controllers\Employee\DashboardController::class, 'togglePause'])->name('toggle-pause');
});
