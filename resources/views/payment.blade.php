<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout – SpeedWave WiFi</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: #fff; margin: 0; padding: 20px; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 20px;
            padding: 40px; text-align: center; width: 100%; max-width: 420px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .btn-pay {
            background: linear-gradient(135deg, #00d4aa, #0072ff);
            color: white; border: none; padding: 18px 30px; border-radius: 12px;
            width: 100%; font-size: 18px; font-weight: 700; cursor: pointer;
            transition: transform 0.2s; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            margin-top: 10px;
        }
        .btn-pay:active { transform: scale(0.98); }
        .btn-pay:disabled { opacity: 0.6; cursor: not-allowed; }
        .amount { font-size: 48px; font-weight: 700; color: #00d4aa; margin: 20px 0; }
        #statusMsg { display:none; margin-top: 20px; padding: 16px; border-radius: 12px;
            font-size: 15px; line-height: 1.5; }
        #statusMsg.error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #fca5a5; }
        #statusMsg.info  { background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); color: #c7d2fe; }
        .spinner { display:inline-block; width:20px; height:20px; border:3px solid rgba(255,255,255,0.3);
            border-top-color:#fff; border-radius:50%; animation:spin 0.8s linear infinite; vertical-align:middle; margin-right:8px; }
        @keyframes spin { to { transform:rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="margin:0;">Finalize Payment</h2>
        <p style="color:rgba(255,255,255,0.5); margin-top:6px;">Order ID: {{ session('order_id') }}</p>
        
        <div class="amount">₹{{ session('amount', 0) }}</div>
        
        <p style="margin-bottom:20px; color:rgba(255,255,255,0.8);">Tap the button to pay securely via Razorpay.</p>
        
        <button id="payBtn" class="btn-pay">💳 PAY SECURELY NOW</button>

        <div id="statusMsg"></div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    // ─────────────────────────────────────────────────────────────────────────
    // CRITICAL: Use absolute server URL so this fetch always works even when
    // the phone reaches this page via MikroTik redirect (hotspot interception).
    // Relative URLs like '/payment-success' may resolve to the router IP instead
    // of the actual server, causing a network error → "Something went wrong".
    // ─────────────────────────────────────────────────────────────────────────
    var SERVER_URL = '{{ rtrim(config("app.url"), "/") }}';

    function showStatus(type, msg) {
        var el = document.getElementById('statusMsg');
        el.className = type;
        el.innerHTML = msg;
        el.style.display = 'block';
    }

    var options = {
        key:         '{{ config("services.razorpay.key") }}',
        amount:      '{{ session("amount", 0) * 100 }}',
        currency:    'INR',
        name:        'SpeedWave WiFi',
        description: 'WiFi Plan Purchase',
        order_id:    '{{ session("order_id") }}',
        theme:       { color: '#00d4aa' },

        handler: function(response) {
            var btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Activating Internet...';
            showStatus('info', '⏳ Payment verified! Activating your plan...');

            // POST to absolute URL so it always reaches our Laravel server
            fetch(SERVER_URL + '/payment-success', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id:   response.razorpay_order_id,
                    razorpay_signature:  response.razorpay_signature
                })
            })
            .then(function(r) {
                // Handle non-JSON responses (PHP error pages)
                var contentType = r.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    return r.text().then(function(text) {
                        throw new Error('Server returned non-JSON. Status: ' + r.status + 
                            '. Check Laravel logs. Preview: ' + text.substring(0, 200));
                    });
                }
                return r.json();
            })
            .then(function(data) {
                if (data.redirect) {
                    showStatus('info', '✅ Payment successful! Connecting you to internet...');
                    // Small delay so user sees the success message
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 800);
                } else if (data.error) {
                    showStatus('error', '❌ ' + data.error + '<br><br><a href="/plans" style="color:#93c5fd;">← Back to Plans</a>');
                    btn.disabled = false;
                    btn.innerHTML = '💳 PAY SECURELY NOW';
                }
            })
            .catch(function(err) {
                console.error('Payment fetch error:', err);
                showStatus('error',
                    '⚠️ <b>Connection error after payment.</b><br>' +
                    'Your payment was received by Razorpay but we could not activate your plan.<br><br>' +
                    '<b>Error:</b> ' + err.message + '<br><br>' +
                    'Please <a href="/activate-internet" style="color:#93c5fd;">click here to activate manually</a> ' +
                    'or contact support with Order ID: {{ session("order_id") }}'
                );
                btn.disabled = false;
                btn.innerHTML = '🔄 Retry Activation';
                btn.onclick = function() { window.location.href = SERVER_URL + '/activate-internet'; };
            });
        },

        modal: {
            ondismiss: function() {
                document.getElementById('payBtn').disabled = false;
                document.getElementById('payBtn').innerHTML = '💳 PAY SECURELY NOW';
            }
        }
    };

    var rzp = new Razorpay(options);

    document.getElementById('payBtn').onclick = function(e) {
        rzp.open();
        e.preventDefault();
    };
    </script>
</body>
</html>