<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<button id="payBtn">Pay Now</button>

<script>
var options = {
    "key": "{{ env('RAZORPAY_KEY') }}",
    "amount": "{{ session('amount', 100) * 100 }}",
    "currency": "INR",
    "order_id": "{{ session('order_id') }}",

    "handler": function (response){
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
        }).then(r => r.json())
        .then(data => {
            if (data.redirect) {
                // Navigate browser to /activate-internet (full page load = reliable form auto-submit)
                window.location.href = data.redirect;
            } else if (data.error) {
                alert('Payment error: ' + data.error);
            }
        }).catch(err => {
            console.error('Payment callback error:', err);
            alert('Payment processed but connection failed. Please contact support.');
        });
    }
};

var rzp = new Razorpay(options);

document.getElementById('payBtn').onclick = function(e){
    rzp.open();
    e.preventDefault();
}
</script>