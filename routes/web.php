<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VendorRegistrationController;
use App\Http\Controllers\Auth\CustomerRegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;

// Public customer-facing pages. Employees are kept out (bounced to their panel)
// so staff accounts can't wander onto the discovery/booking site.
Route::middleware(['employee.panel.only'])->group(function () {
    Route::get('/', [\App\Http\Controllers\CustomerDiscoveryController::class, 'index'])->name('home');
    Route::get('/discover', [\App\Http\Controllers\CustomerDiscoveryController::class, 'index'])->name('discover');

    // Batch endpoint the landing listing's infinite scroll pulls from — the
    // search/filter counterpart of category.vendors.
    Route::get('/discover/vendors', [\App\Http\Controllers\CustomerDiscoveryController::class, 'vendorsFeed'])->name('discover.vendors');

    // Search-as-you-type dropdown behind both search bars. Throttled because
    // it fires while the customer is still typing; the `type` parameter keeps
    // a category page's suggestions inside its own category.
    Route::get('/discover/suggestions', [\App\Http\Controllers\CustomerDiscoveryController::class, 'suggestions'])
        ->middleware('throttle:60,1')
        ->name('discover.suggestions');
    Route::get('/vendors/{vendor:slug}', [\App\Http\Controllers\CustomerDiscoveryController::class, 'show'])->name('vendor.show');

    // Category detail page + the batch endpoint its infinite scroll pulls from
    Route::get('/category/{slug}', [\App\Http\Controllers\CustomerDiscoveryController::class, 'category'])->name('category.show');
    Route::get('/category/{slug}/vendors', [\App\Http\Controllers\CustomerDiscoveryController::class, 'categoryVendors'])->name('category.vendors');

    Route::get('/qr/{vendor:slug}', function (\App\Models\Vendor $vendor) {
        return redirect()->route('vendor.show', ['vendor' => $vendor->slug, 'qr' => 1]);
    })->name('vendor.qr');

    // Every booking the visitor holds, across all vendors. Guest-accessible:
    // identity comes from the phone stored at booking time (session + cookie),
    // which is the only handle a guest has.
    Route::get('/my-bookings', [\App\Http\Controllers\MyBookingsController::class, 'index'])->name('bookings.mine');
    Route::get('/my-bookings/status', [\App\Http\Controllers\MyBookingsController::class, 'status'])->name('bookings.mine.status');

    // Customers cancelling their own booking. Ownership is proved by the device
    // having booked with that number, not by the id in the URL.
    Route::post('/my-bookings/{booking}/cancel', [\App\Http\Controllers\MyBookingsController::class, 'cancel'])
        ->middleware('throttle:10,1')
        ->name('bookings.mine.cancel');
});
Route::get('/vendors/{vendor:slug}/queue-status', [\App\Http\Controllers\CustomerDiscoveryController::class, 'queueStatus'])->name('vendor.queue-status');
// Latest 5 reviews, optionally filtered by star rating (?rating=N)
Route::get('/vendors/{vendor:slug}/reviews-list', [\App\Http\Controllers\CustomerDiscoveryController::class, 'reviewsList'])->name('vendor.reviews.list');

/*
| Public content pages. Deliberately outside the `employee.panel.only` group:
| the legal pages and the support form have to be reachable by everyone,
| including staff accounts, and they carry no discovery/booking surface.
*/
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,10') // 5 enquiries per 10 minutes per IP
    ->name('contact.store');

// PWA manifest (dynamic so icon URLs are absolute; branded as the project + our icon)
Route::get('/manifest.webmanifest', [\App\Http\Controllers\ManifestController::class, 'site'])->name('manifest.site');

// Guest-accessible booking (no login required)
Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('bookings.store');

