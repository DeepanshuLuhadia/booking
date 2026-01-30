<x-app-layout>
    <div class="max-w-xl mx-auto py-20 text-center">
        <h1 class="text-4xl font-black mb-4">Complete Your Registration</h1>
        <p class="text-gray-500 mb-12 text-lg">You've selected the <span class="text-primary-600 font-bold">{{ $plan->name }}</span> plan. Please complete the payment to activate your shop.</p>

        <div class="glass-card p-10 space-y-8 bg-white/50 backdrop-blur-xl border-slate-200">
            @if(isset($error))
                <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-600 rounded-xl text-sm font-medium">
                    {{ $error }}
                </div>
            @else
                <div class="flex justify-between items-center pb-6 border-b border-slate-100">
                    <span class="text-slate-500 font-medium tracking-wide uppercase text-xs">Total Amount</span>
                    <span class="text-3xl font-black text-slate-900">₹{{ number_format($plan->price, 2) }}</span>
                </div>

                <button id="rzp-button" class="btn-primary w-full py-4 text-xl shadow-xl shadow-blue-500/20">
                    Pay Securely via Razorpay
                </button>
                <p class="text-xs text-slate-400">Secure payment processed via Razorpay SSL Encryption.</p>
            @endif
        </div>

        <form action="{{ route('payment.callback') }}" method="POST" id="payment-form" class="hidden">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>
    </div>

    @if(!isset($error))
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "{{ $keyId ?? '' }}",
            "amount": "{{ $order->amount ?? 0 }}",
            "currency": "INR",
            "name": "BOOKAI",
            "description": "Subscription for {{ $vendor->business_name }}",
            "order_id": "{{ $order->id }}",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('payment-form').submit();
            },
            "prefill": {
                "name": "{{ $vendor->owner_name }}",
                "email": "{{ auth()->user()->email }}",
                "contact": "{{ $vendor->contact_number }}"
            },
            "theme": {
                "color": "#3b82f6"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
    </script>
    @endif
</x-app-layout>
