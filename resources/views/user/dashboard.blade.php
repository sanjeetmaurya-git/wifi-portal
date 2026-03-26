<!DOCTYPE html>
<html>
    <head>
        <title>User Dashboard</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>

    <body style="background:#1E1E2E; color:#CDD6F4; font-family:Arial;">

        <!-- Error Message -->
        @if(session('error'))
        <div style="background:#f38ba8;color:#1E1E2E;padding:10px;text-align:center;">
            {{ session('error') }}
        </div>
        @endif

        <div style="max-width:900px;margin:30px auto;">
            <h2 style="text-align:center;">User Dashboard</h2>

            <!-- Cards -->
            <div style="display:flex;gap:15px;flex-wrap:wrap;justify-content:space-between;">

                <div style="background:#45475A;padding:20px;border-radius:10px;width:48%;">
                    <h3>Current Status</h3>
                    <p id="statusText">Checking...</p>
                </div>

                <div style="background:#45475A;padding:20px;border-radius:10px;width:48%;">
                    <h3>Remaining Time</h3>
                    <p id="remainingTime">--</p>
                </div>
            </div>

            <!-- Actions -->
            <div style="margin-top:20px;text-align:center;">
                <a href="/my-plans" style="padding:10px 20px;background:#89b4fa;color:#1E1E2E;border-radius:6px;text-decoration:none;">
              My Plans
                </a>
                <a href="/plans" style="padding:10px 20px;background:#A6E3A1;color:#1E1E2E;border-radius:6px;text-decoration:none;margin-left:10px;">
                    Buy Plan
                </a>
        </div>
    </div>

<!-- 🔥 Live Session Check -->
<script>
function checkSession(){
    fetch('/check-session')
    .then(res => res.json())
    .then(data => {
        if(!data.active){
            document.getElementById('statusText').innerText = "Expired";
            alert("Plan expired!");
            window.location.href = "/plans";
        } else {
            document.getElementById('statusText').innerText = "Active";
        }
    });
}

// run every 10 sec
setInterval(checkSession, 10000);
checkSession();
</script>

</body>
</html>