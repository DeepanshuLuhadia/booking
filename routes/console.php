<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Vendor;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $now = Carbon::now()->format('H:i:s');
    
    Vendor::where('status', 'active')
        ->whereNotNull('global_opening_time')
        ->whereNotNull('global_closing_time')
        ->get()
        ->each(function ($vendor) use ($now) {
            $isOpen = ($now >= $vendor->global_opening_time && $now <= $vendor->global_closing_time);
            if ($vendor->is_open !== $isOpen) {
                $vendor->updateQuietly(['is_open' => $isOpen]);
            }
        });
})->everyMinute();
