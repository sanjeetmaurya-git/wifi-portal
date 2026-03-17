<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin dashboard</title>

    <!-- charts js library -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        *{
            margin: 0;
            padding: 0;

        }
        body{
            background:#1E1E2E;
            color:#CDD6F4;
            font-family:Arial;
            margin:0;
        }

        .sidebar{
            width: 220px;
            height: 100vh;
            background: #45475A;
            position: fixed;
            padding: 20px;
            /* top: 60px; */
        }
        .sidebar a{
            display: block;
            color:#CDD6F4;
            text-decoration:none;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .sidebar a:hover{
            background:#89DCEB;
            color:#1E1E2E;
        }

        /* Header container */
        .header {
            height: 60px;
            background: #313244; /* Slightly different from sidebar/body for depth */
            margin-left: 220px; /* Same as sidebar width */
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Pushes content to the right */
            padding: 0 30px;
            border-bottom: 1px solid #45475A;
        }

        /* Admin Profile Group */
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-name {
            font-weight: bold;
            font-size: 14px;
        }

        .admin-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #89DCEB;
        }

        /* Adjust Content to sit below header */
        .content {
            margin-left: 220px;
            padding: 30px;
            /* Removed the top margin if you want it flush, 
            or keep it for a "floating" look */
        }

        .content{
            margin-left: 240px;
            padding: 30px;
        }

        .card{
            background:#45475A;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        button{
            background: #89DCEB;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .success{
            color:#A6E3A1;
            }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>WiFi Portal Admin</h2>
        <a href="/admin">Dashboard</a>
        <a href="/admin/users">Users</a>
        <a href="/admin/sessions">Sessions</a>
        <a href="/admin/otp-logs">OTP Logs</a>
        <a href="/admin/plans/">Plan</a>
        <!-- <a href="/admin/plans">Plans</a> -->
    </div>
    <!-- header of admin dashboard  -->
    <div class="header">
        <div class="admin-profile">
            <span class="admin-name" style="font-style:italic;">Sanjeet</span>
            <img src="https://ui-avatars.com/api/?name=Sm&background=89DCEB&color=1E1E2E" alt="Profile" class="admin-pic">
        </div>
    </div>
    <!-- main content od dashboard  -->
    <div class="content">
        @yield('content')
    </div>

</body>
</html>