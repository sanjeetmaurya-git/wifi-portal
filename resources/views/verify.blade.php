<div>
    <form action="{{ url('/verify-otp') }}" method="POST">
        @csrf
        <input type="hidden" name="mobile" value="{{ $mobile }}">
        <input type="text" name="otp" placeholder="Enter Otp">
        <button type="submit">Verify Otp</button>
    </form>
</div>
