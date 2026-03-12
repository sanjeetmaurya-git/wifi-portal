@extends('admin.layout')

@section('content')

<h1>WiFi Users</h1>

<table width="100%">
    <thead>
        <th class="users-info">ID</th>
        <th class="users-info">Mobile</th>
        <th class="users-info">Created</th>
    </thead>
    <tbody class="table-uses-info">
        @foreach($users as $user)

        <tr class="tb-row">
            <td class="users-info">{{ $user->id }}</td>
            <td class="users-info">{{ $user->mobile }}</td>
            <td class="users-info">{{ $user->created_at }}</td>
        </tr>
            
        @endforeach
    </tbody>
</table>
@endsection
<style>
   thead{
    background: #006791;
   }
    .users-info{
        text-align: center;
        padding: 6px;
    }
    .tb-row:hover{
        background: #767272;

    }
</style>