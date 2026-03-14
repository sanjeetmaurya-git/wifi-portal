@extends('admin.layout')

@section('content')

<h1>System Logs</h1>

<table width="100%">

    <tr>
        <th>User</th>
        <th>IP</th>
        <th>Login</th>
        <th>Logout</th>
    </tr>

    @foreach($sessions as $s)

    <tr>

        <td>{{ $s->user->mobile }}</td>
        <td>{{ $s->ip_address }}</td>
        <td>{{ $s->login_at }}</td>
        <td>{{ $s->logout_at }}</td>
    </tr>

    @endforeach
    

</table>

@endsection