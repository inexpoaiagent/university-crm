<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgentPerformanceController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);

        $agents = User::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->where('role_slug', 'agent')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $subAgents = User::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->where('role_slug', 'sub_agent')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'parent_user_id']);

        $agentStats = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->select('agent_id', DB::raw('count(*) as total'))
            ->groupBy('agent_id')
            ->pluck('total', 'agent_id');

        $subAgentStats = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->select('sub_agent_id', DB::raw('count(*) as total'))
            ->groupBy('sub_agent_id')
            ->pluck('total', 'sub_agent_id');

        $agentStageStats = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->select('agent_id', 'stage', DB::raw('count(*) as total'))
            ->groupBy('agent_id', 'stage')
            ->get()
            ->groupBy('agent_id');

        $subAgentStageStats = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->select('sub_agent_id', 'stage', DB::raw('count(*) as total'))
            ->groupBy('sub_agent_id', 'stage')
            ->get()
            ->groupBy('sub_agent_id');

        return view('reports.agent-performance', compact(
            'agents',
            'subAgents',
            'agentStats',
            'subAgentStats',
            'agentStageStats',
            'subAgentStageStats'
        ));
    }
}
