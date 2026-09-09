<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerDiscoveryController;
use App\Http\Controllers\RazorpayWebhookController;

Route::middleware('throttle:60,1')->get('/vendors/{vendor}/employees/{employee}/slots', [CustomerDiscoveryController::class, 'getSlots']);

// Razorpay -> our server, not a browser request: no session/CSRF (the api
// group already omits both), no auth. Authenticity comes entirely from the
// X-Razorpay-Signature check inside the controller.
Route::middleware('throttle:120,1')->post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])
    ->name('webhooks.razorpay');
