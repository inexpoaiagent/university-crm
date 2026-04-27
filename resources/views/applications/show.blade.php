@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
        <div>
            <h2 style="margin-top:0;">Application #{{ $application->id }}</h2>
            <p><strong>Status:</strong> <span class="badge {{ $application->status }}">{{ ucfirst($application->status) }}</span></p>
        </div>
        <a class="tab" href="/applications">Back to list</a>
    </div>

    <div class="two-col">
        <div class="card">
            <h3>Case Details</h3>
            <div class="app-case-table">
                <div class="app-case-item">
                    <div class="app-case-name">Student</div>
                    <div class="app-case-data">{{ $student?->full_name ?: ('#'.$application->student_id) }}</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">University</div>
                    <div class="app-case-data">{{ $university?->name ?: ('#'.$application->university_id) }}</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">Program</div>
                    <div class="app-case-data">{{ $application->program }}</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">Intake</div>
                    <div class="app-case-data">{{ $application->intake }}</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">Deadline</div>
                    <div class="app-case-data">{{ $application->deadline ?: '-' }}</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">Enroll Probability</div>
                    <div class="app-case-data">{{ $application->enroll_probability }}%</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">Explainability</div>
                    <div class="app-case-data">{{ $application->explainability ?: '-' }}</div>
                </div>
                <div class="app-case-item">
                    <div class="app-case-name">Best Next Action</div>
                    <div class="app-case-data">{{ $application->best_next_action ?: '-' }}</div>
                </div>
            </div>
            <h3>Notes</h3>
            <pre style="white-space:pre-wrap;font-family:inherit;">{{ $application->notes ?: '-' }}</pre>
        </div>

        <div class="card">
            <h3>Edit Application</h3>
            <form method="POST" action="/applications/{{ $application->id }}">
                @csrf
                @method('PUT')
                <div class="grid-4">
                    <select name="student_id" required>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ $application->student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                    <select name="university_id" required>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $application->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <input name="program" value="{{ $application->program }}" required>
                    <input name="intake" value="{{ $application->intake }}" required>
                    <select name="status">
                        @foreach(['draft','submitted','under_review','accepted','rejected','enrolled'] as $s)
                            <option value="{{ $s }}" {{ $application->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <input name="deadline" type="date" value="{{ $application->deadline }}">
                </div>
                <textarea name="notes" rows="5" style="width:100%;margin-top:8px;">{{ $application->notes }}</textarea>
                <div style="margin-top:10px;display:flex;gap:8px;">
                    <button type="submit">Save Changes</button>
                </div>
            </form>
            <form method="POST" action="/applications/{{ $application->id }}" onsubmit="return confirm('Delete application?')" style="margin-top:8px;">
                @csrf
                @method('DELETE')
                <button class="secondary" type="submit">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
