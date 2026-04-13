<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: #fff; margin: 0; padding: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 20px;
            padding: 40px; text-align: center; width: 100%; max-width: 400px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .btn-pay {
            background: linear-gradient(135deg, #00d4aa, #0072ff);
            color: white; border: none; padding: 18px 30px; border-radius: 12px;
            width: 100%; font-size: 18px; font-weight: 700; cursor: pointer;
            transition: transform 0.2s; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .btn-pay:active { transform: scale(0.98); }
        .amount { font-size: 48px; font-weight: 700; color: #00d4aa; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="margin:0;">Finalize Payment</h2>
        <p style="color:rgba(255,255,255,0.5);">Order ID: {{ session('order_id') }}</p>
        
        <div class="amount">₹{{ session('amount', 0) }}</div>
        
        <p style="margin-bottom:30px; color:rgba(255,255,255,0.8);">Click the button below to complete your transaction securely via Razorpay.</p>
        
        <button id="payBtn" class="btn-pay">PAY SECURELY NOW</button>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    var options = {
        "key": "{{ config('services.razorpay.key') }}",
        "amount": "{{ session('amount', 0) * 100 }}",
        "currency": "INR",
        "name": "SpeedWave WiFi",
        "description": "WiFi Plan Purchase",
        "order_id": "{{ session('order_id') }}",
        "theme": { "color": "#00d4aa" },
        "handler": function (response){
            document.body.innerHTML = '<div style="text-align:center; padding:100px; font-family:Outfit; color:white;"><h2>Processing Payment...</h2><p>Please do not refresh this page.</p></div>';
            
            fetch('/payment-success', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                })
            }).then(async r => {
                const data = await r.json();
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.error) {
                    alert('Payment error: ' + data.error);
                    location.reload();
                }
            }).catch(async err => {
                alert('Something went wrong. Please check connection.');
                location.reload();
            });
        }
    };

    var rzp = new Razorpay(options);

    document.getElementById('payBtn').onclick = function(e){
        rzp.open();
        e.preventDefault();
    }
    </script>
</body>
</html>