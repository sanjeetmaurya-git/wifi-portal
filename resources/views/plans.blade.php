<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select a WiFi Plan</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); margin: 0; padding: 0; font-family: 'Outfit', sans-serif; min-height: 100vh; color: white;">
    @include('partials.header')
    <div class="container" style="padding: 40px 20px;">
        <h1 style="text-align: center; color: white; margin-bottom: 10px; font-weight: 700;">Select a WiFi Plan</h1>
        <p style="text-align: center; color: rgba(255,255,255,0.6); margin-bottom: 40px;">Choose a plan to continue surfing the internet.</p>

        <div
            style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; max-width: 1200px; margin: 0 auto;">
            @foreach($plans as $plan)
                <div
                    style="background: rgba(255,255,255,0.08); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 35px 30px; width: 300px; text-align: center; position:relative; overflow: visible;">
                    
                    @if($plan->is_free)
                        <div style="position:absolute; top:-10px; right:-10px; background:#e74c3c; color:white; padding:5px 15px; border-radius:30px; font-weight:bold; font-size:12px; transform:rotate(10deg); box-shadow:0 5px 15px rgba(231,76,60,0.3);">FREE TRIAL</div>
                    @endif

                    <h2 style="color: white; margin-top: 0; font-weight: 700;">{{ $plan->name }}</h2>
                    <div style="font-size: 38px; font-weight: 700; color: #00d4aa; margin: 25px 0;">₹{{ $plan->price }}
                    </div>

                    <div style="text-align: left; margin-bottom: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 25px;">
                        <div style="margin-bottom: 14px; color: rgba(255,255,255,0.8);">⏱ <b>Duration:</b>
                            {{ $plan->duration_minutes }} Minutes
                        </div>
                        <div style="margin-bottom: 14px; color: rgba(255,255,255,0.8);">🚀 <b>Speed:</b>
                            {{ $plan->upload_limit ?: 'Best' }}/{{ $plan->download_limit ?: 'Available' }}
                        </div>
                        <div style="margin-bottom: 14px; color: rgba(255,255,255,0.8);">📊 <b>Data Limit:</b>
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