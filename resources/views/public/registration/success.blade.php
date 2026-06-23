<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Submitted — {{ $tenant->gym_name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f1f5f9;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 32px rgba(0,0,0,.10);
            max-width: 460px;
            width: 100%;
            padding: 48px 40px;
            text-align: center;
        }
        .check-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            background: rgba(16,185,129,.12);
            border-radius: 50%;
            margin-bottom: 24px;
        }
        .check-icon svg {
            width: 36px;
            height: 36px;
            color: #10b981;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin-bottom: 12px;
        }
        p {
            font-size: 15px;
            line-height: 1.65;
            color: #64748b;
        }
        .gym-name {
            display: inline-block;
            margin-top: 24px;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .steps {
            margin: 28px 0;
            text-align: left;
            background: #f8fafc;
            border-radius: 14px;
            padding: 18px 20px;
        }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            color: #475569;
            line-height: 1.5;
        }
        .step + .step { margin-top: 12px; }
        .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: #ea580c;
            color: #fff;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 1px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="check-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>

        <h1>Registration Submitted!</h1>
        <p>Your details have been received. The team at <strong>{{ $tenant->gym_name }}</strong> will review your registration and confirm your membership soon.</p>

        <div class="steps">
            <div class="step">
                <span class="step-num">1</span>
                <span>Your registration has been saved and is pending review.</span>
            </div>
            <div class="step">
                <span class="step-num">2</span>
                <span>A staff member will verify your details and assign a membership plan.</span>
            </div>
            <div class="step">
                <span class="step-num">3</span>
                <span>Once confirmed, you'll be officially enrolled as a member.</span>
            </div>
        </div>

        <p style="font-size:13px;color:#94a3b8">Feel free to visit {{ $tenant->gym_name }} for any queries or follow-up.</p>

        <span class="gym-name">{{ $tenant->gym_name }}</span>
    </div>
</body>
</html>
