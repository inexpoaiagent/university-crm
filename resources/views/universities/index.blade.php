@extends('layouts.app')

@section('content')
<div class="card">
    <div class="toolbar">
        <form method="GET" action="/universities" style="display:flex;gap:8px;">
            <input id="global-search" name="q" value="{{ $q }}" placeholder="Search by university/country">
            <button type="submit">Search</button>
        </form>
        <button onclick="document.getElementById('addUniversity').showModal()">+ Add University</button>
    </div>
    <div class="grid-4">
        @foreach($universities as $u)
            <div class="card">
                <h3 style="margin:0 0 6px;font-size:14px;color:var(--text)">{{ $u->name }}</h3>
                <div class="footer-note">{{ $u->country }} • {{ $u->currency }}</div>
                <p>{{ $u->tuition_range }}</p>
                <p class="footer-note">{{ \Illuminate\Support\Str::limit($u->description, 150) }}</p>
                <p class="footer-note">Applications: {{ $applicationCounts[$u->id] ?? 0 }}</p>
                <div style="display:flex;gap:6px;">
                    <a class="tab" href="/universities/{{ $u->id }}">More info</a>
                    <button type="button" class="secondary" onclick="document.getElementById('editUniversity{{ $u->id }}').showModal()">Edit</button>
                    <form method="POST" action="/universities/{{ $u->id }}" onsubmit="return confirm('Delete university?')">@csrf @method('DELETE')<button class="secondary">Delete</button></form>
                </div>
            </div>
        @endforeach
    </div>
    <div style="margin-top:10px;">{{ $universities->links() }}</div>
</div>

<dialog id="addUniversity" class="card" style="max-width:880px;">
    <h3 style="margin-top:0;">Add University</h3>
    <form method="POST" action="/universities">
        @csrf
        <div class="grid-4">
            <input name="name" placeholder="University name" required>
            <input name="country" placeholder="Country" required>
            <input name="website" placeholder="Website">
            <select name="currency"><option>USD</option><option>EUR</option><option>TRY</option></select>
            <input name="tuition_range" placeholder="Tuition range">
            <input name="language" placeholder="Language">
            <input name="deadline" type="date">
        </div>
        <textarea name="programs_summary" rows="4" style="width:100%;margin-top:8px;" placeholder="Programs"></textarea>
        <textarea name="visa_notes" rows="3" style="width:100%;margin-top:8px;" placeholder="Visa notes"></textarea>
        <textarea name="description" rows="4" style="width:100%;margin-top:8px;" placeholder="Full details"></textarea>
        <div style="margin-top:10px;display:flex;gap:8px;">
            <button type="submit">Save</button>
            <button type="button" class="secondary" onclick="document.getElementById('addUniversity').close()">Cancel</button>
        </div>
    </form>
</dialog>

@foreach($universities as $u)
<dialog id="editUniversity{{ $u->id }}" class="card" style="max-width:880px;">
    <h3 style="margin-top:0;">Edit University</h3>
    <form method="POST" action="/universities/{{ $u->id }}">
        @csrf
        @method('PUT')
        <div class="grid-4">
            <input name="name" value="{{ $u->name }}" required>
            <input name="country" value="{{ $u->country }}" required>
            <input name="website" value="{{ $u->website }}">
            <select name="currency"><option value="USD" {{ $u->currency === 'USD' ? 'selected' : '' }}>USD</option><option value="EUR" {{ $u->currency === 'EUR' ? 'selected' : '' }}>EUR</option><option value="TRY" {{ $u->currency === 'TRY' ? 'selected' : '' }}>TRY</option></select>
            <input name="tuition_range" value="{{ $u->tuition_range }}">
            <input name="language" value="{{ $u->language }}">
            <input name="deadline" type="date" value="{{ $u->deadline }}">
            <select name="is_active"><option value="1" {{ (int) $u->is_active === 1 ? 'selected' : '' }}>Active</option><option value="0" {{ (int) $u->is_active === 0 ? 'selected' : '' }}>Inactive</option></select>
        </div>
        <textarea name="programs_summary" rows="4" style="width:100%;margin-top:8px;">{{ $u->programs_summary }}</textarea>
        <textarea name="visa_notes" rows="3" style="width:100%;margin-top:8px;">{{ $u->visa_notes }}</textarea>
        <textarea name="description" rows="4" style="width:100%;margin-top:8px;">{{ $u->description }}</textarea>
        <div style="margin-top:10px;display:flex;gap:8px;">
            <button type="submit">Update</button>
            <button type="button" class="secondary" onclick="document.getElementById('editUniversity{{ $u->id }}').close()">Cancel</button>
        </div>
    </form>
</dialog>
@endforeach
@endsection
