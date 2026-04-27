<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connected – SpeedWave WiFi</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #22c55e;
            --indigo: #6366f1;
            --red: #ef4444;
            --amber: #f59e0b;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        body {
            background: var(--bg);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            color: var(--text);
            padding: 20px;
        }
        body::before {
            content:'';
            position:fixed; inset:0; z-index:0;
            background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(34,197,94,0.1), transparent);
            pointer-events:none;
        }

        .card {
            background: var(--card);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 28px;
            padding: 2.5rem 2rem;
            max-width: 440px; width: 100%;
            position: relative; z-index: 1;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        /* Top accent bar */
        .card::before {
            content:'';
            position:absolute; top:0; left:0; right:0;
            height:4px; border-radius:28px 28px 0 0;
            background: linear-gradient(to right, var(--green), #06b6d4);
        }

        /* Status badge */
        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 16px; border-radius: 100px;
            font-size: 0.8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 1.2rem;
        }
        .badge-connected { background: rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.3); color: #4ade80; }
        .badge-warning   { background: rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); color: #fbbf24; }

        h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.3rem; }
        .subtitle { color: var(--muted); font-size: 0.95rem; margin-bottom: 1.8rem; }

        /* Queued notice */
        .queued-notice {
            background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.25);
            border-radius: 12px; padding: 12px 16px;
            font-size: 0.88rem; color: #a5b4fc; margin-bottom: 1.5rem; text-align:left;
        }

        /* Internet status test */
        .net-status {
            border-radius: 14px; padding: 14px 16px;
            font-size: 0.9rem; font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 10px;
            border: 1px solid;
        }
        .net-status.testing  { background:rgba(99,102,241,0.08); border-color:rgba(99,102,241,0.2); color:#a5b4fc; }
        .net-status.online   { background:rgba(34,197,94,0.08);  border-color:rgba(34,197,94,0.25); color:#4ade80; }
        .net-status.offline  { background:rgba(239,68,68,0.08);  border-color:rgba(239,68,68,0.25); color:#fca5a5; }

        /* Stat grid */
        .stats { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:1.5rem; }
        .stat { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:16px; padding:1rem; }
        .stat .lbl { font-size:0.72rem; text-transform:uppercase; letter-spacing:1px; color:var(--muted); margin-bottom:6px; }
        .stat .val { font-size:1.15rem; font-weight:700; }

        /* Countdown */
        .timer { font-size:1.5rem; font-weight:800; color:var(--green); text-align:center; margin:0.5rem 0; font-family:monospace; }

        /* Buttons */
        .btn {
            display:flex; align-items:center; justify-content:center; gap:8px;
            width:100%; padding:1rem; border-radius:14px;
            border:none; font-family:'Outfit',sans-serif;
            font-size:1rem; font-weight:700; cursor:pointer;
            transition:all 0.25s; text-decoration:none; margin-bottom:0.75rem;
        }
        .btn-reauth { background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; box-shadow:0 8px 20px rgba(34,197,94,0.25); }
        .btn-reauth:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(34,197,94,0.4); }
        .btn-plans  { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; }
        .btn-plans:hover  { transform:translateY(-2px); }
        .btn-logout { background:transparent; border:1px solid rgba(255,255,255,0.1); color:var(--muted); }
        .btn-logout:hover { background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.3); color:#fca5a5; }

        footer { text-align:center; margin-top:1.5rem; color:var(--muted); font-size:0.75rem; }

        @media (max-width:400px) { .card { padding:2rem 1.25rem; } h1 { font-size:1.5rem; } }
    </style>
</head>
<body>
<div class="card">

    {{-- Status badge --}}
    @if($session)
        <div class="badge badge-connected"><span>●</span> Internet Active</div>
    @else
        <div class="badge badge-warning"><span>●</span> No Active Plan</div>
    @endif

    <h1>Hello, {{ $user->full_name ?? 'WiFi User' }} 👋</h1>
    <p class="subtitle">
        @if($session) You're connected to SpeedWave WiFi.
        @else Your plan has expired or is not yet active.
        @endif
    </p>

    {{-- Queued plan notice --}}
    @if(request('queued'))
    <div class="queued-notice">
        📦 <strong>Plan Queued!</strong> Your new plan will activate automatically when the current one expires.
    </div>
    @endif

    {{-- ── INTERNET CONNECTIVITY TEST ── --}}
    <div class="net-status testing" id="netStatus">
        <span id="netIcon">⏳</span>
        <span id="netText">Testing internet access...</span>
    </div>

    {{-- 🔍 DIAGNOSTICS --}}
    <div id="debugInfo" style="display:none; background:rgba(255,255,255,0.04); border-radius:12px; padding:12px; margin-bottom:1.5rem; font-size:0.75rem; color:var(--muted); line-height:1.5; border:1px solid rgba(255,255,255,0.08);">
        <div style="color:var(--amber); font-weight:700; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px;">Diagnostic Info:</div>
        <div>Detected MAC: <strong style="color:var(--text); font-family:monospace;">{{ session('mac') ?? 'Unknown' }}</strong></div>
        <div style="margin-top:8px; font-style:italic;">Check if this MAC matches in Winbox (IP > Hotspot > Hosts).</div>
    </div>

    {{-- Plan stats --}}
    @if($session)
    @php $plan = $session->plan; @endphp
    <div class="stats">
        <div class="stat">
            <div class="lbl">Plan</div>
            <div class="val">{{ $plan->name ?? 'Active' }}</div>
        </div>
        <div class="stat">
            <div class="lbl">Type</div>
            <div class="val">{{ ucfirst($plan->plan_type ?? 'Standard') }}</div>
        </div>
        <div class="stat">
            <div class="lbl">Data</div>
            <div class="val">{{ $plan?->data_label ?? 'Unlimited' }}</div>
        </div>
        <div class="stat">
            <div class="lbl">Speed</div>
            <div class="val">{{ $plan->download_limit ?? 'Best' }}</div>
        </div>
    </div>

    @if($session->expires_at)
    <div class="stat" style="margin-bottom:1.5rem; text-align:center;">
        <div class="lbl" style="text-align:center;">⏱ Session ends in</div>
        <div class="timer" id="countdown">--:--:--</div>
    </div>
    @endif
    @endif

    {{-- No plan --}}
    @if(!$session)
    <a href="/plans" class="btn btn-plans">📦 Buy a Plan</a>
    @endif

    {{-- Fallback Login (Hidden, shown after 10 retries) --}}
    <div id="fallbackLogin" style="display:none; margin-bottom:1.5rem;">
        <button onclick="universalLogin()" class="btn" style="background:var(--indigo); color:white;">
            🚀 Universal Login — Tap to Force
        </button>
        <p style="font-size:0.75rem; color:var(--muted); text-align:center; margin-top:8px;">
            If the auto-connection is slow, tap this to nudge your router.
        </p>
    </div>

    {{-- Re-authenticate button (shown when internet test fails) --}}
    <div id="reauthWrap" style="display:none;">
        <a href="/activate-internet" class="btn btn-reauth">
            ⚡ Fix Internet — Re-authenticate
        </a>
        <p style="font-size:0.78rem; color:var(--muted); text-align:center; margin-bottom:1rem;">
            Tap this if you have a plan but can't browse yet. It re-sends your credentials to the router.
        </p>
    </div>

    {{-- Always show re-auth for users with a plan --}}
    @if($session)
    <a href="/activate-internet" class="btn btn-reauth" id="reauthAlways">
        ⚡ Tap to Activate Internet
    </a>
    @endif

    <a href="/plans" class="btn btn-plans">📶 View / Upgrade Plan</a>

    @if($session)
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-logout">🔌 Disconnect</button>
    </form>
    @endif
</div>

<footer>© 2026 SpeedWave · All sessions logged per Govt. guidelines</footer>

<script>
var SERVER = '{{ rtrim(config("app.url"), "/") }}';

// ── Internet connectivity test ──────────────────────────────────────────────
var retryCount = 0;
var maxRetries = 20; // Try for ~60 seconds (3s intervals)

function testInternet() {
    // Use fetch to our own server's ping endpoint — always reachable if internet works
    // We use a timestamp to prevent caching
    fetch('https://www.google.com/favicon.ico?_=' + Date.now(), {
        method: 'HEAD',
        mode: 'no-cors',
        cache: 'no-store'
    })
    .then(function() {
        // fetch with no-cors resolves even on opaque responses — means internet works!
        setOnline();
    })
    .catch(function() {
        handleFailure();
    });
}

function handleFailure() {
    if (retryCount < maxRetries) {
        retryCount++;
        document.getElementById('netText').textContent = 'Waiting for router (Attempt ' + retryCount + ')...';
        
        // After 10 attempts, show diagnostics and the fallback nudge
        if (retryCount == 10) {
            document.getElementById('debugInfo').style.display = 'block';
            if (document.getElementById('fallbackLogin')) document.getElementById('fallbackLogin').style.display = 'block';
            universalLogin(); // Try silent nudge automatically
        }
        
        setTimeout(testInternet, 3000); 
    } else {
        setOffline();
    }
}

function universalLogin() {
    console.log("PMWANI: Attempting Universal Login Nudge...");
    var routerIp = '{{ env("MIKROTIK_HOST", "192.168.88.1") }}';
    var username = '{{ $user->mobile ?? "" }}';
    
    // Nudge Method 1: Background ping to router IP (often triggers authorized=yes hosts)
    var img = new Image();
    img.src = "http://" + routerIp + "/login?username=" + username + "&password=" + username + "&_=" + Date.now();
    
    // Nudge Method 2: Ping common fallback URL
    var img2 = new Image();
    img2.src = "http://1.1.1.1/generate_204?_=" + Date.now();
}

function setOnline() {
    var el = document.getElementById('netStatus');
    el.className = 'net-status online';
    document.getElementById('netIcon').textContent = '✅';
    document.getElementById('netText').textContent = 'Internet is working! Enjoy!';
    if (document.getElementById('reauthWrap')) document.getElementById('reauthWrap').style.display = 'none';
    if (document.getElementById('reauthAlways')) document.getElementById('reauthAlways').style.display = 'none';
}

function setOffline() {
    var el = document.getElementById('netStatus');
    el.className = 'net-status offline';
    document.getElementById('netIcon').textContent = '❌';
    document.getElementById('netText').textContent = 'No internet — tap "Fix Internet" below';
    document.getElementById('reauthWrap').style.display = 'block';
}

// Start test after 1s
setTimeout(testInternet, 1500);

// ── Countdown timer ─────────────────────────────────────────────────────────
@if($session && $session->expires_at)
var expiry = new Date('{{ $session->expires_at }}').getTime();
function tick() {
    var diff = expiry - Date.now();
    if (diff <= 0) {
        document.getElementById('countdown').textContent = 'EXPIRED';
        document.getElementById('netStatus').className = 'net-status offline';
        document.getElementById('netText').textContent = 'Plan expired — please renew';
        return;
    }
    var h = Math.floor(diff / 3600000);
    var m = Math.floor((diff % 3600000) / 60000);
    var s = Math.floor((diff % 60000) / 1000);
    document.getElementById('countdown').textContent =
        (h < 10 ? '0' : '') + h + ':' +
        (m < 10 ? '0' : '') + m + ':' +
        (s < 10 ? '0' : '') + s;
}
setInterval(tick, 1000); tick();
@endif
</script>
</body>
</html>
