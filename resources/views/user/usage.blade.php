<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Dashboard - Data Usage</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #f43f5e;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --glass: rgba(30, 41, 59, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .background-blob {
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(15, 23, 42, 0) 70%);
            border-radius: 50%;
            z-index: -1;
            filter: blur(50px);
        }

        .blob-1 {
            top: -100px;
            right: -100px;
        }

        .blob-2 {
            bottom: -100px;
            left: -100px;
        }

        header {
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(to right, #818cf8, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 1rem;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 600;
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.2);
            margin-bottom: 1rem;
        }

        .status-badge.inactive {
            background: rgba(244, 63, 94, 0.1);
            color: #fb7185;
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        main {
            flex: 1;
            padding: 0 1.5rem 3rem;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        .usage-hero {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .usage-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }

        .usage-circle {
            width: 180px;
            height: 180px;
            margin: 0 auto 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
        }

        .usage-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .usage-unit {
            font-size: 1rem;
            color: var(--text-dim);
            font-weight: 400;
        }

        .plan-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .plan-expiry {
            font-size: 0.9rem;
            color: var(--text-dim);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-dim);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .progress-container {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 100px;
            margin-top: 0.8rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 100px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            width: 100%;
            padding: 1.2rem;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.5);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            margin-top: 1rem;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        footer {
            padding: 2rem;
            text-align: center;
            color: var(--text-dim);
            font-size: 0.85rem;
        }

        .animate-pop {
            animation: pop 0.5s ease-out;
        }

        @keyframes pop {
            0% {
                transform: scale(0.9);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <style>
        * {
            color: #fff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
    </style>
    </head>

    <body>
        @include('partials.header')
        <div class="background-blob blob-1"></div>
        <div class="background-blob blob-2"></div>

        <header>
            <div class="logo">SPEEDWAVE WIFI</div>
            <p style="color: var(--text-dim)">Welcome back, {{ $user->full_name ?? $user->mobile }}</p>
        </header>

        <main>
            <div class="text-center" style="text-align: center;">
                @if($activeSession)
                    <div class="status-badge">
                        <i class="fas fa-circle-check" style="margin-right: 5px;"></i> INTERNET ACTIVE
                    </div>
                @else
                    <div class="status-badge inactive">
                        <i class="fas fa-circle-exclamation" style="margin-right: 5px;"></i> NO ACTIVE PLAN
                    </div>
                @endif
            </div>

            <div class="usage-hero animate-pop">
                <div class="usage-circle">
                    <div class="usage-value">{{ $usage['total']['gb'] ?? '0' }}</div>
                    <div class="usage-unit">GB USED</div>
                </div>

                @if($activeSession)
                    <div class="plan-name">{{ $activeSession->plan->name ?? 'Premium Plan' }}</div>
                    <div class="plan-expiry">Expires:
                        {{ \Carbon\Carbon::parse($activeSession->expires_at)->format('d M, h:i A') }}</div>

                    @php
                        $limit = $activeSession->plan->limit_bytes ?? 0;
                        $used = $usage['total']['mb'] ?? 0;
                        $percent = $limit > 0 ? min(100, ($used / $limit) * 100) : 0;
                    @endphp

                    <div class="progress-container">
                        <div class="progress-bar" style="width: {{ $percent }}%"></div>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-dim);">
                        <span>{{ $usage['total']['mb'] }} MB Used</span>
                        <span>Limit: {{ $limit }} MB</span>
                    </div>
                @else
                    <div class="plan-name">No active plan</div>
                    <p style="color: var(--text-dim); margin-bottom: 1rem;">Unlock high-speed internet by choosing a plan.
                    </p>
                    <a href="{{ url('/plans') }}" class="btn btn-primary">VIEW PLANS</a>
                @endif
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">
                        <i class="fas fa-calendar-day"></i> TODAY
                    </div>
                    <div class="stat-value">{{ $usage['daily']['mb'] }} <span style="font-size: 0.8rem;">MB</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">
                        <i class="fas fa-calendar-alt"></i> THIS MONTH
                    </div>
                    <div class="stat-value">{{ $usage['monthly']['gb'] }} <span style="font-size: 0.8rem;">GB</span>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="{{ url('/plans') }}" class="btn btn-primary">
                    <i class="fas fa-bolt"></i> UPGRADE / RENEW
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%;">
                        <i class="fas fa-sign-out-alt"></i> DISCONNECT DEVICE
                    </button>
                </form>
            </div>
        </main>

        <footer>
            &copy; 2026 SPEEDWAVE CAPTIVE PORTAL<br>
            All connection logs are stored for 14 months as per Govt. Guidelines.
        </footer>
    </body>

</html>