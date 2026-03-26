<!DOCTYPE html>
<html>
<head>
    <title>User Panel</title>
</head>
<body style="background:#1E1E2E; color:#CDD6F4; font-family:Arial;">
    @if(session('error'))
<div style="background:#f38ba8;color:#1E1E2E;padding:10px;text-align:center;">
    {{ session('error') }}
</div>
@endif
    
    @if(session('error'))
    <div style="background:#f38ba8;color:#1E1E2E;padding:10px;text-align:center;">
        {{ session('error') }}
    </div>
    @endif

    @yield('content')

</body>
</html>