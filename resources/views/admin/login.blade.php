<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Portal</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(-45deg, #615642ff, #313244, #45475a, #11111b);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #cdd6f4;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .login-box {
            background: rgba(30, 30, 46, 0.7);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.8s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .login-box h2 {
            margin: 0 0 30px;
            font-weight: 600;
            font-size: 28px;
            color: #b4befe;
            letter-spacing: 1px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
            text-align: left;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: none;
            border-radius: 12px;
            background: rgba(17, 17, 27, 0.6);
            color: #cdd6f4;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .input-group input:focus {
            background: rgba(17, 17, 27, 0.8);
            border: 1px solid #89b4fa;
            box-shadow: 0 0 15px rgba(137, 180, 250, 0.3);
        }

        .input-group input::placeholder {
            color: #a6adc8;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #89b4fa, #cba6f7);
            color: #11111b;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(203, 166, 247, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .error-msg {
            background: rgba(243, 139, 168, 0.1);
            color: #f38ba8;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(243, 139, 168, 0.2);
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-5px);
            }

            40%,
            80% {
                transform: translateX(5px);
            }
        }

        /* Glowing dots animation in background */
        .glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(137, 180, 250, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            z-index: -1;
            animation: pulse 4s infinite alternate;
        }

        .glow-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(203, 166, 247, 0.1) 0%, rgba(0, 0, 0, 0) 70%);
            border-radius: 50%;
            bottom: -150px;
            right: -100px;
            z-index: -1;
            animation: pulse 6s infinite alternate-reverse;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.2);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="glow"></div>
    <div class="glow-2"></div>

    <div class="login-box">
        <h2>Admin Portal</h2>

        @if(session('error'))
            <div class="error-msg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ url('/admin/login') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required autofocus autocomplete="off">
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit">Secure Login</button>
        </form>
    </div>

</body>

</html>