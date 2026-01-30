<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index()
    {
        $settlements = Settlement::with(['vendor.user'])->latest()->get();
        return view('admin.settlements.index', compact('settlements'));
    }

    public function show($id)
    {
        $settlement = Settlement::with(['vendor.user', 'vendor.bookings' => function($query) use ($id) {
            $settlement = Settlement::find($id);
            if ($settlement) {
                $query->where('status', 'confirmed')
                    ->whereBetween('booking_date', [$settlement->period_start, $settlement->period_end]);
            }
        }])->findOrFail($id);

        return view('admin.settlements.show', compact('settlement'));
    }

    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'upi_transaction_id' => 'required|string|max:255',
        ]);

        $settlement = Settlement::findOrFail($id);
        
        $settlement->update([
            'status' => 'completed',
            'payout_date' => now(),
            'upi_transaction_id' => $request->upi_transaction_id,
        ]);

        return redirect()->route('admin.settlements.index')
            ->with('success', 'Settlement marked as paid successfully!');
    }
}
