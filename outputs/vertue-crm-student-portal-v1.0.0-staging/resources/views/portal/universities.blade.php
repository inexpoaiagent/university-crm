@extends('layouts.portal')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Universities</h2>
    <div class="grid-4">
        @foreach($universities as $u)
            <div class="card">
                <strong>{{ $u->name }}</strong>
                <p class="footer-note">{{ $u->country }} | {{ $u->currency }}</p>
                <p><strong>Tuition:</strong> {{ $u->tuition_range ?: '-' }}</p>
                <p><strong>Deadline:</strong> {{ $u->deadline ?: '-' }}</p>
                <p class="footer-note">{{ \Illuminate\Support\Str::limit($u->programs_summary ?: $u->description, 120) }}</p>
                <form method="POST" action="/portal/universities/apply">
                    @csrf
                    <input type="hidden" name="university_id" value="{{ $u->id }}">
                    <input
                        type="text"
                        name="target_program"
                        placeholder="Program (optional)"
                        value="{{ old('target_program', $student->field_of_study ?? '') }}"
                    >
                    <button type="submit" style="margin-top:8px;">Send Request to Admin</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
