<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting to WiFi...</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            min-height: 100vh; display: flex; align-items: center;
            justify-content: center; font-family: 'Outfit', sans-serif; color: white;
        }
        .card {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 24px;
            padding: 48px 36px; text-align: center; max-width: 380px; width: 92%;
        }
        .ring {
            width: 72px; height: 72px; border: 5px solid rgba(255,255,255,0.1);
            border-top: 5px solid #00d4aa; border-radius: 50%;
            animation: spin 0.9s linear infinite; margin: 0 auto 28px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .sub { color: rgba(255,255,255,0.55); font-size: 14px; margin-bottom: 12px; }
        #fallback { display: none; }
        .btn {
            display: block; width: 100%; margin-top: 24px;
            background: linear-gradient(135deg, #00d4aa, #0072ff);
            color: white; border: none; padding: 18px; border-radius: 14px;
            font-size: 18px; font-weight: 700; cursor: pointer; font-family: 'Outfit', sans-serif;
        }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
<div class="card">
    <div id="phase1">
        <div class="ring"></div>
        <h1>Finishing Connection</h1>
        <p class="sub">Authenticating with the bridge...</p>
    </div>

    <div id="fallback">
        <div style="font-size:56px;margin-bottom:20px;">🔌</div>
        <h1>Almost Connected</h1>
        <p class="sub">Your device needs a manual tap to finish.</p>
        <button class="btn" onclick="document.getElementById('mikrotikForm').submit()">
            ⚡ Tap to Finish
        </button>
    </div>
</div>

{{-- MikroTik Handshake Form: MUST use POST for modern RouterOS versions --}}
<form id="mikrotikForm" method="POST" action="{{ $link_login }}" style="display:none">
    <input type="hidden" name="username" value="{{ $username }}">
    <input type="hidden" name="password" value="{{ $password }}">
    <input type="hidden" name="dst"      value="{{ $dst ?? url('/success') }}">
</form>

<script>
(function() {
    // Show fallback after 8s
    setTimeout(function(){
        document.getElementById('phase1').style.display = 'none';
        document.getElementById('fallback').style.display = 'block';
    }, 8000);

    // Submit form with tiny delay
    setTimeout(function(){
        try {
            document.getElementById('mikrotikForm').submit();
        } catch(e) {
            document.getElementById('phase1').style.display = 'none';
            document.getElementById('fallback').style.display = 'block';
        }
    }, 800);
})();
</script>
</body>
</html>