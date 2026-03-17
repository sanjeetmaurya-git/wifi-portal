@extends('admin.layout')
@section('content')
 
<h2>Plans</h2>
<a href="/admin/plans/create">Create Plan</a>

<table border="1" width="100%">
    <tr>
        <th>Name</th>
        <th>Price</th>
        <th>Data</th>
        <th>Validity</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    @foreach($plans as $plan)
    <tr>
        <td>{{ $plan->name }}</td>
        <td>{{ $plan->price }}</td>
        <td>{{ $plan->data_limit_mb }} MB</td>
        <td>{{ $plan->validity_type }}</td>
        <td>{{ $plan->is_active ? 'Active' : 'Inactive' }}</td>
        <td>
    <a href="{{ url('/admin/plans/'.$plan->id.'/edit') }}" style="color:blue;">
        Edit
    </a>

    <form action="{{ url('/admin/plans/'.$plan->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')

        <button type="submit" style="color:red;"
            onclick="return confirm('Delete this plan?')">
            Delete
        </button>
    </form>
</td>
    </tr>
    @endforeach
</table>

@endsection