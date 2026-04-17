@extends('layouts.app')

@section('content')
<h1>Message Templates</h1>
<div class="card">
    <form method="POST" action="/templates">
        @csrf
        <select name="channel" required>
            <option value="email">Email</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="notification">Notification</option>
        </select>
        <input name="name" placeholder="Template name" required>
        <input name="subject" placeholder="Subject (optional)">
        <textarea name="body" rows="3" placeholder="Template body" required></textarea>
        <button type="submit">Add Template</button>
    </form>
</div>
<div class="card" style="margin-top:12px;">
    <table>
        <thead><tr><th>Channel</th><th>Name</th><th>Subject</th><th>Body</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($templates as $t)
            <tr>
                <td>{{ $t->channel }}</td>
                <td>{{ $t->name }}</td>
                <td>{{ $t->subject ?: '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($t->body, 70) }}</td>
                <td>
                    <form method="POST" action="/templates/{{ $t->id }}" onsubmit="return confirm('Delete template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

