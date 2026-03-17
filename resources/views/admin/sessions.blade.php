@extends('admin.layout')
@section('content')

<h1>Sessions</h1>
<table width="100%">
    <thead>
        <th>User</th>
        <th>IP</th>
        <th>MAC</th>
        <th>Login Time</th>
    </thead>
    <tbody>
        @foreach ($sessions  as $s )
        <tr style="text-align: center;">
            <td>{{ $s->user_id }}</td>
            <td>{{ $s->ip_address }}</td>
            <td>{{ $s->mac_address }}</td>
            <td>{{ $s->login_at }}</td>
        </tr>
            
        @endforeach
    </tbody>
</table>

@endsection