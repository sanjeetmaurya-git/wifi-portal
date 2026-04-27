<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: #fff; margin: 0; padding: 0; min-height: 100vh;
        }
        .container { max-width: 600px; margin: 40px auto; padding: 20px; }
        .card {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 30px;
        }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .info-label { color: rgba(255,255,255,0.5); font-size: 14px; }
        .info-value { font-weight: 600; }
        .btn-add {
            background: linear-gradient(135deg, #00d4aa, #0072ff);
            color: white; border: none; padding: 12px 20px; border-radius: 12px;
            width: 100%; margin-top: 25px; font-weight: 700; cursor: pointer; font-family: 'Outfit';
        }
        input {
            background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 12px; border-radius: 10px; width: 100%; box-sizing: border-box;
            margin-top: 10px; font-family: 'Outfit';
        }
    </style>
</head>
<body>
    @include('partials.header')

    <div class="container">
        <!-- 📡 Usage Dashboard -->
        <div class="card" style="margin-bottom: 25px; background: linear-gradient(135deg, rgba(0,212,170,0.1), rgba(0,114,255,0.1));">
            <h3 style="margin-top:0; color: #00d4aa;">Current Usage</h3>
            
            @if($session)
                <div style="margin: 20px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Data Consumed</span>
                        <span style="font-weight:700; color: #00d4aa;">{{ $usage }} MB</span>
                    </div>
                    <div style="height: 10px; background: rgba(255,255,255,0.1); border-radius: 5px; overflow: hidden;">
                        <div style="width: {{ min(($usage / 1024) * 100, 100) }}%; height: 100%; background: linear-gradient(90deg, #00d4aa, #0072ff);"></div>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Active Plan</span>
                    <span class="info-value" style="color: #00d4aa;">{{ $session->plan->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Expires In</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($session->expires_at)->diffForHumans(null, true) }}</span>
                </div>
            @else
                <div style="text-align: center; padding: 20px 0;">
                    <p style="color: rgba(255,255,255,0.5);">No active data plan found.</p>
                    <a href="/plans" class="btn-add" style="text-decoration:none; display:inline-block; width: auto; padding: 10px 30px;">Buy a Plan</a>
                </div>
            @endif
        </div>

        <div class="card">
            <h3 style="margin-top:0;">My Account</h3>
            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value">{{ $user->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Primary Number</span>
                <span class="info-value">{{ $user->mobile }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">MAC Address</span>
                <span class="info-value" style="font-family: monospace; font-size: 13px;">{{ $user->mac_address ?? 'Not detected' }}</span>
            </div>

            <hr style="border:0; border-top: 1px solid rgba(255,255,255,0.1); margin: 30px 0;">

            <h3>Add Another Number</h3>
            <p style="color:rgba(255,255,255,0.5); font-size:13px;">Link another device to your profile. Requires OTP.</p>
            
            <form action="/add-secondary-number" method="POST">
                @csrf
                <input type="text" name="secondary_mobile" placeholder="Enter secondary number" required maxlength="10">
                <button type="submit" class="btn-add">Verify & Add Number</button>
            </form>
        </div>
    </div>
</body>
</html>
