<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connected - WiFi Portal Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #27ae60;
            --secondary: #2c3e50;
            --bg: #f8fafc;
            --glass: rgba(255, 255, 255, 0.95);
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--secondary);
        }
        .dashboard {
            background: var(--glass);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 440px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }
        .status-badge {
            background: rgba(39, 174, 96, 0.1);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 10px 0; font-size: 28px; font-weight: 700; color: #1e293b; }
        p.subtitle { color: #64748b; margin-bottom: 30px; font-size: 16px; }
        
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
        }
        .stat-card .label { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-card .value { font-size: 20px; font-weight: 700; color: #1e293b; }

        .progress-container {
            background: #e2e8f0;
            height: 12px;
            border-radius: 10px;
            margin: 20px 0 10px 0;
            overflow: hidden;
        }
        .progress-bar {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .timer {
            font-family: monospace;
            font-size: 1.2rem;
            color: var(--primary);
            font-weight: bold;
            margin-top: 10px;
        }

        .btn-disconnect {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 16px;
            width: 100%;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-disconnect:hover {
            background: #ffe4e4;
            color: #ef4444;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="status-badge">⚡ CONNECTED</div>
    <h1>Hello, {{ $user->full_name ?? 'WiFi User' }}</h1>
    <p class="subtitle">You are successfully connected to high-speed WiFi.</p>

    @if(request('queued'))
    <div style="background:rgba(52,152,219,0.15);border:1px solid rgba(52,152,219,0.4);border-radius:12px;padding:14px;margin-bottom:20px;color:#3498db;font-size:14px;text-align:left;">
        📦 <strong>Plan Queued!</strong> Your new plan will activate automatically when the current one expires.
    </div>
    @endif

    <div class="stat-grid">
        <div class="stat-card">
            <div class="label">Plan</div>
            <div class="value">
                @if($session && $session->wifi_plan_id)
                    {{ \App\Models\WifiPlan::find($session->wifi_plan_id)->name ?? 'Active' }}
                @else
                    No Plan
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="label">Speed</div>
            <div class="value">
                @php
                    $plan = $session ? \App\Models\WifiPlan::find($session->wifi_plan_id) : null;
                @endphp
                @if($plan && $plan->download_limit)
                    {{ $plan->upload_limit }}/{{ $plan->download_limit }}
                @else
                    Best Effort
                @endif
            </div>
        </div>
    </div>

    @if($session && $session->expires_at)
        <div class="stat-card" style="width: 100%; box-sizing: border-box; margin-bottom: 20px;">
            <div class="label">Session Expiry</div>
            <div class="timer" id="countdown">Calculating...</div>
        </div>
    @endif

    <div class="label" style="text-align: left; font-size: 11px;">Data Usage Indicator</div>
    <div class="progress-container">
        <div class="progress-bar" style="width: 15%;"></div> <!-- Mock progress -->
    </div>
    <div style="font-size: 12px; color: #94a3b8; text-align: right;">Running smoothly...</div>

    @if(!$session)
        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:12px;padding:16px;margin-bottom:20px;color:#856404;font-size:14px;">
            ⚠️ No active plan found. Please buy a plan to access the internet.
        </div>
        <a href="/plans" style="display:block;background:#3498db;color:white;text-align:center;padding:16px;border-radius:12px;font-weight:600;text-decoration:none;margin-bottom:16px;">📦 Buy a Plan</a>
    @else
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-disconnect">Disconnect WiFi Session</button>
        </form>
    @endif
</div>

<script>
    @if($session && $session->expires_at)
    const expiryTime = new Date("{{ $session->expires_at }}").getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const distance = expiryTime - now;

        if (distance < 0) {
            document.getElementById("countdown").innerHTML = "EXPIRED";
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown").innerHTML = 
            (hours < 10 ? "0" : "") + hours + ":" + 
            (minutes < 10 ? "0" : "") + minutes + ":" + 
            (seconds < 10 ? "0" : "") + seconds;
    }

    setInterval(updateTimer, 1000);
    updateTimer();
    @endif
</script>

</body>
</html>
