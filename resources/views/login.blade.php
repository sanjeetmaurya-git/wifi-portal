<!DOCTYPE html>
<html>
    <head>
        <title>WiFi Login</title>
    </head>

    <body>
        <h2>WiFi Login</h2>

        <form action="/send-otp" method="POST">
            @csrf
            <input type="hidden" name="mac" value="{{ request('mac') }}">
            <input type="hidden" name="ip" value="{{ request('ip') }}">
            <input type="hidden" name="link_login" value="{{ request('link-login') }}">

            <input type="number" name="mobile" placeholder="Enter Mobile Number">
            <button type="submit">Send OTP</button>
        </form>
        
    </body>
</html>