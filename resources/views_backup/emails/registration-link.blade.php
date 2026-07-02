<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join {{ $gymName }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1e293b; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px 40px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: #ea580c; padding: 36px 40px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .header p  { color: rgba(255,255,255,.80); font-size: 13px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .body p { color: #475569; font-size: 15px; line-height: 1.65; }
        .body p + p { margin-top: 14px; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #ea580c; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 10px; letter-spacing: 0.2px; }
        .url-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-top: 20px; word-break: break-all; font-size: 13px; color: #64748b; }
        .footer { padding: 20px 40px; border-top: 1px solid #f1f5f9; text-align: center; }
        .footer p { color: #94a3b8; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $gymName }}</h1>
                <p>You've been invited to register as a member</p>
            </div>
            <div class="body">
                <p>Hi there,</p>
                <p>You have been invited to join <strong>{{ $gymName }}</strong>. Click the button below to complete your registration — it only takes a minute.</p>
                <div class="btn-wrap">
                    <a href="{{ $registrationUrl }}" class="btn">Complete Registration →</a>
                </div>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <div class="url-box">{{ $registrationUrl }}</div>
                <p style="margin-top:20px;font-size:13px;color:#94a3b8;">This link can be used to register at any time. Once you submit your details, the gym staff will review and confirm your membership.</p>
            </div>
            <div class="footer">
                <p>This invitation was sent by {{ $gymName }}.<br>If you did not expect this email, you can safely ignore it.</p>
            </div>
        </div>
    </div>
</body>
</html>
