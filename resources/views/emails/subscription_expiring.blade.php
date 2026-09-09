<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your subscription is expiring soon</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .title { color: #111827; font-size: 22px; font-weight: 800; margin: 0 0 6px; }
        .sub { color: #6b7280; font-size: 13px; margin: 0 0 24px; }
        .notice { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 18px; margin-top: 4px; color: #991b1b; font-size: 14px; line-height: 1.6; }
        .notice.autopay { background-color: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 13px 26px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .footer { text-align: center; margin-top: 32px; color: #9ca3af; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">
            @if($daysBefore === 1)
                Your plan expires tomorrow
            @else
                Your plan expires in {{ $daysBefore }} days
            @endif
        </h1>
        <p class="sub">{{ $vendorName }} &middot; expires {{ $expiresAt->format('d M Y, g:i A') }}</p>

        @if($autopayLive)
            <div class="notice autopay">
                UPI Autopay is active on your account, so this will renew automatically and no action is needed.
                If you'd like to change your plan before then, you can do so from your dashboard.
            </div>
        @else
            <div class="notice">
                Renew before {{ $expiresAt->format('d M Y') }} to avoid any interruption &mdash; once your
                subscription expires, customers will no longer be able to book you online.
            </div>
        @endif

        <div class="btn-container">
            <a class="btn" href="{{ $plansUrl }}">{{ $autopayLive ? 'View plans' : 'Renew now' }}</a>
        </div>

        <div class="footer">
            &mdash; {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
