<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        $status = (string) $request->query('status', '');
        $priority = (string) $request->query('priority', '');
        $assignedTo = (string) $request->query('assigned_to', '');

        $tasks = Task::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($priority !== '', fn ($query) => $query->where('priority', $priority))
            ->when($assignedTo !== '', fn ($query) => $query->where('assigned_to', (int) $assignedTo))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $students = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $agents = User::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->whereIn('role_slug', ['admin', 'agent', 'sub_agent'])
            ->orderBy('name')
            ->get(['id', 'name', 'role_slug']);

        return view('tasks.index', compact('tasks', 'students', 'agents', 'status', 'priority', 'assignedTo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'required|integer|exists:users,id',
            'student_id' => 'nullable|integer|exists:students,id',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,completed,blocked',
        ]);
        $data['tenant_id'] = $auth->tenant_id;
        $data['escalation_level'] = 0;

        $task = Task::query()->create($data);
        $this->audit($request, 'task.create', 'task', $task->id, $data);

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        $task = Task::query()->forTenant($auth->tenant_id, $auth->role_slug)->findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'required|integer|exists:users,id',
            'student_id' => 'nullable|integer|exists:students,id',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,completed,blocked',
        ]);
        $task->update($data);
        $this->audit($request, 'task.update', 'task', $task->id, $data);

        return back()->with('success', 'Task updated.');
    }

    public function markComplete(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        $task = Task::query()->forTenant($auth->tenant_id, $auth->role_slug)->findOrFail($id);
        $task->update(['status' => 'completed']);
        $this->audit($request, 'task.complete', 'task', $task->id, ['status' => 'completed']);

        return back()->with('success', 'Task marked as complete.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        $task = Task::query()->forTenant($auth->tenant_id, $auth->role_slug)->findOrFail($id);
        $task->delete();
        $this->audit($request, 'task.delete', 'task', $id);

        return back()->with('success', 'Task deleted.');
    }
}

