@extends('admin.layout')
@section('content')

<style>
    .card .card-heading{
        font-size: 28px;
        text-align: center;
        padding: 16px;
        text-decoration: underline 2px skyblue;
    }
    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    .badge-success { background-color: #28a745; color: white; }
    .badge-warning { background-color: #ffc107; color: black; }
    .badge-danger { background-color: #dc3545; color: white; }
</style>

<h1>Transaction Logs</h1>

<div class="card">
    <h3 class="card-heading">All Transactions</h3>
    <table width="100%" id="transactionsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>User (Mobile)</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Order ID</th>
                <th>Payment ID</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trans)
            <tr>
                <td>{{ $trans->id }}</td>
                <td>{{ $trans->user->mobile ?? 'Unknown' }}</td>
                <td>{{ $trans->plan->name ?? 'N/A' }}</td>
                <td>₹{{ $trans->amount }}</td>
                <td>{{ $trans->order_id }}</td>
                <td>{{ $trans->payment_id ?? '-' }}</td>
                <td>
                    <span class="badge {{ $trans->status == 'paid' ? 'badge-success' : ($trans->status == 'created' ? 'badge-warning' : 'badge-danger') }}">
                        {{ strtoupper($trans->status) }}
                    </span>
                </td>
                <td>{{ $trans->created_at->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $transactions->links() }}
    </div>
</div>

@endsection
