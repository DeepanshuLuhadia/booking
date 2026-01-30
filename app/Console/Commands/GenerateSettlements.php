<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\Booking;
use App\Models\Settlement;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateSettlements extends Command
{
    protected $signature = 'platform:generate-settlements';
    protected $description = 'Generate vendor settlements for the last 15 days';

    public function handle()
    {
        $vendors = Vendor::where('status', 'active')->get();
        $end = Carbon::yesterday();
        $start = $end->copy()->subDays(14); // Full 15-day period

        $this->info("Generating settlements from {$start->toDateString()} to {$end->toDateString()}");

        foreach ($vendors as $vendor) {
            // Get all confirmed bookings in the period
            $bookings = Booking::where('vendor_id', $vendor->id)
                ->where('status', 'confirmed')
                ->whereBetween('booking_date', [$start->toDateString(), $end->toDateString()])
                ->get();

            $bookingCount = $bookings->count();
            // Important: Settlement is ONLY for advance booking payment (service fee component)
            // We subtract emergency_fee from online_paid_amount because emergency fees are NOT part of the settlement
            $totalOnlinePaid = $bookings->sum('online_paid_amount');
            $emergencyAmount = $bookings->sum('emergency_fee');
            $bookingAmount = $totalOnlinePaid - $emergencyAmount;
            
            $referralAmount = $vendor->referral_balance;
 
            // Only create settlement if there are bookings or referral balance to pay
            if ($bookingCount > 0 || $referralAmount > 0) {
                // Total Amount for settlement excludes emergency fees (which vendor already has or receives separately)
                $totalAmount = $bookingAmount + $referralAmount;
 
                 Settlement::create([
                     'vendor_id' => $vendor->id,
                     'period_start' => $start->toDateString(),
                     'period_end' => $end->toDateString(),
                     'booking_count' => $bookingCount,
                     'booking_amount' => $bookingAmount,
                     'emergency_booking_amount' => 0, // No longer paying this through settlements
                     'referral_amount' => $referralAmount,
                     'total_amount' => $totalAmount,
                     'status' => 'pending',
                 ]);

                // Reset vendor's referral balance after including in settlement
                if ($referralAmount > 0) {
                    $vendor->update(['referral_balance' => 0]);
                }

                $this->info("Settlement generated for: {$vendor->business_name} - ₹{$totalAmount} (Bookings: {$bookingCount}, Referral: ₹{$referralAmount})");
            }
        }

        $this->info('Settlement generation completed.');
    }
}
