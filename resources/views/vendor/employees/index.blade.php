<x-vendor-layout>
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
        <div>
            <h1 class="text-4xl font-black italic tracking-tight uppercase">Specialist <span class="text-blue-600">Roster.</span></h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">OPERATIONAL TEAM MANAGEMENT</p>
        </div>
        <a @if($vendor->isProfileComplete()) href="{{ route('vendor.employees.create') }}" @endif
           class="w-full md:w-auto px-8 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all flex items-center justify-center gap-3 italic {{ !$vendor->isProfileComplete() ? 'opacity-20 cursor-not-allowed grayscale' : '' }}">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Enlist Professional
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($employees as $employee)
            <div class="bg-white/5 p-2 shadow-2xl shadow-slate-200/50 border border-white/10 rounded-[3rem] group hover:scale-[1.02] transition-all">
                <div class="p-8">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="w-20 h-20 rounded-[2rem] bg-slate-900 overflow-hidden flex items-center justify-center font-black italic text-2xl text-white shadow-xl">
                            @if($employee->photo)
                                <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover opacity-90">
                            @else
                                {{ substr($employee->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-black italic text-white uppercase tracking-tight">{{ $employee->name }}</h3>
                            <div class="mt-2">
                                <span class="px-3 py-1 {{ $employee->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }} rounded-lg text-[8px] font-black uppercase tracking-widest border italic">
                                    {{ $employee->is_active ? 'ACTIVE DUTY' : 'STANDBY' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 mb-10 bg-white/5 p-6 rounded-[2rem] border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest italic">Operational Window</span>
                            <span class="text-[11px] font-black text-white italic">{{ \Carbon\Carbon::parse($employee->working_start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($employee->working_end_time)->format('h:i A') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest italic">Temporal Slot</span>
                            <span class="text-[11px] font-black text-blue-600 italic tracking-tight">{{ $employee->slot_duration }} MINUTES</span>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <a href="{{ route('vendor.employees.edit', $employee) }}" class="flex-grow h-14 bg-white/5 text-white rounded-xl text-[9px] md:text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all flex items-center justify-center italic text-center px-2">Modify Protocol</a>
                        @if($employee->qr_code_path)
                            <a href="{{ asset('storage/' . $employee->qr_code_path) }}" download="qr-{{ $employee->slug }}.svg" title="Download QR Code" class="w-14 h-14 bg-sky-500/20 text-sky-300 rounded-xl hover:bg-sky-500/30 transition-all flex items-center justify-center border border-sky-500/30">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2a2 2 0 002-2v-5a2 2 0 00-2-2H4a2 2 0 00-2 2v5a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </a>
                        @endif
                        <form action="{{ route('vendor.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Decommission specialist?')">
                            @csrf @method('DELETE')
                            <button class="w-14 h-14 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all flex items-center justify-center border border-rose-100">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white/5 p-24 text-center border-4 border-dashed border-white/10 rounded-[4rem]">
                <div class="opacity-10 mb-8 flex justify-center">
                    <svg class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="text-xl font-black text-slate-300 uppercase italic tracking-widest mb-10">Roster Empty</p>
                <a @if($vendor->isProfileComplete()) href="{{ route('vendor.employees.create') }}" @endif
                   class="px-12 py-5 bg-slate-900 text-white rounded-[2rem] text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all italic shadow-2xl shadow-slate-900/20 {{ !$vendor->isProfileComplete() ? 'opacity-20 cursor-not-allowed grayscale' : '' }}">Initialize Team</a>
            </div>
        @endforelse
    </div>
</x-vendor-layout>
