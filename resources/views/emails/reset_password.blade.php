@php
    $brand = \App\Models\SiteSetting::get('company_name') ?? config('app.name');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset your password</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .title { color: #111827; font-size: 24px; font-weight: 800; margin: 0 0 20px; }
        .content { color: #374151; font-size: 15px; line-height: 1.7; }
        .btn-container { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; text-decoration: none; padding: 15px 34px; border-radius: 8px; font-weight: 700; font-size: 16px; }
        .fallback { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; font-size: 12px; color: #6b7280; word-break: break-all; }
        .note { margin-top: 26px; font-size: 13px; color: #6b7280; line-height: 1.7; }
        .footer { text-align: center; margin-top: 36px; padding-top: 20px; border-top: 1px solid #f3f4f6; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Reset your password</h1>

        <div class="content">
            <p>Hi{{ $user->name ? ' ' . $user->name : '' }},</p>
            <p>We received a request to reset the password for your {{ $brand }} account ({{ $user->email }}). Click the button below to choose a new one.</p>
        </div>

        <div class="btn-container">
            <a class="btn" href="{{ $resetUrl }}">Reset Password</a>
        </div>

        <p style="font-size:13px;color:#6b7280;margin-bottom:8px;">If the button does not work, copy this link into your browser:</p>
        <div class="fallback">{{ $resetUrl }}</div>

        <p class="note">
            This link expires in {{ $expiresIn }} minutes and can be used once.<br>
            <strong>Didn't ask for this?</strong> You can safely ignore this email — your password stays as it is, and nobody can change it without this link.
        </p>

        <div class="footer">
            &mdash; {{ $brand }}
        </div>
    </div>
</body>
</html>
