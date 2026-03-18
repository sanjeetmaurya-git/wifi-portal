<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internet Access Granted</title>
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
            padding: 44px 36px;
            border-radius: 16px;
            width: 340px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        .success-icon {
            font-size: 60px;
            margin-bottom: 16px;
            animation: pop 0.4s ease;
        }

        @keyframes pop {
            0%   { transform: scale(0.5); opacity: 0; }
            80%  { transform: scale(1.15); }
            100% { transform: scale(1); opacity: 1; }
        }

        h2 {
            font-size: 22px;
            color: #A6E3A1;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 13px;
            color: #6C7086;
            margin-bottom: 28px;
        }

        .info-box {
            background: #1E1E2E;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
            text-align: left;
            font-size: 13px;
            color: #CDD6F4;
            line-height: 2;
        }

        .info-box span {
            color: #89DCEB;
            font-weight: 600;
        }

        .btn {
            display: block;
            background: linear-gradient(135deg, #A6E3A1, #89DCEB);
            color: #1E1E2E;
            border: none;
            padding: 13px;
            width: 100%;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn:hover { opacity: 0.88; }


        .router-badge {
            display: inline-block;
            margin-top: 16px;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            color: #1E1E2E;
            font-weight: 600;
        }

        .router-badge--synced {
            background: #A6E3A1;
        }

        .router-badge--unsynced {
            background: #F9E2AF;
        }

        .footer {
            font-size: 11px;
            color: #45475A;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="success-icon">✅</div>
    <!-- <h2>Internet Access Granted!</h2> -->
     <h2>Connected Successfully</h2>
    <p class="subtitle">You are now connected to WiFi</p>

    <div class="info-box">
        📱 Mobile: <span>{{ $mobile }}</span><br>
        🕐 Session: <span>30 minutes</span><br>
        📶 Status: <span>Connected</span>
    </div>

    {{-- If router sent link_login, provide a button to complete captive portal handshake --}}
    @if($link_login)
        <a class="btn" href="{{ $link_login }}">🌐 Start Browsing</a>
        <p style="font-size:11px;color:#6C7086;margin-top:8px;">Click above to complete router activation</p>
        <script>
            setTimeout(function() {
                window.location.href = "{{ $link_login }}";
            }, 2000);
        </script>
        <!-- <script>
            const loginUrl = @json($link_login);
            setTimeout(function() {
                window.location.href = loginUrl;
            }, 2000);
        </script> -->
    @else
        <!-- <a class="btn" href="https://www.google.com">🌐 Start Browsing</a> -->
         <p>Dev Mode: No router link found</p>
    @endif

    <span class="router-badge">
        Router: {{ $routerSynced ? '✓ Synced' : '⏳ Pending' }}
    </span>

    <p class="footer">Free WiFi powered by HotSpot Portal</p>
</div>

</body>
</html>
