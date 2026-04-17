@extends('layouts.app')

@section('content')
<h1>Automation Rules</h1>
<div class="card">
    <form method="POST" action="/automation-rules">
        @csrf
        <input name="name" placeholder="Rule name" required>
        <select name="trigger_key" required>
            <option value="sla_overdue_tasks">SLA Overdue Tasks</option>
            <option value="daily_followup">Daily Follow-up</option>
        </select>
        <button type="submit">Create Rule</button>
    </form>
    <form method="POST" action="/automation-rules/run" style="margin-top:10px;">
        @csrf
        <button type="submit">Run Automations Now</button>
    </form>
</div>

<div class="card" style="margin-top:12px;">
    <table>
        <thead><tr><th>Name</th><th>Trigger</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($rules as $r)
            <tr>
                <td>{{ $r->name }}</td>
                <td>{{ $r->trigger_key }}</td>
                <td>{{ (int) $r->is_active === 1 ? 'Active' : 'Inactive' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

