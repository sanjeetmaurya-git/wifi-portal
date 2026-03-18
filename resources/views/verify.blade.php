<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #1E1E2E;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            background: #313244;
            padding: 40px 36px;
            border-radius: 16px;
            width: 340px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        .wifi-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 12px;
        }

        h2 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 6px;
            color: #CDD6F4;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #6C7086;
            margin-bottom: 24px;
        }

        .mobile-display {
            text-align: center;
            font-size: 14px;
            color: #89DCEB;
            margin-bottom: 20px;
            font-weight: 500;
        }

        label {
            display: block;
            font-size: 13px;
            color: #A6ADC8;
            margin-bottom: 6px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #45475A;
            background: #1E1E2E;
            color: white;
            font-size: 15px;
            outline: none;
            letter-spacing: 6px;
            text-align: center;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus {
            border-color: #A6E3A1;
        }

        button {
            background: linear-gradient(135deg, #A6E3A1, #89DCEB);
            color: #1E1E2E;
            border: none;
            padding: 13px;
            width: 100%;
            margin-top: 18px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        button:hover { opacity: 0.88; }

        .error-box {
            background:#F38BA8;
            color:#1E1E2E;
            padding:10px;
            border-radius:8px;
            margin-bottom:14px;
            font-size:13px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #585B70;
            text-decoration: none;
        }

        .back-link:hover { color: #89DCEB; }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #585B70;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="wifi-icon">🔐</div>
    <h2>Verify OTP</h2>
    <p class="subtitle">OTP sent to your mobile number</p>
    <p class="mobile-display">📱 +91 {{ $mobile }}</p>
    
    {{-- Development OTP display --}}
    @if(isset($otp))
        <div style="background: rgba(166, 227, 161, 0.1); border: 1px dashed #A6E3A1; color: #A6E3A1; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
            Development OTP: <strong>{{ $otp }}</strong>
        </div>
    @endif

    {{-- Show validation / session errors --}}
    @if($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
    @endif

    @if(session('error'))
        <div class="error-box">{{ session('error') }}</div>
    @endif

    <form action="{{ url('/verify-otp') }}" method="POST">
        @csrf

        {{-- Carry all router parameters forward --}}
        <input type="hidden" name="mobile"     value="{{ old('mobile', $mobile) }}">
        <input type="hidden" name="mac"        value="{{ old('mac', $mac) }}">
        <input type="hidden" name="ip"         value="{{ old('ip', $ip) }}">
        <input type="hidden" name="link_login" value="{{ old('link_login', $link_login) }}">

        <label for="otp">Enter 6-digit OTP</label>
        {{-- type="text" so leading zeroes are not stripped --}}
        <input type="text"
               id="otp"
               name="otp"
               placeholder="_ _ _ _ _ _"
               maxlength="6"
               autocomplete="off"
               inputmode="numeric">

        <button type="submit">Verify & Connect →</button>
    </form>

    <a class="back-link" href="{{ url('/login') }}">← Change mobile number</a>

    <p class="footer">Free WiFi powered by HotSpot Portal</p>
</div>

</body>
</html>
