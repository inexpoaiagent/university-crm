<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrmSlaCheckCommand extends Command
{
    protected $signature = 'crm:sla-check';
    protected $description = 'Run SLA alert automation rules';

    public function handle(): int
    {
        $rules = DB::table('automation_rules')->where('is_active', 1)->get();
        foreach ($rules as $rule) {
            if ($rule->trigger_key !== 'sla_overdue_tasks') {
                continue;
            }
            $overdueTasks = Task::query()
                ->where('tenant_id', $rule->tenant_id)
                ->whereIn('status', ['todo', 'in_progress'])
                ->whereNotNull('deadline')
                ->where('deadline', '<', now())
                ->get();
            foreach ($overdueTasks as $task) {
                Notification::query()->create([
                    'tenant_id' => $rule->tenant_id,
                    'user_id' => $task->assigned_to,
                    'type' => 'sla_alert',
                    'title' => 'SLA alert: overdue task',
                    'body' => 'Task "'.$task->title.'" is overdue.',
                    'meta_json' => json_encode(['task_id' => $task->id], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
        $this->info('SLA check finished.');
        return self::SUCCESS;
    }
}

