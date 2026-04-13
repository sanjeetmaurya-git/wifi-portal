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
        <div class="card">
            <h2 style="margin-top:0;">My Profile</h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:25px;">Your registered details and devices.</p>

            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value">{{ $user->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Primary Number</span>
                <span class="info-value">{{ $user->mobile }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Address</span>
                <span class="info-value">{{ $user->address }}, {{ $user->city }}</span>
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
