<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VendorRegistrationController;
use App\Http\Controllers\Auth\CustomerRegistrationController;
use App\Http\Controllers\Auth\SessionController;

Route::get('/', [\App\Http\Controllers\CustomerDiscoveryController::class, 'index'])->name('home');

Route::get('/discover', [\App\Http\Controllers\CustomerDiscoveryController::class, 'index'])->name('discover');
Route::get('/vendors/{vendor:slug}', [\App\Http\Controllers\CustomerDiscoveryController::class, 'show'])->name('vendor.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
    
    Route::get('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'show'])->name('otp.verify');
    Route::post('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verify']);
    Route::get('/resend-otp', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('otp.resend');
});

// Authentication
Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

// Vendor Registration
Route::get('/register/vendor', [VendorRegistrationController::class, 'create'])->name('register.vendor');
Route::post('/register/vendor', [VendorRegistrationController::class, 'store']);

// Customer Registration
Route::get('/register', [CustomerRegistrationController::class, 'create'])->name('register');
Route::post('/register', [CustomerRegistrationController::class, 'store']);

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
    
    Route::get('/profile', [\App\Http\Controllers\Vendor\ProfileController::class, 'edit'])->name('vendor.profile.edit');
    Route::post('/profile', [\App\Http\Controllers\Vendor\ProfileController::class, 'update'])->name('vendor.profile.update');
    Route::post('/status/toggle', [\App\Http\Controllers\Vendor\ProfileController::class, 'toggleStatus'])->name('vendor.status.toggle');

    Route::resource('/employees', \App\Http\Controllers\Vendor\EmployeeController::class, ['as' => 'vendor']);
});

// Admin Panel
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/plans', \App\Http\Controllers\Admin\PlanController::class, ['as' => 'admin']);
    Route::resource('/vendors', \App\Http\Controllers\Admin\VendorController::class, ['as' => 'admin'])->only(['index', 'show', 'destroy', 'update']);
    Route::get('/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('admin.settlements.index');
    Route::get('/settlements/{id}', [\App\Http\Controllers\Admin\SettlementController::class, 'show'])->name('admin.settlements.show');
    Route::post('/settlements/{id}/mark-paid', [\App\Http\Controllers\Admin\SettlementController::class, 'markAsPaid'])->name('admin.settlements.markAsPaid');
});
