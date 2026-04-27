<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting to PMWANI WiFi...</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh; display: flex; align-items: center;
            justify-content: center; font-family: 'Outfit', sans-serif; color: white;
        }
        .card {
            background: rgba(255,255,255,0.06); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12); border-radius: 28px;
            padding: 52px 40px; text-align: center; max-width: 400px; width: 92%;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }
        .logo { font-size: 48px; margin-bottom: 20px; }
        .ring-wrap { position: relative; width: 80px; height: 80px; margin: 0 auto 28px; }
        .ring {
            width: 80px; height: 80px;
            border: 4px solid rgba(255,255,255,0.08);
            border-top: 4px solid #22c55e; border-radius: 50%;
            animation: spin 0.9s linear infinite;
            position: absolute; top: 0; left: 0;
        }
        .ring2 {
            width: 60px; height: 60px;
            border: 4px solid rgba(255,255,255,0.05);
            border-bottom: 4px solid #06b6d4; border-radius: 50%;
            animation: spin 1.4s linear infinite reverse;
            position: absolute; top: 10px; left: 10px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .sub { color: rgba(255,255,255,0.5); font-size: 14px; margin-bottom: 28px; line-height: 1.6; }
        .progress-bar-wrap {
            background: rgba(255,255,255,0.08); border-radius: 100px;
            height: 6px; margin-bottom: 18px; overflow: hidden;
        }
        .progress-bar {
            height: 100%; width: 0%;
            background: linear-gradient(90deg, #22c55e, #06b6d4);
            border-radius: 100px; transition: width 0.8s ease;
        }
        .step-text { font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 24px; min-height: 20px; }
        .note { font-size: 11px; color: rgba(255,255,255,0.2); margin-top: 20px; line-height: 1.6; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">📶</div>
    <div class="ring-wrap">
        <div class="ring"></div>
        <div class="ring2"></div>
    </div>
    <h1 id="title">Connecting to PMWANI</h1>
    <p class="sub" id="subtitle">Setting up your secure access...<br>Please wait, do not close this page.</p>

    <div class="progress-bar-wrap">
        <div class="progress-bar" id="bar"></div>
    </div>
    <div class="step-text" id="stepText">Registering your device with router...</div>

    <p class="note">
        Your device will be connected automatically<br>in a few seconds.
    </p>
</div>

<script>
// ── Progress bar steps ─────────────────────────────────────────────────────
var steps = [
    { at: 0,    pct: 8,   text: 'Registering your device...' },
    { at: 1500, pct: 30,  text: 'Sending activation signal...' },
    { at: 3500, pct: 55,  text: 'Router processing your request...' },
    { at: 5500, pct: 78,  text: 'Almost ready...' },
    { at: 7000, pct: 92,  text: 'Finalizing your connection...' },
];

var bar      = document.getElementById('bar');
var stepText = document.getElementById('stepText');

steps.forEach(function(s) {
    setTimeout(function() {
        bar.style.width = s.pct + '%';
        stepText.textContent = s.text;
    }, s.at);
});

// ── Redirect to success after 8 seconds ───────────────────────────────────
// The MikroTik scheduler has already authorized this device while we waited.
// No browser-to-router communication needed at all.
setTimeout(function() {
    bar.style.width = '100%';
    stepText.textContent = 'Done! Opening your dashboard...';
    document.getElementById('title').textContent = 'Connected!';
    document.getElementById('subtitle').textContent = 'You now have internet access. Enjoy!';

    setTimeout(function() {
        window.location.href = '{{ url("/success") }}';
    }, 600);
}, 8000);
</script>
</body>
</html>