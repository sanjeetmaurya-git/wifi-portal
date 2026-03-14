<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Login</title>
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
            transition: border-color 0.2s;
        }

        input[type="text"]:focus {
            border-color: #89DCEB;
        }

        button {
            background: linear-gradient(135deg, #89DCEB, #74C7EC);
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
    <div class="wifi-icon">📶</div>
    <h2>WiFi Login</h2>
    <p class="subtitle">Enter your mobile number to get OTP</p>

    {{-- Show validation errors --}}
    @if($errors->any())
        <div style="background:#F38BA8;color:#1E1E2E;padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ url('/send-otp') }}" method="POST">
        @csrf

        {{-- Router parameters: pass through so they survive the OTP step --}}
        <input type="hidden" name="mac"        value="{{ old('mac', request('mac')) }}">
        <input type="hidden" name="ip"         value="{{ old('ip', request('ip')) }}">
        <input type="hidden" name="link_login" value="{{ old('link_login', request('link-login')) }}">
        <input type="hidden" name="username"   value="{{ old('username', request('username')) }}">

        <label for="mobile">Mobile Number</label>
        {{-- type="text" to preserve leading 0 --}}
        <input type="text"
               id="mobile"
               name="mobile"
               placeholder="e.g. 9876543210"
               maxlength="10"
               value="{{ old('mobile') }}"
               autocomplete="off"
               inputmode="numeric">

        <button type="submit">Send OTP →</button>
    </form>

    <p class="footer">Free WiFi powered by HotSpot Portal</p>
</div>

</body>
</html>