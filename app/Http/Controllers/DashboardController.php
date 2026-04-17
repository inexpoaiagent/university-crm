<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authUser($request);

        $students = Student::query()->forTenant($user->tenant_id, $user->role_slug)->whereNull('deleted_at');
        $applications = Application::query()->forTenant($user->tenant_id, $user->role_slug);
        $tasks = Task::query()->forTenant($user->tenant_id, $user->role_slug);
        $requests = StudentRequest::query()->forTenant($user->tenant_id, $user->role_slug)->where('status', 'pending');

        $pipeline = [
            'lead' => (clone $students)->where('stage', 'lead')->count(),
            'applied' => (clone $students)->where('stage', 'applied')->count(),
            'enrolled' => (clone $students)->where('stage', 'enrolled')->count(),
        ];

        $recentStudents = (clone $students)->latest('id')->limit(5)->get();
        $notifications = Notification::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get();
        $unreadNotifications = $notifications->whereNull('read_at')->count();
        $topPrograms = Application::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->select('program', DB::raw('count(*) as total'))
            ->groupBy('program')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        $upcomingTasks = Task::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereIn('status', ['todo', 'in_progress'])
            ->orderBy('deadline')
            ->limit(6)
            ->get();
        $overdueTasks = Task::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereIn('status', ['todo', 'in_progress'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->count();
        $monthlyRevenue = Payment::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();
        $funnelTotal = max(1, array_sum($pipeline));
        $funnelPercentages = [
            'lead' => (int) round(($pipeline['lead'] / $funnelTotal) * 100),
            'applied' => (int) round(($pipeline['applied'] / $funnelTotal) * 100),
            'enrolled' => (int) round(($pipeline['enrolled'] / $funnelTotal) * 100),
        ];

        return view('dashboard.index', [
            'user' => $user,
            'stats' => [
                'students' => (clone $students)->count(),
                'active_applications' => (clone $applications)->whereIn('status', ['submitted', 'under_review', 'accepted'])->count(),
                'pending_tasks' => (clone $tasks)->whereIn('status', ['todo', 'in_progress'])->count(),
                'new_requests' => $requests->count(),
            ],
            'pipeline' => $pipeline,
            'recentStudents' => $recentStudents,
            'unreadNotifications' => $unreadNotifications,
            'notifications' => $notifications,
            'topPrograms' => $topPrograms,
            'upcomingTasks' => $upcomingTasks,
            'overdueTasks' => $overdueTasks,
            'monthlyRevenue' => $monthlyRevenue,
            'funnelPercentages' => $funnelPercentages,
        ]);
    }
}
