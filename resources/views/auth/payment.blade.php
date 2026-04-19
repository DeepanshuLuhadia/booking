<x-app-layout page-title="Payment Gateway | Deployment Protocol">
    <div class="relative min-h-[90vh] flex items-center justify-center py-20 bg-theme-main">
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-10"></div>

        <div class="relative z-10 w-full max-w-2xl px-6">
            <!-- Loader State (Visible by default) -->
            <div id="payment-loader" class="text-center animate-pulse">
                <div class="inline-flex items-center gap-4 px-6 py-3 bg-white/5 border border-white/10 rounded-full text-white/60 text-xs font-black uppercase tracking-[0.3em] mb-12">
                    <svg class="w-5 h-5 animate-spin text-theme-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Initializing Razorpay Secure Portal...
                </div>
                <h2 class="text-4xl font-black text-white italic tracking-tighter mb-4">Establishing Secure Payload.</h2>
                <p class="text-white/40 font-medium italic">Please do not refresh as we authorize your institutional registry.</p>
            </div>

            <!-- Manual Card (Hidden by default, shown if popup blocked or error) -->
                    <div id="payment-card" class="{{ isset($error) ? '' : 'hidden' }} animate-reveal">
                        <div class="text-center mb-12">
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/5 border border-white/10 rounded-full text-white/40 text-[9px] font-black uppercase tracking-widest mb-8">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Final Operational Authorization
                            </div>
                            <h1 class="text-5xl md:text-[4.5rem] font-black text-white mb-6 tracking-tighter leading-[0.9] italic">
                                Authorize <span class="text-theme-primary">Sync.</span>
                            </h1>
                        </div>

                        <div class="glass-card p-4 shadow-2xl overflow-hidden">
                            <div class="p-10 md:p-14 bg-white rounded-[3rem] space-y-10">
                                @if(isset($error))
                                    <div class="p-10 bg-rose-50 rounded-[2.5rem] text-rose-600 flex flex-col items-center gap-6">
                                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <div class="space-y-2 text-center">
                                            <p class="font-black italic uppercase tracking-widest text-sm leading-relaxed">{{ $error }}</p>
                                            <p class="text-[10px] font-bold opacity-50">THE GATEWAY FAILED TO AUTHORIZE. PLEASE USE SIMULATOR FOR TEST RUN.</p>
                                        </div>
                                    </div>
                                    
                                    @if($demoMode)
                                    <button onclick="simulateSuccess()" class="w-full h-20 bg-emerald-600 text-white rounded-[1.5rem] text-lg font-black italic uppercase tracking-widest hover:bg-emerald-700 transition-all flex items-center justify-center gap-4 group">
                                        SIMULATE PAYMENT SUCCESS (DEMO)
                                        <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    @endif

                                    <a href="{{ route('register.vendor') }}" class="flex items-center justify-center gap-2 text-slate-400 font-black italic text-[10px] uppercase tracking-widest hover:text-slate-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                        Return to Registry Identity
                                    </a>
                                @else
                                    <div class="bg-slate-900 rounded-[2.5rem] p-12 text-white relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-theme-primary opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                        <div class="flex justify-between items-end relative z-10">
                                            <div>
                                                <p class="text-[9px] font-black uppercase tracking-[0.3em] text-white/30 mb-2 italic">Settlement Payload</p>
                                                <h2 class="text-6xl font-black italic tracking-tighter">₹{{ number_format($plan->price) }}</h2>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-white/30 mb-2 italic">Active Tier</p>
                                                <span class="px-5 py-2 bg-white/5 rounded-xl text-[10px] font-black uppercase tracking-widest border border-white/10 italic">{{ $plan->name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <button id="rzp-button" class="btn-premium w-full h-24 !rounded-[2rem] !text-2xl">
                                            INITIALIZE RAZORPAY
                                            <svg class="w-8 h-8 transition-transform group-hover:translate-x-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </button>

                                        <button onclick="simulateSuccess()" class="w-full h-12 text-slate-400 hover:text-emerald-600 font-black italic text-[10px] uppercase tracking-[0.2em] transition-colors">
                                            OR BYPASS FOR DEMO TESTING
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('payment.callback') }}" method="POST" id="payment-form" class="hidden">
                @csrf
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

                document.addEventListener('DOMContentLoaded', function() {
                    @if(isset($order))
                    var options = {
                        "key": "{{ $keyId ?? '' }}",
                        "amount": "{{ $order->amount ?? 0 }}",
                        "currency": "INR",
                        "name": "{{ config('app.name') }}",
                        "description": "Institutional Subscription: {{ $vendor->business_name }}",
                        "order_id": "{{ $order->id ?? '' }}",
                        "handler": function (response){
                            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                            document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                            document.getElementById('razorpay_signature').value = response.razorpay_signature;
                            document.getElementById('payment-form').submit();
                        },
                        "modal": {
                            "ondismiss": function(){
                                document.getElementById('payment-loader').classList.add('hidden');
                                document.getElementById('payment-card').classList.remove('hidden');
                            }
                        },
                        "prefill": {
                            "name": "{{ $vendor->owner_name }}",
                            "email": "{{ auth()->user()->email }}",
                            "contact": "{{ $vendor->mobile }}"
                        },
                        "theme": {
                            "color": "#0f172a"
                        }
                    };
                    
                    var rzp1 = new Razorpay(options);
                    
                    // Auto open
                    setTimeout(function() {
                        rzp1.open();
                    }, 500);

                    if(document.getElementById('rzp-button')) {
                        document.getElementById('rzp-button').onclick = function(e){
                            rzp1.open();
                            e.preventDefault();
                        }
                    }
                    @else
                        document.getElementById('payment-loader').classList.add('hidden');
                        document.getElementById('payment-card').classList.remove('hidden');
                    @endif
                });
            </script>
</x-app-layout>
