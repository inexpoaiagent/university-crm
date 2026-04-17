<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AutomationRuleController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        $rules = DB::table('automation_rules')
            ->where('tenant_id', $auth->tenant_id)
            ->orderByDesc('id')
            ->get();

        return view('automation.index', compact('rules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'trigger_key' => 'required|string|in:sla_overdue_tasks,daily_followup',
            'is_active' => 'nullable|boolean',
        ]);

        DB::table('automation_rules')->insert([
            'tenant_id' => $auth->tenant_id,
            'name' => $data['name'],
            'trigger_key' => $data['trigger_key'],
            'conditions_json' => json_encode([], JSON_UNESCAPED_UNICODE),
            'actions_json' => json_encode([], JSON_UNESCAPED_UNICODE),
            'is_active' => (int) ($data['is_active'] ?? 1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Automation rule created.');
    }

    public function run(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        $rules = DB::table('automation_rules')
            ->where('tenant_id', $auth->tenant_id)
            ->where('is_active', 1)
            ->get();

        foreach ($rules as $rule) {
            if ($rule->trigger_key === 'sla_overdue_tasks') {
                $overdue = Task::query()
                    ->forTenant($auth->tenant_id, $auth->role_slug)
                    ->whereIn('status', ['todo', 'in_progress'])
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now())
                    ->get();
                foreach ($overdue as $task) {
                    Notification::query()->create([
                        'tenant_id' => $auth->tenant_id,
                        'user_id' => $task->assigned_to,
                        'type' => 'sla_alert',
                        'title' => 'SLA alert: overdue task',
                        'body' => 'Task "'.$task->title.'" is overdue.',
                        'meta_json' => json_encode(['task_id' => $task->id], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        }

        return back()->with('success', 'Automation rules executed.');
    }
}
