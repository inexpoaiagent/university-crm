@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Agent Performance</h2>
    <p class="footer-note">Track how many students each Agent/Sub-Agent has sourced, including stage distribution and conversion.</p>
</div>

<div class="card" style="margin-top:12px;">
    <h3>Agents</h3>
    <table class="table-compact">
        <thead><tr><th>Agent</th><th>Email</th><th>Total Students</th><th>Lead</th><th>Applied</th><th>Accepted</th><th>Enrolled</th><th>Conversion</th></tr></thead>
        <tbody>
        @forelse($agents as $agent)
            @php
                $total = (int) ($agentStats[$agent->id] ?? 0);
                $rows = $agentStageStats[$agent->id] ?? collect();
                $lead = (int) optional($rows->firstWhere('stage', 'lead'))->total;
                $applied = (int) optional($rows->firstWhere('stage', 'applied'))->total;
                $accepted = (int) optional($rows->firstWhere('stage', 'accepted'))->total;
                $enrolled = (int) optional($rows->firstWhere('stage', 'enrolled'))->total;
                $conversion = $total > 0 ? (int) round(($enrolled / $total) * 100) : 0;
            @endphp
            <tr>
                <td><a href="/agents/{{ $agent->id }}">{{ $agent->name }}</a></td>
                <td>{{ $agent->email }}</td>
                <td><strong>{{ $total }}</strong></td>
                <td>{{ $lead }}</td>
                <td>{{ $applied }}</td>
                <td>{{ $accepted }}</td>
                <td>{{ $enrolled }}</td>
                <td>{{ $conversion }}%</td>
            </tr>
        @empty
            <tr><td colspan="8">No agents found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:12px;">
    <h3>Sub-Agents</h3>
    <table class="table-compact">
        <thead><tr><th>Sub-Agent</th><th>Parent Agent</th><th>Total Students</th><th>Lead</th><th>Applied</th><th>Accepted</th><th>Enrolled</th><th>Conversion</th></tr></thead>
        <tbody>
        @forelse($subAgents as $sub)
            @php
                $total = (int) ($subAgentStats[$sub->id] ?? 0);
                $rows = $subAgentStageStats[$sub->id] ?? collect();
                $lead = (int) optional($rows->firstWhere('stage', 'lead'))->total;
                $applied = (int) optional($rows->firstWhere('stage', 'applied'))->total;
                $accepted = (int) optional($rows->firstWhere('stage', 'accepted'))->total;
                $enrolled = (int) optional($rows->firstWhere('stage', 'enrolled'))->total;
                $conversion = $total > 0 ? (int) round(($enrolled / $total) * 100) : 0;
                $parent = $agents->firstWhere('id', $sub->parent_user_id);
            @endphp
            <tr>
                <td><a href="/agents/{{ $sub->id }}">{{ $sub->name }}</a></td>
                <td>{{ $parent?->name ?: '-' }}</td>
                <td><strong>{{ $total }}</strong></td>
                <td>{{ $lead }}</td>
                <td>{{ $applied }}</td>
                <td>{{ $accepted }}</td>
                <td>{{ $enrolled }}</td>
                <td>{{ $conversion }}%</td>
            </tr>
        @empty
            <tr><td colspan="8">No sub-agents found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
