@extends('layouts.app')

@section('content')
<h1>Advanced Reports</h1>
<div class="card">
    <form method="GET" action="/reports/advanced" class="grid-4">
        <input type="date" name="from" value="{{ $from }}">
        <input type="date" name="to" value="{{ $to }}">
        <select name="agent_id">
            <option value="">All Agents</option>
            @foreach($agents as $a)
                <option value="{{ $a->id }}" @selected((string)$agentId === (string)$a->id)>{{ $a->name }}</option>
            @endforeach
        </select>
        <input name="country" value="{{ $country }}" placeholder="Country">
        <button type="submit">Filter</button>
    </form>
    <p style="margin-top:8px;">
        <a href="/reports/advanced/export-csv?{{ http_build_query(request()->query()) }}">Export CSV</a> |
        <a href="/reports/advanced/export-excel?{{ http_build_query(request()->query()) }}">Export Excel</a> |
        <a href="/reports/advanced/export-pdf?{{ http_build_query(request()->query()) }}">Export PDF</a>
    </p>
</div>
<div class="card" style="margin-top:12px;">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Stage</th><th>Country</th><th>Agent</th><th>Sub-Agent</th></tr></thead>
        <tbody>
        @foreach($students as $s)
            <tr>
                <td>{{ $s->full_name }}</td>
                <td>{{ $s->email }}</td>
                <td>{{ $s->stage }}</td>
                <td>{{ $s->target_country }}</td>
                <td>{{ $s->agent?->name ?: '-' }}</td>
                <td>{{ $s->subAgent?->name ?: '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:8px;">{{ $students->links() }}</div>
</div>
@endsection
