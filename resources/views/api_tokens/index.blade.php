@extends('layouts.app')

@section('content')
<h1>API Tokens</h1>
<div class="card">
    <form method="POST" action="/api-tokens">
        @csrf
        <input name="name" placeholder="Token name" required>
        <button type="submit">Create Token</button>
    </form>
</div>
<div class="card" style="margin-top:12px;">
    <table>
        <thead><tr><th>Name</th><th>Last Used</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @foreach($tokens as $token)
            <tr>
                <td>{{ $token->name }}</td>
                <td>{{ $token->last_used_at ?: '-' }}</td>
                <td>{{ (int)$token->is_active === 1 ? 'Active' : 'Inactive' }}</td>
                <td>
                    <form method="POST" action="/api-tokens/{{ $token->id }}" onsubmit="return confirm('Revoke token?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Revoke</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

