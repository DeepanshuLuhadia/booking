<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerDiscoveryController;

Route::middleware('throttle:60,1')->get('/vendors/{vendor}/employees/{employee}/slots', [CustomerDiscoveryController::class, 'getSlots']);
