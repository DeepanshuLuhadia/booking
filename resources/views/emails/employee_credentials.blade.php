<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Dashboard Login Credentials</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .title { color: #111827; font-size: 24px; font-weight: 800; margin: 0; }
        .content { color: #374151; font-size: 16px; line-height: 1.6; }
        .credentials-box { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .credential-item { margin-bottom: 10px; }
        .label { font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; }
        .value { color: #111827; font-weight: 700; font-family: monospace; font-size: 16px; }
        .btn-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .footer { text-align: center; margin-top: 40px; color: #9ca3af; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Welcome to the Team!</h1>
        </div>
        
        <div class="content">
            <p>Hi {{ $employeeName }},</p>
            <p><strong>{{ $vendorName }}</strong> has created or updated your employee account on our booking platform. You can now log in to your dashboard to manage your appointments, view your schedule, and pause your availability when on break.</p>
            
            <div class="credentials-box">
                <div class="credential-item">
                    <span class="label">Login Email</span>
                    <span class="value">{{ $emailAddress }}</span>
                </div>
                <div class="credential-item" style="margin-bottom: 0;">
                    <span class="label">Password</span>
                    <span class="value">{{ $password }}</span>
                </div>
            </div>

            <p style="font-size: 14px; color: #6b7280; text-align: center;">We recommend changing your password after your first login if required by your manager.</p>

            <div class="btn-container">
                <a href="{{ url('/login') }}" class="btn">Login to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Booking Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
