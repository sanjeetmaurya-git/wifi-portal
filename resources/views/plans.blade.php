<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select a WiFi Plan</title>
</head>
<body style="background-color: #f4f7f6; margin: 0; padding: 0; font-family: Arial, sans-serif;">
<div class="container" style="padding: 40px 20px;">
    <h1 style="text-align: center; color: #1a1a1a; margin-bottom: 10px;">Select a WiFi Plan</h1>
    <p style="text-align: center; color: #666; margin-bottom: 40px;">Choose a plan to continue surfing the internet.</p>

    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; max-width: 1200px; margin: 0 auto;">
        @foreach($plans as $plan)
        <div style="background: white; border-radius: 12px; padding: 30px; width: 280px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
            <h2 style="color: #2c3e50; margin-top: 0;">{{ $plan->name }}</h2>
            <div style="font-size: 32px; font-weight: bold; color: #27ae60; margin: 20px 0;">₹{{ $plan->price }}</div>
            
            <div style="text-align: left; margin-bottom: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <div style="margin-bottom: 10px; color: #555;">⏱ <b>Duration:</b> {{ $plan->duration_minutes }} Mins</div>
                <div style="margin-bottom: 10px; color: #555;">🚀 <b>Speed:</b> {{ $plan->upload_limit }}/{{ $plan->download_limit }}</div>
                <div style="margin-bottom: 10px; color: #555;">📊 <b>Data Limit:</b> {{ $plan->data_limit_mb ?? 'Unlimited' }} MB</div>
            </div>

            <form action="/create-order" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 14px 20px; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: bold; width: 100%; transition: background 0.3s;">
                    Buy Now
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
</body>
</html>
