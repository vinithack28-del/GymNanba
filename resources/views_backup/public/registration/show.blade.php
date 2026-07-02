<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join {{ $tenant->gym_name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f1f5f9;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            min-height: 100vh;
            padding: 24px 16px 48px;
        }

        .page-wrap {
            max-width: 560px;
            margin: 0 auto;
        }

        /* Header */
        .gym-header {
            text-align: center;
            margin-bottom: 28px;
            padding-top: 12px;
        }
        .gym-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: #ea580c;
            color: #fff;
            font-size: 26px;
            font-weight: 700;
            border-radius: 18px;
            margin-bottom: 14px;
        }
        .gym-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #0f172a;
        }
        .gym-sub {
            margin-top: 4px;
            font-size: 14px;
            color: #64748b;
        }

        /* Card */
        .card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 2px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            padding: 24px 28px;
            color: #fff;
        }
        .card-header h1 {
            font-size: 18px;
            font-weight: 700;
        }
        .card-header p {
            margin-top: 4px;
            font-size: 13px;
            opacity: 0.85;
        }
        .card-body {
            padding: 28px;
        }

        /* Form */
        .field { margin-bottom: 18px; }
        .field:last-child { margin-bottom: 0; }
        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .field label .req { color: #ef4444; }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            color: #0f172a;
            font-size: 15px;
            padding: 11px 14px;
            outline: none;
            transition: border-color 150ms;
            -webkit-appearance: none;
            appearance: none;
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234,88,12,.10);
        }
        .field textarea { resize: vertical; min-height: 80px; }

        .field-row {
            display: grid;
            gap: 14px;
            grid-template-columns: 1fr 1fr;
        }

        /* Error messages */
        .field-error {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }
        .alert-error {
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px;
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 12px 16px;
        }

        /* Submit button */
        .btn-submit {
            display: block;
            width: 100%;
            background: #ea580c;
            border: none;
            border-radius: 12px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            padding: 14px;
            margin-top: 24px;
            transition: background 150ms, transform 80ms;
            letter-spacing: 0.2px;
        }
        .btn-submit:hover  { background: #c2410c; }
        .btn-submit:active { transform: scale(.98); }

        /* Divider */
        .section-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 22px 0;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 14px;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 480px) {
            .field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-wrap">

        <div class="gym-header">
            <div class="gym-avatar">{{ strtoupper(substr($tenant->gym_name, 0, 1)) }}</div>
            <div class="gym-name">{{ $tenant->gym_name }}</div>
            @if ($tenant->city)
                <div class="gym-sub">{{ $tenant->city }}@if($tenant->state), {{ $tenant->state }}@endif</div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h1>Member Registration</h1>
                <p>Fill in your details and we'll get you set up.</p>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit', $token) }}" novalidate>
                    @csrf

                    <p class="section-label">Basic Information</p>

                    <div class="field">
                        <label for="name">Full Name <span class="req">*</span></label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name') }}"
                            placeholder="Your full name"
                            required maxlength="100" autocomplete="name">
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label for="phone">Phone Number <span class="req">*</span></label>
                        <input type="tel" id="phone" name="phone"
                            value="{{ old('phone') }}"
                            placeholder="+91 98765 43210"
                            required maxlength="20" autocomplete="tel">
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            maxlength="255" autocomplete="email">
                        @error('email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="section-divider"></div>
                    <p class="section-label">Personal Details <span style="font-weight:400;letter-spacing:0;text-transform:none;color:#b0bec5;font-size:11px">(optional)</span></p>

                    <div class="field-row">
                        <div class="field">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="" {{ old('gender') === '' ? 'selected' : '' }}>Select…</option>
                                <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob"
                                value="{{ old('dob') }}"
                                max="{{ now()->subYears(5)->toDateString() }}">
                            @error('dob') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="field">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" maxlength="500"
                            placeholder="Your address (optional)">{{ old('address') }}</textarea>
                        @error('address') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit">Submit Registration →</button>
                </form>

            </div>
        </div>

        <div class="page-footer">
            After submission, gym staff will review your details and confirm your membership.
        </div>

    </div>
</body>
</html>
