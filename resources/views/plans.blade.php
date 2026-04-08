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
        <p style="text-align: center; color: #666; margin-bottom: 40px;">Choose a plan to continue surfing the internet.
        </p>

        <div
            style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; max-width: 1200px; margin: 0 auto;">
            @foreach($plans as $plan)
                <div
                    style="background: white; border-radius: 12px; padding: 30px; width: 280px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: transform 0.3s ease; position:relative;">
                    
                    @if($plan->is_free)
                        <div style="position:absolute; top:-10px; right:-10px; background:#e74c3c; color:white; padding:5px 15px; border-radius:30px; font-weight:bold; font-size:12px; transform:rotate(10deg); box-shadow:0 5px 15px rgba(231,76,60,0.3);">FREE TRIAL</div>
                    @endif

                    <h2 style="color: #2c3e50; margin-top: 0;">{{ $plan->name }}</h2>
                    <div style="font-size: 32px; font-weight: bold; color: #27ae60; margin: 20px 0;">₹{{ $plan->price }}
                    </div>

                    <div style="text-align: left; margin-bottom: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <div style="margin-bottom: 10px; color: #555;">⏱ <b>Duration:</b>
                            {{ $plan->duration_minutes }} Minutes
                        </div>
                        <div style="margin-bottom: 10px; color: #555;">🚀 <b>Speed:</b>
                            {{ $plan->upload_limit ?: 'Best' }}/{{ $plan->download_limit ?: 'Available' }}
                        </div>
                        <div style="margin-bottom: 10px; color: #555;">📊 <b>Data Limit:</b>
                            {{ $plan->limit_bytes ? $plan->limit_bytes.' MB' : 'Unlimited' }} 
                        </div>
                    </div>

                    <form action="/create-order" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        
                        @php
                            $isClaimed = $plan->is_free && isset($claimedFreePlans) && in_array($plan->id, $claimedFreePlans);
                        @endphp
                        @if($isClaimed)
                            <button type="button" disabled
                                style="background-color: #95a5a6; color: white; border: none; padding: 14px 20px; border-radius: 8px; cursor: not-allowed; font-size: 16px; font-weight: bold; width: 100%;">
                                Already Claimed
                            </button>
                        @elseif($plan->is_free)
                            <button type="submit"
                                style="background-color: #3498db; color: white; border: none; padding: 14px 20px; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: bold; width: 100%; transition: background 0.3s;">
                                Claim Now
                            </button>
                        @else
                            <button type="submit"
                                style="background-color: #27ae60; color: white; border: none; padding: 14px 20px; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: bold; width: 100%; transition: background 0.3s;">
                                Buy Now
                            </button>
                        @endif
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>