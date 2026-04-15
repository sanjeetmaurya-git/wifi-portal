<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Data Limit Reached – SpeedWave WiFi</title>
    <meta name="description" content="Your daily data allowance has been used. Buy a Data Pack or wait for midnight reset.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0a0a1a;
            --card: #111130;
            --red: #ef4444;
            --red-glow: rgba(239,68,68,0.18);
            --amber: #f59e0b;
            --green: #22c55e;
            --indigo: #6366f1;
            --text: #f1f5f9;
            --muted: #94a3b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        body {
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text);
            overflow-x: hidden;
            padding: 20px;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(239,68,68,0.12), transparent),
                        radial-gradient(ellipse 60% 60% at 80% 80%, rgba(99,102,241,0.08), transparent);
            pointer-events: none;
        }

        .card {
            background: var(--card);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 28px;
            padding: 3rem 2.5rem;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 0 60px var(--red-glow), 0 25px 50px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.6s ease-out;
        }
        .card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--red), var(--amber));
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .icon-wrap {
            width: 90px; height: 90px;
            margin: 0 auto 1.5rem;
            background: var(--red-glow);
            border: 2px solid rgba(239,68,68,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem;
            animation: pulse 2.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.25); }
            50%       { box-shadow: 0 0 0 16px rgba(239,68,68,0); }
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #f87171, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            color: var(--muted);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* Plan info pill */
        .plan-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 100px;
            padding: 0.4rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #a5b4fc;
            margin-bottom: 2rem;
        }

        /* Countdown */
        .countdown-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }
        .countdown-wrap {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }
        .time-block {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 0.75rem 1rem;
            min-width: 70px;
        }
        .time-num {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            color: var(--amber);
        }
        .time-unit {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            color: var(--muted);
            font-size: 0.8rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.08);
        }

        /* Buttons */
        .btn {
            display: flex; align-items: center; justify-content: center;
            gap: 0.6rem;
            width: 100%;
            padding: 1rem 1.5rem;
            border-radius: 14px;
            border: none;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            margin-bottom: 0.75rem;
        }
        .btn-boost {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 10px 25px rgba(34,197,94,0.3);
        }
        .btn-boost:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(34,197,94,0.45);
        }
        .btn-plans {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            box-shadow: 0 10px 25px rgba(99,102,241,0.3);
        }
        .btn-plans:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(99,102,241,0.45);
        }
        .btn-usage {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.12);
            color: var(--muted);
            font-weight: 600;
        }
        .btn-usage:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text);
        }

        .note {
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.7;
        }

        @media (max-width: 480px) {
            .card { padding: 2rem 1.5rem; }
            h1 { font-size: 1.5rem; }
            .time-num { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">📵</div>

        <h1>Daily Limit Reached</h1>
        <p class="subtitle">
            You've used your full daily data allowance.<br>
            Your data will <strong style="color:#fbbf24;">auto-reset at midnight</strong>, or you can instantly boost it.
        </p>

        @if($plan ?? null)
        <div class="plan-pill">
            <i class="fas fa-bolt"></i>
            {{ $plan->name ?? 'Daily Plan' }}
            @if($plan->daily_data_mb)
                — {{ $plan->daily_data_mb }} MB/Day
            @endif
            @if($expiresIn ?? null)
                · {{ $expiresIn }} left
            @endif
        </div>
        @endif

        <div class="countdown-label">⏰ Data resets in</div>
        <div class="countdown-wrap">
            <div class="time-block">
                <div class="time-num" id="cd-hours">00</div>
                <div class="time-unit">Hours</div>
            </div>
            <div class="time-block">
                <div class="time-num" id="cd-mins">00</div>
                <div class="time-unit">Mins</div>
            </div>
            <div class="time-block">
                <div class="time-num" id="cd-secs">00</div>
                <div class="time-unit">Secs</div>
            </div>
        </div>

        {{-- Only show Data Pack button if user has an active daily session --}}
        @if($hasActiveDailyPlan ?? false)
        <a href="/plans?filter=datapack" class="btn btn-boost">
            <i class="fas fa-rocket"></i> Buy Data Pack — Instant Boost
        </a>
        @endif

        <a href="/plans" class="btn btn-plans">
            <i class="fas fa-wifi"></i> View All Plans
        </a>

        <a href="/usage" class="btn btn-usage">
            <i class="fas fa-chart-bar"></i> View Usage Dashboard
        </a>

        <p class="note">
            📋 Your current plan remains active. Data resets every midnight automatically.<br>
            Need help? Contact your WiFi provider.
        </p>
    </div>

    <script>
        // Countdown to next midnight
        function updateCountdown() {
            const now = new Date();
            const midnight = new Date(now);
            midnight.setHours(24, 0, 0, 0);
            const diff = Math.max(0, midnight - now);

            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);

            document.getElementById('cd-hours').textContent = String(h).padStart(2, '0');
            document.getElementById('cd-mins').textContent  = String(m).padStart(2, '0');
            document.getElementById('cd-secs').textContent  = String(s).padStart(2, '0');

            if (diff === 0) {
                // Midnight! Reload so they get internet
                window.location.reload();
            }
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
