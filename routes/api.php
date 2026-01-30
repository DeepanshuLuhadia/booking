<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerDiscoveryController;

Route::get('/vendors/{vendor}/employees/{employee}/slots', [CustomerDiscoveryController::class, 'getSlots']);
