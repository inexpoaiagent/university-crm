@extends('layouts.portal')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Universities</h2>
    <p class="footer-note">Use professional filters and send your application request directly to admin.</p>

    <form method="GET" action="/portal/universities" class="toolbar" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-bottom:12px;">
        <input name="keywords" value="{{ $keywords }}" placeholder="Keywords">

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

        <div style="display:flex;gap:8px;grid-column:1/-1;">
            <button type="submit">Search</button>
            <a class="secondary" href="/portal/universities" style="text-decoration:none;padding:10px 14px;border-radius:10px;">Reset</a>
        </div>
    </form>

    <div class="grid-4">
        @foreach($universities as $u)
            <div class="card">
                <strong>{{ $u->name }}</strong>
                <p class="footer-note">{{ $u->country }}{{ $u->city ? ' / '.$u->city : '' }} | {{ ucfirst($u->institution_type ?: 'university') }} | {{ $u->currency }} | Match {{ $u->match_score }}%</p>
                <p><strong>Tuition:</strong> {{ $u->tuition_range ?: '-' }}</p>
                <p><strong>Deadline:</strong> {{ $u->deadline ?: '-' }}</p>
                <p class="footer-note">{{ \Illuminate\Support\Str::limit($u->programs_summary ?: $u->description, 120) }}</p>
                <form method="POST" action="/portal/universities/apply">
                    @csrf
                    <input type="hidden" name="university_id" value="{{ $u->id }}">
                    <input type="text" name="target_program" placeholder="Program (optional)" value="{{ $student->field_of_study }}">
                    <button type="submit" style="margin-top:8px;">Send Request to Admin</button>
                </form>
            </div>
        @endforeach
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

