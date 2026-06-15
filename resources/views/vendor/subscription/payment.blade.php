<x-vendor-layout>
    <div class="relative min-h-[80vh] flex items-center justify-center py-20">
        <div class="relative z-10 w-full max-w-2xl px-6">

            @if(!isset($error))
            <!-- Loader State -->
            <div id="payment-loader" class="text-center animate-pulse">
                <div class="inline-flex items-center gap-4 px-6 py-3 bg-white/5 border border-white/10 rounded-full text-white/60 text-xs font-black uppercase tracking-[0.3em] mb-12">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Initializing Razorpay Secure Portal...
                </div>
                <h2 class="text-4xl font-black text-white italic tracking-tighter mb-4">Upgrading Your Plan.</h2>
                <p class="text-white/40 font-medium italic">Please do not refresh — authorizing payment for {{ $plan->name }}.</p>
            </div>
            @endif

            <!-- Payment Card -->
            <div id="payment-card" class="{{ isset($error) ? '' : 'hidden' }}">
                <div class="text-center mb-10">
                    <a href="{{ route('vendor.plans') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-[10px] font-black uppercase tracking-widest mb-8 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Plans
                    </a>
                    <h1 class="text-5xl font-black text-white mb-6 tracking-tighter leading-[0.9] italic">
                        Upgrade to <span class="text-blue-400">{{ $plan->name }}.</span>
                    </h1>
                </div>

                <div class="glass-card p-8 space-y-6">
                    <!-- Plan Summary -->
                    <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden">
                        <div class="absolute inset-0 bg-blue-600 opacity-5"></div>
                        <div class="flex justify-between items-end relative z-10">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-[0.3em] text-white/30 mb-2 italic">Plan Upgrade</p>
                                <h2 class="text-5xl font-black italic tracking-tighter">₹{{ number_format($plan->price) }}</h2>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-white/30 mb-2 italic">New Tier</p>
                                <span class="px-5 py-2 bg-blue-600/20 border border-blue-600/30 rounded-xl text-[10px] font-black uppercase tracking-widest italic text-blue-400">{{ $plan->name }}</span>
                            </div>
                        </div>
                    </div>

                    @if(isset($error))
                        <div class="p-6 bg-rose-500/10 border border-rose-500/20 rounded-[1.5rem] text-rose-400 text-center">
                            <p class="font-black italic uppercase tracking-widest text-sm">{{ $error }}</p>
                        </div>
                        @if($demoMode)
                        <button onclick="simulateSuccess()" class="w-full h-16 bg-emerald-600 text-white rounded-[1.5rem] text-sm font-black italic uppercase tracking-widest hover:bg-emerald-700 transition-all">
                            SIMULATE PAYMENT SUCCESS (DEMO)
                        </button>
                        @endif
                    @else
                        <div class="space-y-4">
                            <button id="rzp-button" class="btn-primary w-full h-20 !rounded-[1.5rem] !text-xl justify-center">
                                PAY ₹{{ number_format($plan->price) }} WITH RAZORPAY
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                            <button onclick="simulateSuccess()" class="w-full h-10 text-slate-400 hover:text-emerald-400 font-black italic text-[10px] uppercase tracking-[0.2em] transition-colors">
                                OR BYPASS FOR DEMO TESTING
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Hidden callback form -->
        <form action="{{ route('vendor.plan.callback') }}" method="POST" id="payment-form" class="hidden">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>

        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            function simulateSuccess() {
                document.getElementById('razorpay_payment_id').value = 'pay_demo_' + Math.random().toString(36).substr(2, 9);
                document.getElementById('razorpay_order_id').value = 'order_demo_' + Math.random().toString(36).substr(2, 9);
                document.getElementById('razorpay_signature').value = 'simulated_signature';
                document.getElementById('payment-form').submit();
            }

            document.addEventListener('DOMContentLoaded', function () {
                @if(isset($order))
                var options = {
                    "key": "{{ $keyId ?? '' }}",
                    "amount": "{{ $order->amount ?? 0 }}",
                    "currency": "INR",
                    "name": "{{ config('app.name') }}",
                    "description": "Plan Upgrade: {{ $plan->name }}",
                    "order_id": "{{ $order->id ?? '' }}",
                    "handler": function (response) {
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                        document.getElementById('razorpay_signature').value = response.razorpay_signature;
                        document.getElementById('payment-form').submit();
                    },
                    "modal": {
                        "ondismiss": function () {
                            document.getElementById('payment-loader').classList.add('hidden');
                            document.getElementById('payment-card').classList.remove('hidden');
                        }
                    },
                    "prefill": {
                        "name": "{{ $vendor->owner_name }}",
                        "email": "{{ auth()->user()->email }}",
                        "contact": "{{ $vendor->contact_number }}"
                    },
                    "theme": { "color": "#2563EB" }
                };

                var rzp = new Razorpay(options);

                setTimeout(function () { rzp.open(); }, 500);

                if (document.getElementById('rzp-button')) {
                    document.getElementById('rzp-button').onclick = function (e) {
                        rzp.open();
                        e.preventDefault();
                    };
                }
                @else
                    document.getElementById('payment-loader') && document.getElementById('payment-loader').classList.add('hidden');
                    document.getElementById('payment-card').classList.remove('hidden');
                @endif
            });
        </script>
    </div>
</x-vendor-layout>
