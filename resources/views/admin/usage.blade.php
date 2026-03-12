@extends('admin.layout')

@section('content')

<h1>Data Usage</h1>

<!-- {{-- ✅ Graph यहाँ --}} -->
<div style="width:100%; max-width:700px;">
    <canvas id="trafficChart"></canvas>
</div>

<table width="100%">
    <thead>
        <tr>
            <th>User</th>
            <th>Download</th>
            <th>Upload</th>
            <th>Time</th>
        </tr>
    </thead>

    <tbody>
        @foreach($stats as $s)

        <tr>
            <td>{{ $s->user->mobile }}</td>
            <td>{{ round($s->download_bytes/1024/1024,2) }} MB</td>
            <td>{{ round($s->upload_bytes/1024/1024,2) }} MB</td>
            <td>{{ $s->recorded_at }}</td>
        </tr>
        @endforeach

    </tbody>
</table>

@endsection

<!-- graph chart js  -->
 @section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // const ctx = document.getElementById('trafficChart').getContext('2d');

   new Chart(ctx,{
        type:'bar',
        data:{
            labels:['User1','User2','User3'],
            datasets:[{
                label:'Data Usage',
                data:[200,350,150],
                backgroundColor:'#89DCEB'
            }]
        }
    })
  </script>
 @endsection


