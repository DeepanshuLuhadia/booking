<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$vendor = App\Models\Vendor::where('slug', 'heena-saloon-VppAg')->first();
$employee = $vendor->employees()->first();

// Get initial slots
$request = Illuminate\Http\Request::create('/api/vendors/'.$vendor->id.'/employees/'.$employee->id.'/slots', 'GET');
$response = $kernel->handle($request);
$res1 = json_decode($response->getContent());
echo "BEFORE - Queue Index: " . ($res1->queue_index ?? 'null') . ", Running: " . ($res1->running_token ?? 'null') . "\n";

// Perform Booking
$bookingRequest = Illuminate\Http\Request::create('/bookings', 'POST', [
    'vendor_id' => $vendor->id,
    'employee_id' => $employee->id,
    'slot_start' => '11:00',
    'slot_end' => 'Queue',
    'booking_type' => 'normal',
    'customer_name' => 'Automated Test',
    'customer_phone' => '1234567890',
    'payment_id' => 'pay_automated123'
]);
$bookingResponse = $kernel->handle($bookingRequest);
echo "BOOKING STATUS: " . $bookingResponse->getStatusCode() . "\n";

// Get updated slots
$request3 = Illuminate\Http\Request::create('/api/vendors/'.$vendor->id.'/employees/'.$employee->id.'/slots', 'GET');
$response3 = $kernel->handle($request3);
$res3 = json_decode($response3->getContent());
echo "AFTER - Queue Index: " . ($res3->queue_index ?? 'null') . ", Running: " . ($res3->running_token ?? 'null') . "\n";
