<x-employee-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Hello, {{ $employee->name }}</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Manage your daily appointments</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest {{ $employee->is_paused ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                    Status: {{ $employee->is_paused ? 'PAUSED' : 'ACTIVE' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="glass-card p-8 flex flex-col justify-center items-center text-center">
                <p class="text-slate-400 text-xs uppercase font-black tracking-widest mb-2">Completed Today</p>
                <h3 class="text-5xl font-black text-emerald-400">{{ $stats['completed'] }}</h3>
            </div>
            <div class="glass-card p-8 flex flex-col justify-center items-center text-center">
                <p class="text-slate-400 text-xs uppercase font-black tracking-widest mb-2">Remaining Today</p>
                <h3 class="text-5xl font-black text-amber-400">{{ $stats['remaining'] }}</h3>
            </div>
        </div>

        <div class="glass-card p-6 sm:p-10">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mb-8 border-b border-white/10 pb-6">
                <div>
                    <h3 class="text-2xl font-black text-white">Current Appointment</h3>
                    <p class="text-xs text-slate-400 uppercase font-black tracking-widest mt-1">Next customer in queue</p>
                </div>
            </div>

            @if($currentBooking)
                <div class="bg-white/5 rounded-[2rem] p-8 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-8 mb-10">
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer Name</p>
                            <p class="text-2xl font-black text-white">{{ $currentBooking->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Time Slot</p>
                            <p class="text-lg font-bold text-white">{{ \Carbon\Carbon::parse($currentBooking->slot_start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($currentBooking->slot_end_time)->format('h:i A') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            {{ ucfirst($currentBooking->booking_type) }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('employee.mark-done') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $currentBooking->id }}">
                    <button type="submit" class="w-full py-5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-black text-lg uppercase tracking-widest shadow-[0_10px_30px_-10px_rgba(16,185,129,0.5)] transition-all transform hover:-translate-y-1">
                        ✓ Mark Appointment Done
                    </button>
                </form>
            @else
                <div class="text-center py-12 bg-white/5 rounded-[2rem] border border-white/10 mb-8">
                    <svg class="w-16 h-16 text-slate-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-lg font-bold text-white">No pending appointments.</p>
                    <p class="text-sm text-slate-400 mt-2">You have completed all active appointments for now.</p>
                </div>
            @endif

            <div class="border-t border-white/10 pt-8 mt-8">
                <form action="{{ route('employee.toggle-pause') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-5 rounded-2xl border-2 {{ $employee->is_paused ? 'border-emerald-500 text-emerald-500 hover:bg-emerald-500/10' : 'border-amber-500 text-amber-500 hover:bg-amber-500/10' }} font-black text-base uppercase tracking-widest transition-all">
                        {{ $employee->is_paused ? '▶ Resume Appointments' : '⏸ Pause Appointments (Take a Break)' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-employee-layout>
