<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Enquiry</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .title { color: #111827; font-size: 22px; font-weight: 800; margin: 0 0 6px; }
        .sub { color: #6b7280; font-size: 13px; margin: 0 0 24px; }
        .row { border-bottom: 1px solid #f3f4f6; padding: 12px 0; }
        .label { font-weight: 600; color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 4px; }
        .value { color: #111827; font-weight: 600; font-size: 15px; word-break: break-word; }
        .message-box { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px; margin-top: 20px; color: #374151; font-size: 15px; line-height: 1.6; white-space: pre-wrap; }
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 13px 26px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .footer { text-align: center; margin-top: 32px; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">New enquiry from the website</h1>
        <p class="sub">Received {{ $contactMessage->created_at->format('d M Y, g:i A') }}</p>

        <div class="row">
            <span class="label">Name</span>
            <span class="value">{{ $contactMessage->name }}</span>
        </div>
        <div class="row">
            <span class="label">Email</span>
            <span class="value"><a href="mailto:{{ $contactMessage->email }}" style="color:#2563eb;text-decoration:none;">{{ $contactMessage->email }}</a></span>
        </div>
        @if($contactMessage->phone)
            <div class="row">
                <span class="label">Phone</span>
                <span class="value">{{ $contactMessage->phone }}</span>
            </div>
        @endif
        <div class="row">
            <span class="label">Subject</span>
            <span class="value">{{ $contactMessage->subject }}</span>
        </div>

        <div class="message-box">{{ $contactMessage->message }}</div>

        <div class="btn-container">
            <a class="btn" href="{{ route('admin.contacts.show', $contactMessage) }}">Open in admin panel</a>
        </div>

        <div class="footer">
            You can reply to this email directly to reach {{ $contactMessage->name }}.<br>
            &mdash; {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
