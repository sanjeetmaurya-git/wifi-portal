<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SpeedWave WiFi</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --accent: #f43f5e;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --text: #f8fafc;
            --text-dim: #94a3b8;
            --green: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image:
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(244, 63, 94, 0.1) 0%, transparent 50%);
        }

        /* Responsive Container */
        main {
            flex: 1;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        header {
            padding: 2.5rem 1rem 1rem;
            text-align: center;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .user-welcome {
            font-size: 0.95rem;
            color: var(--text-dim);
            font-weight: 400;
        }

        /* Status Badge */
        .status-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status-inactive {
            background: rgba(244, 63, 94, 0.1);
            color: #fb7185;
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        /* Hero Card */
        .usage-hero {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .usage-hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }

        .usage-circle {
            margin-bottom: 1.5rem;
        }

        .usage-value {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1;
            display: block;
        }

        .usage-unit {
            font-size: 0.9rem;
            color: var(--text-dim);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .plan-info {
            margin-top: 1rem;
        }

        .plan-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .plan-expiry {
            font-size: 0.85rem;
            color: var(--text-dim);
        }

        /* Progress Bar */
        .progress-box {
            margin-top: 1.5rem;
            text-align: left;
        }

        .progress-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dim);
        }

        .progress-bar-bg {
            height: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 10px;
            transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-dim);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .stat-value span {
            font-size: 0.85rem;
            color: var(--text-dim);
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 1.1rem;
            border-radius: 18px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-upgrade {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            margin-bottom: 1rem;
        }

        .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: var(--text-dim);
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        footer {
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: var(--text-dim);
            font-size: 0.75rem;
            line-height: 1.5;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate {
            animation: slideUp 0.6s ease-out forwards;
        }

        /* Tablet/Desktop Tweaks */
        @media (min-width: 640px) {
            main {
                max-width: 600px;
                padding: 2rem;
            }

            .logo {
                font-size: 2.2rem;
            }
        }

        /* Very Small Screens */
        @media (max-width: 360px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .usage-value {
                font-size: 2.6rem;
            }
        }
    </style>
</head>

<body>

    @include('partials.header')

    <header>
        <div class="logo">PM-WANI.Smartlead</div>
        <p class="user-welcome">Welcome back, {{ $user->full_name ?? $user->mobile }}</p>
    </header>

    <main>
        <div class="status-container">
            @if($activeSession)
                <div class="status-badge status-active">
                    <i class="fas fa-circle"></i> Connected
                </div>
            @else
                <div class="status-badge status-inactive">
                    <i class="fas fa-exclamation-triangle"></i> No Active Plan
                </div>
            @endif
        </div>

            <div class="usage-hero animate">
                <div class="usage-circle">
                    @php
                        $liveMb = $activeSession ? ($activeSession->used_mb ?? 0) : ($usage['total']['mb'] ?? 0);
                        $liveGb = round($liveMb / 1024, 2);
                    @endphp
                    <span class="usage-value">{{ $liveGb }}</span>
                    <span class="usage-unit">GB CONSUMED</span>
                </div>

                @if($activeSession)
                    <div class="plan-info">
                        <div class="plan-name">{{ $activeSession->plan->name ?? 'Internet Plan' }}</div>
                        <div class="plan-expiry">
                            Valid until {{ \Carbon\Carbon::parse($activeSession->expires_at)->format('d M, h:i A') }}
                        </div>
                    </div>

                    @php
                        $plan = $activeSession->plan;
                        $limit = $plan->limit_bytes ?? 0;
                        if ($plan->isDailyPlan()) $limit = $plan->daily_data_mb;
                        
                        $percent = $limit > 0 ? min(100, ($liveMb / $limit) * 100) : 0;
                    @endphp

                    <div class="progress-box">
                        <div class="progress-meta">
                            <span>{{ $liveMb }} MB USED</span>
                            <span>LIMIT: {{ $limit }} MB</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width:{{ $percent }}%;"></div>
                        </div>
                    </div>
                @else
                    <p style="color: var(--text-dim); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        You don't have an active data plan. Subscribe to stay connected.
                    </p>
                    <a href="{{ url('/plans') }}" class="btn btn-upgrade">Get a Plan</a>
                @endif
            </div>

            <div class="stats-grid">
                <div class="stat-card animate" style="animation-delay: 0.1s">
                    <div class="stat-label">
                        <i class="fas fa-calendar-day"></i> Today
                    </div>
                    <div class="stat-value">{{ $liveMb }} <span>MB</span></div>
                </div>
            <div class="stat-card animate" style="animation-delay: 0.2s">
                <div class="stat-label">
                    <i class="fas fa-calendar-alt"></i> Month
                </div>
                <div class="stat-value">{{ $usage['monthly']['gb'] }} <span>GB</span></div>
            </div>
        </div>

        <div class="actions animate" style="animation-delay: 0.3s">
            <a href="{{ url('/plans') }}" class="btn btn-upgrade">
                <i class="fas fa-plus"></i> Renew or Upgrade
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-logout">
                    <i class="fas fa-power-off"></i> Disconnect Device
                </button>
            </form>
        </div>
    </main>

    <footer>
        &copy; 2026 SPEEDWAVE WIFI PORTAL<br>
        All connection logs are stored securely as per regulatory norms.
    </footer>

</body>

</html>