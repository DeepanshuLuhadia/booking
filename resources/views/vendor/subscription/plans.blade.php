<x-vendor-layout>
    <div class="mb-12">
        <h1 class="text-4xl font-black italic tracking-tight uppercase text-slate-900">Tier <span class="text-blue-600">Evolution.</span></h1>
        <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">SUBSCRIPTION MATRIX & SCALING</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($plans as $plan)
            <div class="bg-white p-2 shadow-2xl shadow-slate-200/50 border {{ $vendor->subscription_plan_id == $plan->id ? 'border-blue-600' : 'border-slate-100' }} rounded-[3rem] transition-all group relative overflow-hidden">
                @if($vendor->subscription_plan_id == $plan->id)
                <div class="absolute top-8 right-8 bg-blue-600 text-white px-4 py-1.5 rounded-xl text-[8px] font-black uppercase italic tracking-widest shadow-lg shadow-blue-500/20">Active Rank</div>
                @endif

                <div class="p-10">
                    <div class="mb-10">
                        <h3 class="text-2xl font-black italic text-slate-900 uppercase tracking-tighter mb-2">{{ $plan->name }}</h3>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black italic text-blue-600">₹{{ number_format($plan->price) }}</span>
                            <span class="text-[9px] font-black text-slate-300 uppercase italic tracking-widest">/ Cycle</span>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-[2rem] p-8 border border-slate-100 mb-10">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mb-6">Operational Limit</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-center font-black italic text-slate-900">{{ $plan->max_employees }}</div>
                            <span class="text-[10px] font-black text-slate-700 uppercase italic tracking-tight">Concurrent Specialist Slots</span>
                        </div>
                    </div>

                    <ul class="space-y-4 mb-12">
                        @foreach($plan->features as $feature)
                        <li class="flex items-center gap-4 text-[10px] font-black text-slate-700 italic tracking-tight uppercase">
                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>

                    @if($vendor->subscription_plan_id == $plan->id)
                    <button disabled class="w-full h-16 bg-slate-50 text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-widest italic cursor-default border border-slate-100">Current Rank Engaged</button>
                    @else
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'CONTACT ADMIN FOR TIER UPGRADE', type: 'info' } }))" class="w-full h-16 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all italic shadow-xl shadow-slate-900/20">Initiate Upgrade</button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-vendor-layout>
