<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - WiFi Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0F172A;
            --card-bg: #1E293B;
            --accent-color: #38BDF8;
            --text-color: #F1F5F9;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--accent-color);
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
        }

        input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: #334155;
            border: 1px solid #475569;
            border-radius: 10px;
            color: white;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 15px;
            background: var(--accent-color);
            color: #0F172A;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            opacity: 0.8;
            transform: scale(0.98);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .input-group {
            flex: 1;
        }

        .declaration {
            background: #0F172A;
            padding: 15px;
            border-radius: 10px;
            font-size: 13px;
            color: #94A3B8;
            margin: 15px 0;
            border: 1px solid #334155;
        }

        .declaration input {
            width: auto;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2><i class="fas fa-id-card"></i> WiFi Registration</h2>
        <p style="text-align: center; color: #94A3B8; margin-bottom: 25px;">Please provide your KYC details to access the internet.</p>

        <form action="{{ route('register.save') }}" method="POST">
            @csrf
            <input type="hidden" name="mobile" value="{{ $mobile }}">
            <input type="hidden" name="mac" value="{{ $mac ?? '' }}">
            <input type="hidden" name="ip" value="{{ $ip ?? '' }}">
            <input type="hidden" name="link_login" value="{{ $link_login ?? '' }}">

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <i class="fas fa-home"></i>
                <input type="text" name="address" placeholder="Residential Address" required>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <i class="fas fa-city"></i>
                    <input type="text" name="city" placeholder="City" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="district" placeholder="District" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <i class="fas fa-map"></i>
                    <input type="text" name="state" placeholder="State" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-location-arrow"></i>
                    <input type="text" name="pincode" placeholder="Pincode" required>
                </div>
            </div>

            <div class="input-group">
                <i class="fas fa-id-badge"></i>
                <input type="text" name="id_proof" placeholder="Aadhar/ID Proof Number (Optional)">
            </div>

            <div class="declaration">
                <label>
                    <input type="checkbox" required name="consent">
                    I declare that I will not misuse this network. I am sole responsible for any legal action if I misuse this data or internet connection.
                </label>
            </div>

            <button type="submit">Verify & Get Access <i class="fas fa-arrow-right"></i></button>
        </form>
    </div>
</body>

</html>
