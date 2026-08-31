<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Online payment received for a booking</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .title { color: #111827; font-size: 22px; font-weight: 800; margin: 0 0 6px; }
        .sub { color: #6b7280; font-size: 13px; margin: 0 0 24px; }
        .row { border-bottom: 1px solid #f3f4f6; padding: 12px 0; }
        .label { font-weight: 600; color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 4px; }
        .value { color: #111827; font-weight: 600; font-size: 15px; word-break: break-word; }
        .amount { color: #047857; font-size: 20px; font-weight: 800; }
        .notice { background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 18px; margin-top: 20px; color: #92400e; font-size: 14px; line-height: 1.6; }
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 13px 26px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .footer { text-align: center; margin-top: 32px; color: #9ca3af; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">A customer paid you online for a booking</h1>
        <p class="sub">Booking #{{ $booking->id }} &middot; {{ optional($booking->payment_submitted_at)->format('d M Y, g:i A') }}</p>

        <div class="row">
            <span class="label">Customer</span>
            <span class="value">{{ $booking->customer_name }}@if($booking->customer_phone && $booking->customer_phone !== 'Anonymous') &middot; {{ $booking->customer_phone }}@endif</span>
        </div>
        <div class="row">
            <span class="label">Appointment</span>
            <span class="value">
                {{ $booking->appointment_date_label }}
                @if($booking->token_number)
                    &middot; Token #{{ $booking->token_number }}
                @elseif($booking->appointment_at)
                    &middot; {{ $booking->appointment_at->format('h:i A') }}
                @endif
                @if($booking->employee)
                    &middot; with {{ $booking->employee->name }}
                @endif
            </span>
        </div>
        <div class="row">
            <span class="label">Amount they were asked to send</span>
            <span class="value amount">&#8377;{{ number_format((float) $booking->requested_amount, 2) }}</span>
        </div>

        {{-- Said plainly, because it is the single thing that makes this flow
             safe: we never see the money, so the shop's own UPI app is the only
             record that exists. The booking stands either way. --}}
        <div class="notice">
            This payment went directly into your account &mdash; {{ config('app.name') }} never received it and
            cannot confirm it for you. Check your UPI app for a credit of
            <strong>&#8377;{{ number_format((float) $booking->requested_amount, 2) }}</strong>, and ask the customer
            for their payment receipt when they arrive. The appointment is confirmed either way.
        </div>

        <div class="btn-container">
            <a class="btn" href="{{ $paymentsUrl }}">See this booking's payment</a>
        </div>

        <div class="footer">
            No action is needed on {{ config('app.name') }} to keep this booking &mdash; it is already confirmed.<br>
            &mdash; {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
