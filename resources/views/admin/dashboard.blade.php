@extends('admin.layout')
@section('content')

<style>
    .card .card-heading{
        font-size: 28px;
        text-align: center;
        padding: 16px;
        text-decoration: underline 2px skyblue;
    }
</style>
<h1>Wifi Analytics Dashboard</h1>


<div class="card">
    <h3>Active Users</h3>
    <!-- <h2>{{ $users }}</h2> -->
     <h2 id="activeUsers">0</h2>
</div>

<div class="card">
    <h3>Active Sessions</h3>
    <!-- <h2 class="success">{{ $activeSessions }}</h2> -->
     <h2 id="sessionsToday">0</h2>
</div>

<div class="card">
    <h3>OTP Requests</h3>
    <!-- <h2>{{$otpRequests }}</h2> -->
    <h2 id="otpToday">0</h2>
</div>

<div class="card">
    <h3>Login Activity</h3>
    <canvas id="loginChart"></canvas>
</div>

<div class="card">
    <h3>Total Users</h3>
    <h2 id="totalUsers">0</h2>
</div>


<!-- Step : 23 Revenue Section -->

<div class="card">
    <h3>Today's Revenue</h3>
    <h2 id="todayRevenue">₹0</h2>
</div>

<div class="card">
    <h3>Monthly Revenue</h3>
    <h2 id="monthlyRevenue">₹0</h2>
</div>

<div class="card">
    <h3>Total Revenue</h3>
    <h2 id="totalRevenue">₹0</h2>
</div>

<div class="card">
    <h3>Total Transactions</h3>
    <h2 id="totalTransactions">0</h2>
</div>

<div class="card">
    <h3>Revenue Chart</h3>
    <canvas id="revenueChart"></canvas>
</div>

<!-- Show Router Status -->
<div class="card">
    <h3>Router Status</h3>
    <span id="routerStatus" class="warning">Checking...</span>
</div>

<!-- Show Connected Devices -->
<div class="card">
    <h3 class="card-heading">Connected Devices</h3>
    <table width="100%" id="devicesTable">
        <thead>
            <tr>
                <th>Mobile</th>
                <th>IP</th>
                <th>MAC</th>
                <th>Login Time</th>
                <th>Device</th>
                <th>Browser</th>
                <th>OS</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>    </tbody>
    </table>

</div>

<script>

document.addEventListener("DOMContentLoaded", function() 
{
     // analytics data by json api 
     function loadAnalytics(){
        fetch('/admin/analytics-data')
        .then(res => res.json())
        .then(data => {
            document.getElementById('activeUsers').innerText = data.active_users
            document.getElementById('sessionsToday').innerText = data.sessions_today
            document.getElementById('otpToday').innerText = data.otp_today
            document.getElementById('totalUsers').innerText = data.total_users
        })
     }
     loadAnalytics()
     // update dasboard every 10 seconds 
     setInterval(loadAnalytics,10000)

     function loadRouterStatus(){
        fetch('/admin/router-status')
        .then(res=>res.json())
        .then(data=>{
            const el = document.getElementById('routerStatus');
            el.innerText = data.status.toUpperCase();
            el.className = data.status === 'online' ? 'success' : 'danger';
        })
        .catch(err => {
            const el = document.getElementById('routerStatus');
            el.innerText = 'ERROR';
            el.className = 'danger';
        });
     }
     loadRouterStatus();
     setInterval(loadRouterStatus, 30000);

     // login users charts 
    const ctx = document.getElementById('loginChart').getContext('2d')
    new Chart(ctx,{
        type:'line',
        data:{
            labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets:[{
                label:'Logins',
                data:[12,19,3,5,2,3,9],
                borderColor:'#89DCEB',
                tension:0.4
                }]
            }
    })

    //Step : 23 Revenue Chart
    function loadRevenue(){
        fetch('/admin/revenue-data')
        .then(res=>res.json())
        .then(data=>{
            document.getElementById('todayRevenue').innerText = '₹' + data.today;
            document.getElementById('monthlyRevenue').innerText = '₹' + data.monthly;
            document.getElementById('totalRevenue').innerText = '₹' + data.total;
            document.getElementById('totalTransactions').innerText = data.transactions;
            
            updateRevenueChart(data.chart);
        });

    }

    //Auto load 
    loadRevenue();
    setInterval(loadRevenue, 10000);
    
    //Revenue Chart
    let revenueChart;

    function updateRevenueChart(chartData){
        const ctx = document.getElementById('revenueChart').getContext('2d');

        if(revenueChart){
            revenueChart.data.datasets[0].data = chartData;
            revenueChart.update();
            return;
        }

        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['6d', '5d', '4d', '3d', '2d', '1d', 'Today'],
                datasets: [{
                    label: 'Revenue',
                    data: chartData,
                    borderColor: '#A6E3A1',
                    tension: 0.4

                }]
            }
        });
    }

});

    // Load Connected Devices
    function loadSessions(){
        fetch('{{ url("/admin/active-sessions") }}')
        .then(res=>res.json())
        .then(data=>{
            let html = ''
            data.forEach(session => {
                html += `
                <tr>
                <td>${session.user.mobile}</td>
                <td>${session.ip_address}</td>
                <td>${session.mac_address}</td>
                <td>${session.login_at}</td>
                <td>${session.device_name ?? '-'}</td>
                <td>${session.browser ?? '-'}</td>
                <td>${session.os ?? '-'}</td>
                <td><button onclick="disconnectUser(${session.id})">Disconnect</button></td>
                </tr>
                `
            })
            document.querySelector('#devicesTable tbody').innerHTML = html
        })
    }

    function disconnectUser(id){
        fetch('{{ url("/admin/disconnect-user") }}',{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body:JSON.stringify({
                    session_id:id
                })
            })
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    alert("User disconnected");
                    loadSessions();
                }
            })
        }

        //Auto Refresh Sessions
        loadSessions();
        setInterval(loadSessions,5000);



</script>
        
@endsection

