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
                payment_id: response.razorpay_payment_id,
                order_id: response.razorpay_order_id
            })
        }).then(() => {
            window.location.href = "/success";
        });
    }
};

var rzp = new Razorpay(options);

document.getElementById('payBtn').onclick = function(e){
    rzp.open();
    e.preventDefault();
}
</script>