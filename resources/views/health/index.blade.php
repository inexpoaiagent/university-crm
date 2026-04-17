@extends('layouts.app')

@section('content')
<h1>Health & Backup</h1>
<div class="grid-4">
    <div class="card"><h3>Database</h3><div class="metric">{{ $dbOk ? 'OK' : 'FAIL' }}</div></div>
    <div class="card"><h3>Storage</h3><div class="metric">{{ $storageOk ? 'OK' : 'FAIL' }}</div></div>
    <div class="card"><h3>Environment</h3><div class="metric">{{ $appEnv }}</div></div>
    <div class="card"><h3>Debug</h3><div class="metric">{{ $appDebug ? 'ON' : 'OFF' }}</div></div>
</div>

<div class="card" style="margin-top:12px;">
    <a href="/health/backup">Download Tenant Backup (JSON)</a>
</div>
<div class="card" style="margin-top:12px;">
    <form method="POST" action="/health/restore">
        @csrf
        <textarea name="backup_json" rows="8" placeholder="Paste backup JSON here"></textarea>
        <button type="submit">Restore Backup</button>
    </form>
</div>
@endsection

