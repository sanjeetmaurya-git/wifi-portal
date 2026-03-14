<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Status</title>
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

        .status-header {
            font-size: 18px;
            color: #89DCEB;
            margin-bottom: 24px;
            font-weight: 600;
        }

        .timer {
            font-size: 48px;
            color: #A6E3A1;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .label {
            font-size: 13px;
            color: #6C7086;
            margin-bottom: 30px;
        }

        .info-grid {
            text-align: left;
            background: #1E1E2E;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 28px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .info-row:last-child { margin-bottom: 0; }

        .info-key { color: #6C7086; }
        .info-value { color: #CDD6F4; font-weight: 500; }

        .btn-portal {
            display: block;
            background: linear-gradient(135deg, #89DCEB, #74C7EC);
            color: #1E1E2E;
            border: none;
            padding: 13px;
            width: 100%;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 12px;
        }

        .btn-disconnect {
            background: transparent;
            color: #F38BA8;
            border: 1px solid #F38BA8;
            padding: 11px;
            width: 100%;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-disconnect:hover {
            background: #F38BA8;
            color: #1E1E2E;
        }

        .footer {
            font-size: 11px;
            color: #45475A;
            margin-top: 24px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="status-header">📶 Connection Active</div>
    
    <div class="timer" id="timer">--:--</div>
    <div class="label">Time Remaining</div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-key">User</span>
            <span class="info-value">+91 {{ $mobile }}</span>
        </div>
        <div class="info-row">
            <span class="info-key">MAC</span>
            <span class="info-value">{{ substr($session->mac_address, 0, 8) }}...</span>
        </div>
        <div class="info-row">
            <span class="info-key">Started</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($session->login_at)->format('H:i') }}</span>
        </div>
    </div>

    <a href="https://www.google.com" class="btn-portal">Open Browser</a>
    
    <form action="{{ url('/hotspot/disconnect') }}" method="POST">
        @csrf
        <input type="hidden" name="mac" value="{{ $session->mac_address }}">
        <button type="submit" class="btn-disconnect">Disconnect WiFi</button>
    </form>

    <p class="footer">Provided by HotSpot Portal</p>
</div>

<script>
    const expiryTime = new Date("{{ $session->expires_at }}").getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = expiryTime - now;

        if (diff <= 0) {
            document.getElementById('timer').innerHTML = "00:00";
            window.location.reload();
            return;
        }

        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('timer').innerHTML = 
            (mins < 10 ? "0" : "") + mins + ":" + (secs < 10 ? "0" : "") + secs;
    }

    setInterval(updateTimer, 1000);
    updateTimer();
</script>

</body>
</html>
