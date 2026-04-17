@extends('layouts.app')

@php
    $tab = request()->query('tab', 'overview');
@endphp

@section('content')
<div class="card">
    <h2 style="margin-top:0;">{{ $student->full_name }}</h2>
    <div class="tabs">
        <a class="tab {{ $tab === 'overview' ? 'active' : '' }}" href="/students/{{ $student->id }}?tab=overview">Overview</a>
        <a class="tab {{ $tab === 'apps' ? 'active' : '' }}" href="/students/{{ $student->id }}?tab=apps">Apps ({{ $apps->count() }})</a>
        <a class="tab {{ $tab === 'docs' ? 'active' : '' }}" href="/students/{{ $student->id }}?tab=docs">Docs ({{ $docs->count() }})</a>
        <a class="tab {{ $tab === 'tasks' ? 'active' : '' }}" href="/students/{{ $student->id }}?tab=tasks">Tasks ({{ $tasks->count() }})</a>
        <a class="tab {{ $tab === 'timeline' ? 'active' : '' }}" href="/students/{{ $student->id }}?tab=timeline">Timeline</a>
    </div>

    @if($tab === 'overview')
    <div class="two-col">
        <div class="card">
            <h3>Academic Profile</h3>
            <p><strong>Email:</strong> {{ $student->email }}</p>
            <p><strong>Phone:</strong> {{ $student->phone ?: '-' }}</p>
            <p><strong>GPA:</strong> {{ $student->gpa ?: '-' }}</p>
            <p><strong>English Level:</strong> {{ $student->english_level ?: '-' }}</p>
            <p><strong>Field of Study:</strong> {{ $student->field_of_study ?: '-' }}</p>
            <p><strong>Target Country:</strong> {{ $student->target_country ?: '-' }}</p>
            <p><strong>Budget (USD):</strong> {{ $student->budget_usd ?: '-' }}</p>
            <p><strong>Passport #:</strong> {{ $student->passport_number ?: '-' }}</p>
        </div>
        <div class="card">
            <h3>Case Health</h3>
            <p><strong>Current Stage:</strong> <span class="badge {{ $student->stage }}">{{ ucfirst($student->stage) }}</span></p>
            <p><strong>Temperature:</strong> <span class="{{ $student->stage_temperature }}">{{ ucfirst($student->stage_temperature ?: 'cold') }}</span></p>
            <p><strong>Applications:</strong> {{ $apps->count() }}</p>
            <p><strong>Documents:</strong> {{ $docs->count() }}</p>
            <p><strong>Open Tasks:</strong> {{ $tasks->whereIn('status', ['todo', 'in_progress'])->count() }}</p>
        </div>
    </div>
    @elseif($tab === 'apps')
    <table class="table-compact">
        <thead><tr><th>ID</th><th>Program</th><th>Status</th><th>Intake</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($apps as $app)
                <tr>
                    <td>#{{ $app->id }}</td>
                    <td>{{ $app->program }}</td>
                    <td><span class="badge {{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                    <td>{{ $app->intake }}</td>
                    <td><a class="tab" href="/applications/{{ $app->id }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="5">No applications for this student yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    @elseif($tab === 'docs')
    @php
        $typeLabels = [
            'passport' => 'Passport',
            'diploma' => 'Diploma',
            'transcript' => 'Transcript',
            'english_certificate' => 'English Certificate',
            'photo' => 'Photo',
            'other_documents' => 'Other Documents',
            'payment_receipt' => 'Payment Receipt',
        ];
    @endphp
    <form method="POST" action="/students/{{ $student->id }}/documents" enctype="multipart/form-data" class="toolbar" style="margin-bottom:10px;">
        @csrf
        <select name="type" required>
            <option value="">Document type</option>
            <option value="passport">Passport</option>
            <option value="diploma">Diploma</option>
            <option value="transcript">Transcript</option>
            <option value="english_certificate">English Certificate</option>
            <option value="photo">Photo</option>
            <option value="other_documents">Other Documents</option>
            <option value="payment_receipt">Payment Receipt</option>
        </select>
        <input type="date" name="expiry_date" placeholder="Expiry date">
        <input type="file" name="file" required>
        <button type="submit">Upload Document</button>
    </form>
    <table class="table-compact">
        <thead><tr><th>Type</th><th>Status</th><th>File</th><th>Expiry</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($docs as $doc)
                <tr>
                    <td>{{ $typeLabels[$doc->type] ?? $doc->type }}</td>
                    <td>{{ ucfirst($doc->status) }}</td>
                    <td>
                        @if($doc->file_url)
                            <a href="{{ $doc->file_url }}" target="_blank">{{ $doc->file_name ?: 'Open file' }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $doc->expiry_date ?: '-' }}</td>
                    <td style="display:flex;gap:6px;">
                        @if($doc->status !== 'verified')
                            <form method="POST" action="/students/{{ $student->id }}/documents/{{ $doc->id }}/verify">
                                @csrf
                                <button type="submit" class="secondary">Verify</button>
                            </form>
                        @endif
                        <form method="POST" action="/students/{{ $student->id }}/documents/{{ $doc->id }}" onsubmit="return confirm('Delete this document?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="secondary">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No documents found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @elseif($tab === 'tasks')
    <table class="table-compact">
        <thead><tr><th>Title</th><th>Priority</th><th>Status</th><th>Deadline</th></tr></thead>
        <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ ucfirst($task->priority) }}</td>
                    <td>{{ ucfirst($task->status) }}</td>
                    <td>{{ $task->deadline ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No tasks assigned.</td></tr>
            @endforelse
        </tbody>
    </table>
    @else
    <div class="card">
        <h3>Timeline</h3>
        <p class="footer-note">Student profile created at {{ $student->created_at }}.</p>
        <p class="footer-note">Last update at {{ $student->updated_at }}.</p>
    </div>
    @endif
</div>
@endsection
