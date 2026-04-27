@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Professional Program Search</h2>
    <form method="GET" action="/search" class="toolbar" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;">
        <input id="global-search" name="q" value="{{ $keywords ?? $q }}" placeholder="Keywords">

        <select id="country_university" name="country_university">
            <option value="">University country</option>
            @foreach($countryOptions as $co)
                <option value="{{ $co }}" {{ $countryUniversity === $co ? 'selected' : '' }}>{{ $co }}</option>
            @endforeach
        </select>

        <select id="city_university" name="city_university" data-selected="{{ $cityUniversity }}">
            <option value="">University city</option>
        </select>

        <select name="university_type">
            <option value="">Type (University / School)</option>
            <option value="university" {{ $universityType === 'university' ? 'selected' : '' }}>University</option>
            <option value="school" {{ $universityType === 'school' ? 'selected' : '' }}>School</option>
        </select>

        <input name="university_name" value="{{ $universityName }}" placeholder="University name">

        <select name="degree">
            <option value="">Degree</option>
            @foreach($degreeOptions as $degreeOption)
                <option value="{{ $degreeOption }}" {{ $degree === $degreeOption ? 'selected' : '' }}>{{ $degreeOption }}</option>
            @endforeach
        </select>

        <select name="study_field">
            <option value="">Study field</option>
            @foreach($studyFieldOptions as $fieldOption)
                <option value="{{ $fieldOption }}" {{ $studyField === $fieldOption ? 'selected' : '' }}>{{ $fieldOption }}</option>
            @endforeach
        </select>

        <select name="stage">
            <option value="">Student stage</option>
            @foreach(['lead','applied','offered','accepted','enrolled'] as $st)
                <option value="{{ $st }}" {{ $stage === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>

        <input name="country" value="{{ $country }}" placeholder="Student target country">

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

<script>
(() => {
    const cityByCountry = @json($citiesByCountry);
    const countrySelect = document.getElementById('country_university');
    const citySelect = document.getElementById('city_university');
    if (!countrySelect || !citySelect) return;

    const refillCities = () => {
        const selectedCountry = countrySelect.value;
        const selectedCity = citySelect.dataset.selected || '';
        const cities = cityByCountry[selectedCountry] || [];

        citySelect.innerHTML = '<option value="">University city</option>';
        cities.forEach((city) => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            if (city === selectedCity) option.selected = true;
            citySelect.appendChild(option);
        });
    };

    countrySelect.addEventListener('change', () => {
        citySelect.dataset.selected = '';
        refillCities();
    });

    refillCities();
})();
</script>
@endsection

