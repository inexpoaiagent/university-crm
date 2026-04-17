<div class="grid-4">
    <input type="text" name="title" placeholder="Title" value="{{ old('title', $task?->title) }}" required>
    <select name="assigned_to" required>
        <option value="">Assign to</option>
        @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ (string) old('assigned_to', $task?->assigned_to) === (string) $agent->id ? 'selected' : '' }}>
                {{ $agent->name }} ({{ $agent->role_slug }})
            </option>
        @endforeach
    </select>
    <select name="student_id">
        <option value="">No student (general)</option>
        @foreach($students as $studentRow)
            <option value="{{ $studentRow->id }}" {{ (string) old('student_id', $task?->student_id) === (string) $studentRow->id ? 'selected' : '' }}>
                {{ $studentRow->full_name }}
            </option>
        @endforeach
    </select>
    <input type="datetime-local" name="deadline" value="{{ old('deadline', $task?->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('Y-m-d\TH:i') : '') }}">

    <select name="priority" required>
        @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $key => $label)
            <option value="{{ $key }}" {{ old('priority', $task?->priority ?? 'medium') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <select name="status" required>
        @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked'] as $key => $label)
            <option value="{{ $key }}" {{ old('status', $task?->status ?? 'todo') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<textarea name="description" rows="4" style="width:100%;margin-top:8px;" placeholder="Description">{{ old('description', $task?->description) }}</textarea>

