@extends('admin.layout')

@section('content')

<h2>Edit Plan</h2>

<form method="POST" action="{{ url('/admin/plans/'.$plan->id) }}">
    @csrf
    @method('PUT')

    <input type="text" name="name" value="{{ $plan->name }}"><br>

    <input type="number" name="price" value="{{ $plan->price }}" placeholder="Price"><br>
    
    <label><input type="checkbox" name="is_free" value="1" {{ $plan->is_free?'checked':'' }}> Is One-Time Free Plan?</label><br>

    <input type="number" name="limit_bytes" value="{{ $plan->limit_bytes }}" placeholder="Data Limit (MB)"><br>

    <select name="validity_type">
        <option value="daily" {{ $plan->validity_type=='daily'?'selected':'' }}>Daily</option>
        <option value="weekly" {{ $plan->validity_type=='weekly'?'selected':'' }}>Weekly</option>
    </select><br>

    <input type="number" name="duration_minutes" value="{{ $plan->duration_minutes }}"><br>
    
    <input type="text" name="profile_name" value="{{ $plan->profile_name }}" placeholder="MikroTik Profile"><br>
    
    <input type="number" name="limit_bytes" value="{{ $plan->limit_bytes }}" placeholder="Limit (MB)"><br>

    <input type="text" name="upload_limit" value="{{ $plan->upload_limit }}" placeholder="Upload Speed (e.g. 2M)"><br>

    <input type="text" name="download_limit" value="{{ $plan->download_limit }}" placeholder="Download Speed (e.g. 5M)"><br>

    <input type="text" name="upload_limit" value="{{ $plan->upload_limit }}" placeholder="Upload Limit (e.g. 2M)"><br>

    <input type="text" name="download_limit" value="{{ $plan->download_limit }}" placeholder="Download Limit (e.g. 5M)"><br>

    <button type="submit">Update Plan</button>
</form>

@endsection