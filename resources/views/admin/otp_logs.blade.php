@extends('admin.layout')
@section('content')

<h1>OTP Logs</h1>

<div class="card">
    <table width="100%">
        <thead>
            <tr>
                <th>Mobile</th>
                <th>OTP Code</th>
                <th>IP Address</th>
                <th>Created At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr>
                <td>{{ $log->mobile }}</td>
                <td>{{ $log->otp_code }}</td>
                <td>{{ $log->ip_address }}</td>
                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>
                    @if($log->expires_at > now())
                        <span class="success">Active</span>
                    @else
                        <span class="danger">Expired</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $logs->links() }}
    </div>
</div>

@endsection
