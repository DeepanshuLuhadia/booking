<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $replySubject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .title { color: #111827; font-size: 22px; font-weight: 800; margin: 0 0 20px; }
        .content { color: #374151; font-size: 15px; line-height: 1.7; white-space: pre-wrap; }
        .quote { background-color: #f9fafb; border-left: 3px solid #d1d5db; border-radius: 6px; padding: 16px 18px; margin-top: 30px; color: #6b7280; font-size: 13px; line-height: 1.6; white-space: pre-wrap; }
        .quote-label { font-weight: 700; color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 8px; }
        .footer { text-align: center; margin-top: 36px; padding-top: 20px; border-top: 1px solid #f3f4f6; color: #9ca3af; font-size: 12px; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Hi {{ $contactMessage->name }},</h1>

        <div class="content">{{ $replyBody }}</div>

        <div class="quote">
            <span class="quote-label">Your original message &mdash; {{ $contactMessage->created_at->format('d M Y') }}</span>
            <strong>{{ $contactMessage->subject }}</strong><br><br>{{ $contactMessage->message }}
        </div>

        <div class="footer">
            {{ \App\Models\SiteSetting::get('company_name') }}<br>
            @if(\App\Models\SiteSetting::get('company_support_email'))
                {{ \App\Models\SiteSetting::get('company_support_email') }}
            @endif
            @if(\App\Models\SiteSetting::get('company_phone'))
                &middot; {{ \App\Models\SiteSetting::get('company_phone') }}
            @endif
        </div>
    </div>
</body>
</html>
