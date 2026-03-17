@extends('admin.layout')

@section('content')

<h2>Edit Plan</h2>

<form method="POST" action="{{ url('/admin/plans/'.$plan->id) }}">
    @csrf
    @method('PUT')

    <input type="text" name="name" value="{{ $plan->name }}"><br>

    <input type="number" name="price" value="{{ $plan->price }}"><br>

    <input type="number" name="data_limit_mb" value="{{ $plan->data_limit_mb }}"><br>

    <select name="validity_type">
        <option value="daily" {{ $plan->validity_type=='daily'?'selected':'' }}>Daily</option>
        <option value="weekly" {{ $plan->validity_type=='weekly'?'selected':'' }}>Weekly</option>
    </select><br>

    <input type="number" name="duration_minutes" value="{{ $plan->duration_minutes }}"><br>

    <button type="submit">Update Plan</button>
</form>

@endsection