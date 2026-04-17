@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Professional Search</h2>
    <form method="GET" action="/search" class="toolbar" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;">
        <input id="global-search" name="q" value="{{ $keywords ?? $q }}" placeholder="Keywords">
        <input name="country_university" value="{{ $countryUniversity }}" placeholder="University country">
        <input name="city_university" value="{{ $cityUniversity }}" placeholder="University city">
        <select name="university_type">
            <option value="">Type (University / School)</option>
            <option value="university" {{ $universityType === 'university' ? 'selected' : '' }}>University</option>
            <option value="school" {{ $universityType === 'school' ? 'selected' : '' }}>School</option>
        </select>
        <input name="university_name" value="{{ $universityName }}" placeholder="University name">
        <input name="degree" value="{{ $degree }}" placeholder="Degree (Bachelor, Master...)">
        <input name="study_field" value="{{ $studyField }}" placeholder="Study field">
        <select name="stage">
            <option value="">Student stage</option>
            @foreach(['lead','applied','offered','accepted','enrolled'] as $st)
                <option value="{{ $st }}" {{ $stage === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <input name="country" value="{{ $country }}" placeholder="Target country">
        <select name="status">
            <option value="">Application status</option>
            @foreach(['draft','submitted','under_review','accepted','rejected','enrolled'] as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <div style="display:flex;gap:8px;grid-column:1/-1;">
            <button type="submit">Search</button>
            <a class="secondary" href="/search" style="text-decoration:none;padding:10px 14px;border-radius:10px;">Reset</a>
        </div>
    </form>
</div>

<div class="three-col" style="margin-top:12px;">
    <div class="card">
        <h3>Students ({{ $students->count() }})</h3>
        @forelse($students as $s)
            <p><a href="/students/{{ $s->id }}">{{ $s->full_name }}</a><br><span class="footer-note">{{ $s->email }}</span></p>
        @empty
            <p class="footer-note">No results.</p>
        @endforelse
    </div>
    <div class="card">
        <h3>Applications ({{ $applications->count() }})</h3>
        @forelse($applications as $a)
            <p><a href="/applications/{{ $a->id }}">#{{ $a->id }} - {{ $a->program }}</a><br><span class="footer-note">{{ ucfirst($a->status) }}</span></p>
        @empty
            <p class="footer-note">No results.</p>
        @endforelse
    </div>
    <div class="card">
        <h3>Universities ({{ $universities->count() }})</h3>
        @forelse($universities as $u)
            <p>
                <a href="/universities/{{ $u->id }}">{{ $u->name }}</a><br>
                <span class="footer-note">{{ $u->country }}</span><br>
                <span class="footer-note">{{ \Illuminate\Support\Str::limit($u->programs_summary ?: $u->description, 90) }}</span>
            </p>
        @empty
            <p class="footer-note">No results.</p>
        @endforelse
    </div>
</div>
@endsection
