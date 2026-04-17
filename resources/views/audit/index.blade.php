@extends('layouts.app')

@section('content')
<h1>Global Audit Screen</h1>
<div class="card">
    <table>
        <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Entity ID</th><th>IP</th></tr></thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>#{{ $log->user_id }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->entity_type }}</td>
                <td>{{ $log->entity_id }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:8px;">{{ $logs->links() }}</div>
</div>
@endsection

