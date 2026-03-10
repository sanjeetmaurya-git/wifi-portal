<!DOCTYPE html>
<html>
<head>
<title>WiFi Login</title>
</head>

<body>

<h2>WiFi Login</h2>

<form action="/send-otp" method="POST">
@csrf

<input type="text" name="mobile" placeholder="Enter Mobile Number">

<button type="submit">Send OTP</button>

</form>

</body>
</html>