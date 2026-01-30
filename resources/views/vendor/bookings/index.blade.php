<x-vendor-layout>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-black">All Bookings</h1>
            <p class="text-gray-400">Total history of your customer appointments.</p>
        </div>
        <div class="flex gap-4">
            <form action="{{ route('vendor.bookings.index') }}" method="GET" class="flex gap-2">
                <select name="status" onchange="this.form.submit()" class="glass-input text-sm">
                    <option value="">All Status</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </form>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/5 text-left text-xs uppercase text-gray-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Date & Time</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $booking->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->customer_phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $booking->employee->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                <div>{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->slot_start_time }} - {{ $booking->slot_end_time }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $color = match($booking->status) {
                                        'confirmed' => 'bg-green-500/20 text-green-400',
                                        'cancelled' => 'bg-red-500/20 text-red-400',
                                        'completed' => 'bg-blue-500/20 text-blue-400',
                                        default => 'bg-gray-500/20 text-gray-400'
                                    };
                                @endphp
                                <span class="px-3 py-1 {{ $color }} rounded-full text-xs font-bold uppercase">{{ $booking->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-gray-400 hover:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-white/5">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-vendor-layout>
