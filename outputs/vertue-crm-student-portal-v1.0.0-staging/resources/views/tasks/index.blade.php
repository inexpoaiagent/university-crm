@extends('layouts.app')

@section('content')
<div class="card">
    <div class="toolbar">
        <form method="GET" action="/tasks" style="display:flex;gap:8px;flex-wrap:wrap;">
            <select name="status">
                <option value="">All Statuses</option>
                @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked'] as $key => $label)
                    <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="priority">
                <option value="">All Priorities</option>
                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $key => $label)
                    <option value="{{ $key }}" {{ $priority === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="assigned_to">
                <option value="">All Assignees</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $assignedTo === (string) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                @endforeach
            </select>
            <button type="submit">Filter</button>
        </form>
        <button onclick="document.getElementById('addTask').showModal()">+ New Task</button>
    </div>

    <table class="table-compact">
        <thead>
        <tr><th>Title</th><th>Student</th><th>Assigned To</th><th>Priority</th><th>Status</th><th>Deadline</th><th>Action</th></tr>
        </thead>
        <tbody>
        @forelse($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ optional($students->firstWhere('id', $task->student_id))->full_name ?: '-' }}</td>
                <td>{{ optional($agents->firstWhere('id', $task->assigned_to))->name ?: '#'.$task->assigned_to }}</td>
                <td>{{ ucfirst($task->priority) }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                <td>{{ $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('Y-m-d H:i') : '-' }}</td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    @if($task->status !== 'completed')
                        <form method="POST" action="/tasks/{{ $task->id }}/complete">
                            @csrf
                            <button type="submit">Complete</button>
                        </form>
                    @endif
                    <button type="button" class="secondary" onclick="document.getElementById('editTask{{ $task->id }}').showModal()">Edit</button>
                    <form method="POST" action="/tasks/{{ $task->id }}" onsubmit="return confirm('Delete task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="secondary">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No tasks found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:10px;">{{ $tasks->links() }}</div>
</div>

<dialog id="addTask" class="card" style="max-width:900px;">
    <h3 style="margin-top:0;">Create Task</h3>
    <form method="POST" action="/tasks">
        @csrf
        @include('tasks.partials.form', ['task' => null, 'students' => $students, 'agents' => $agents])
        <div style="margin-top:10px;display:flex;gap:8px;">
            <button type="submit">Save</button>
            <button type="button" class="secondary" onclick="document.getElementById('addTask').close()">Cancel</button>
        </div>
    </form>
</dialog>

@foreach($tasks as $task)
    <dialog id="editTask{{ $task->id }}" class="card" style="max-width:900px;">
        <h3 style="margin-top:0;">Edit Task</h3>
        <form method="POST" action="/tasks/{{ $task->id }}">
            @csrf
            @method('PUT')
            @include('tasks.partials.form', ['task' => $task, 'students' => $students, 'agents' => $agents])
            <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit">Update</button>
                <button type="button" class="secondary" onclick="document.getElementById('editTask{{ $task->id }}').close()">Cancel</button>
            </div>
        </form>
    </dialog>
@endforeach
@endsection

