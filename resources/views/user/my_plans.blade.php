@include('partials.header')
 @extends('user.layout')

@section('content')

<div style="max-width:900px;margin:40px auto;">
    <h2 style="text-align:center;">My Purchased Plans</h2>

    @foreach($transactions as $t)
        @php
            $expiry = \Carbon\Carbon::parse($t->created_at)->addMinutes($t->plan->duration_minutes);
            $isActive = now()->lt($expiry);
        @endphp

        <div style="background:#45475A;padding:20px;margin:15px 0;border-radius:10px;">
            <h3>{{ $t->plan->name }}</h3>
            <p>💰 Price: ₹{{ $t->amount }}</p>
            <p>⏱ Duration: {{ $t->plan->duration_minutes }} mins</p>

            <p>📅 Purchased: {{ $t->created_at->format('d M Y H:i') }}</p>
            <p>⏳ Expiry: {{ $expiry->format('d M Y H:i') }}</p>

            <p>
                Status:
                <span style="color: {{ $isActive ? '#A6E3A1' : '#f38ba8' }}">
                    {{ $isActive ? 'Active' : 'Expired' }}
                </span>
            </p>
        </div>

    @endforeach
    </div>

@endsection

<!DOCTYPE html>
<html>



<body style="background:#1E1E2E; color:#CDD6F4; font-family:Arial;">
    @if(session('error'))
    <div style="background:#f38ba8;color:#1E1E2E;padding:10px;text-align:center;">
        {{ session('error') }}
    </div>
    @endif

    <div style="max-width:900px;margin:40px auto;">
        <h2 style="text-align:center;">My Purchased Plans</h2>

        

    </div>

</body>

</html>