@extends('layouts.portal')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">My Applications</h2>
    <table>
        <thead><tr><th>Program</th><th>Status</th><th>Deadline</th><th>Progress</th></tr></thead>
        <tbody>
        @forelse($applications as $a)
            <tr>
                <td>{{ $a->program }}</td>
                <td>{{ ucfirst($a->status) }}</td>
                <td>{{ $a->deadline ?: '-' }}</td>
                <td>
                    @php
                        $p = match($a->status) {
                            'draft' => 15,
                            'submitted' => 40,
                            'under_review' => 65,
                            'accepted' => 85,
                            'enrolled' => 100,
                            default => 20,
                        };
                    @endphp
                    <div style="height:8px;background:#dbeafe;border-radius:999px;overflow:hidden;">
                        <div style="height:8px;width:{{ $p }}%;background:#0284c7;"></div>
                    </div>
                    <span class="footer-note">{{ $p }}%</span>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">No applications yet. Submit a university request from Universities page.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
