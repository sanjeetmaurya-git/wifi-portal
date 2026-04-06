<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting...</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #1E1E2E;
            color: #CDD6F4;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            gap: 20px;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 5px solid #313244;
            border-top-color: #89DCEB;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        p {
            font-size: 15px;
            color: #6C7086;
        }
    </style>
</head>

<body>

    <div class="spinner"></div>
    <p>Connecting to internet, please wait...</p>

    {{-- 🔥 THE KEY: Hidden POST form — MikroTik requires POST, not GET redirect --}}
    <form id="mikrotikForm" method="POST" action="{{ $link_login }}">
        <input type="hidden" name="username" value="{{ $username }}">
        <input type="hidden" name="password" value="{{ $password }}">
        @if(isset($mac))
            <input type="hidden" name="mac" value="{{ $mac }}">
        @endif
    </form>

    <script>
        // Auto-submit immediately on page load
        window.onload = function () {
            document.getElementById('mikrotikForm').submit();
        };
    </script>

</body>

</html>