/*
| Direct-to-vendor UPI payments — the "pay again" fallback.
|
| Not part of the booking flow: the customer is handed to their UPI app from
| the confirmation screen itself and the booking is confirmed either way. This
| URL exists for the customer who dismissed the payment chooser without paying
| and wants the QR or the app link back.
|
| Guest-accessible on purpose, like booking itself: appointments here are made
| without an account (a shop can switch the details form off entirely), so
| requiring a login to pay would lock out most of the people who owe money.
| Ownership is proved inside the controller through CustomerBookingService::
| owns() — the phone this device actually booked with, its guest key, or the
| signed-in customer's id — never by the id in the URL.
*/
Route::get('/bookings/{booking}/payment', [\App\Http\Controllers\DirectPaymentController::class, 'show'])
    ->name('payment.show');

// Guest-accessible vendor reviews (no login required)
Route::post('/vendors/{vendor:slug}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('vendor.reviews.store');

/*
| "Continue with Google" — the review modal's sign-in.
|
| Deliberately outside the `redirect.role.auth` group: that middleware bounces
| anyone already authenticated, and this is called by fetch() from a page the
| visitor stays on. Throttled because it is public and does a network round
| trip to Google's key endpoint on every call. Customers only — the controller
| refuses any address belonging to a staff account.
*/
Route::post('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('auth.google');

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

    // Password recovery. Throttled because both endpoints send mail and the
    // request one accepts any address.
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('password.update');
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
    
    /*
    | Direct-to-vendor UPI payments — the shop's own ledger.
    |
    | Bookkeeping only: no booking waits on anything here. Inside the vendor
    | group and re-checked per booking in the controller, because a shop may
    | only tick off payments made into its own account.
    */
    Route::get('/payments', [\App\Http\Controllers\Vendor\PaymentVerificationController::class, 'index'])->name('vendor.payments.index');
    Route::post('/payments/{booking}/approve', [\App\Http\Controllers\Vendor\PaymentVerificationController::class, 'approve'])->name('vendor.payments.approve');

    // Notification tab: the stored copies of every push the shop was sent
    // (see NotificationService::sendWebPush), so a switched-off phone loses
    // nothing.
    Route::get('/notifications', [\App\Http\Controllers\NotificationCenterController::class, 'index'])->name('vendor.notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationCenterController::class, 'readAll'])->name('vendor.notifications.readAll');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationCenterController::class, 'read'])->name('vendor.notifications.read');

    Route::get('/profile', [\App\Http\Controllers\Vendor\ProfileController::class, 'edit'])->name('vendor.profile.edit');
    Route::post('/profile', [\App\Http\Controllers\Vendor\ProfileController::class, 'update'])->name('vendor.profile.update');
    // Live QR preview for the direct-payment settings card, rendered from the
    // values currently typed into the form rather than the saved ones.
    Route::get('/profile/upi-qr-preview', [\App\Http\Controllers\Vendor\ProfileController::class, 'upiQrPreview'])->name('vendor.profile.upi-qr');
    Route::get('/plans', [\App\Http\Controllers\Vendor\ProfileController::class, 'plans'])->name('vendor.plans');
    Route::post('/status/toggle', [\App\Http\Controllers\Vendor\ProfileController::class, 'toggleStatus'])->name('vendor.status.toggle');

    // Booking reports — on-screen builder plus CSV/Excel download. Free-trial
    // and Premium shops only; Basic/Standard are bounced to the plans page.
    Route::middleware('reports.access')->group(function () {
        Route::get('/reports', [\App\Http\Controllers\Vendor\ReportController::class, 'index'])->name('vendor.reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Vendor\ReportController::class, 'export'])->name('vendor.reports.export');
    });

    Route::get('/reviews', [\App\Http\Controllers\Vendor\ReviewController::class, 'index'])->name('vendor.reviews.index');
    Route::post('/reviews/{review}/report', [\App\Http\Controllers\Vendor\ReviewController::class, 'report'])->name('vendor.reviews.report');

    Route::resource('/employees', \App\Http\Controllers\Vendor\EmployeeController::class, ['as' => 'vendor']);
    Route::post('/plans/{plan}/checkout', [\App\Http\Controllers\PaymentController::class, 'planCheckout'])->name('vendor.plan.checkout');
    Route::post('/plans/callback', [\App\Http\Controllers\PaymentController::class, 'planCallback'])->name('vendor.plan.callback');
});

// Admin Panel
// `admin.only` is not optional here: this group ran on bare `auth`, so any
// signed-in customer or vendor could open vendor management, settlements and
// the platform booking report simply by typing the URL.
Route::middleware(['auth', 'admin.only'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // Every booking on the platform, paginated and filterable. The tracking
    // counterpart of /admin/reports, which exists to export rather than browse.
    Route::get('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('admin.bookings.index');

    // Platform-wide booking reports, mirroring the vendor panel's.
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('admin.reports.export');

    Route::resource('/plans', \App\Http\Controllers\Admin\PlanController::class, ['as' => 'admin']);
    Route::resource('/vendors', \App\Http\Controllers\Admin\VendorController::class, ['as' => 'admin'])->only(['index', 'show', 'destroy', 'update']);
    Route::post('/vendors/{vendor}/approve',   [\App\Http\Controllers\Admin\VendorController::class, 'approve'])->name('admin.vendors.approve');
    Route::post('/vendors/{vendor}/reject',    [\App\Http\Controllers\Admin\VendorController::class, 'reject'])->name('admin.vendors.reject');
    Route::post('/vendors/{vendor}/suspend',   [\App\Http\Controllers\Admin\VendorController::class, 'suspend'])->name('admin.vendors.suspend');
    Route::post('/vendors/{vendor}/reinstate', [\App\Http\Controllers\Admin\VendorController::class, 'reinstate'])->name('admin.vendors.reinstate');
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::post('/reviews/{review}/unreport', [\App\Http\Controllers\Admin\ReviewController::class, 'unreport'])->name('admin.reviews.unreport');

    // Contact-form inbox: read enquiries and reply to the sender by email.
    Route::get('/contacts', [\App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('admin.contacts.index');
    Route::get('/contacts/{contact}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'show'])->name('admin.contacts.show');
    Route::post('/contacts/{contact}/reply', [\App\Http\Controllers\Admin\ContactMessageController::class, 'reply'])
        ->middleware('throttle:20,10')
        ->name('admin.contacts.reply');
    Route::patch('/contacts/{contact}/status', [\App\Http\Controllers\Admin\ContactMessageController::class, 'updateStatus'])->name('admin.contacts.status');
    Route::delete('/contacts/{contact}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('admin.contacts.destroy');

    // Content and contact details behind the public About/Contact/legal pages.
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/reset', [\App\Http\Controllers\Admin\SettingController::class, 'reset'])->name('admin.settings.reset');

    Route::get('/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('admin.settlements.index');
    Route::get('/settlements/{id}', [\App\Http\Controllers\Admin\SettlementController::class, 'show'])->name('admin.settlements.show');
    Route::post('/settlements/{id}/mark-paid', [\App\Http\Controllers\Admin\SettlementController::class, 'markAsPaid'])->name('admin.settlements.markAsPaid');
});

// Employee Panel
Route::middleware(['auth', 'ensure.vendor.active'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/mark-done', [\App\Http\Controllers\Employee\DashboardController::class, 'markDone'])->name('mark-done');
    Route::post('/cancel', [\App\Http\Controllers\Employee\DashboardController::class, 'cancel'])->name('cancel');
    // Passing over a customer the specialist cannot serve. Same queue mechanics
    // as cancel; different message to the customer (rebook / call the shop).
    Route::post('/skip', [\App\Http\Controllers\Employee\DashboardController::class, 'skip'])->name('skip');
    Route::post('/toggle-pause', [\App\Http\Controllers\Employee\DashboardController::class, 'togglePause'])->name('toggle-pause');

    // Notification tab — the employee-panel twin of vendor.notifications.*.
    Route::get('/notifications', [\App\Http\Controllers\NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationCenterController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationCenterController::class, 'read'])->name('notifications.read');
});

Route::get('/employee/{employee}', [\App\Http\Controllers\EmployeePublicBookingController::class, 'show'])->name('employee.public.show');
