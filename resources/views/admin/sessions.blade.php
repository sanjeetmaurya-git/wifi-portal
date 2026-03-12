@extends('admin.layout')
@section('content')

<h1>Sessions</h1>
<table width="100%">
    <th>
        <th>User</th>
        <th>IP</th>
        <th>MAC</th>
        <th>Login Time</th>
    </th>
    <tbody>
        @foreach ($sessions  as $s )
        <tr>
            <td>{{ $s->user_id }}</td>
            <td>{{ $s->ip_address }}</td>
            <td>{{ $s->mac_address }}</td>
            <td>{{ $s->login_at }}</td>
        </tr>
            
        @endforeach
    </tbody>
</table>

@endsection