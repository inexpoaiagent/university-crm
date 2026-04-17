<!doctype html>
<html lang="en" data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vertue CRM</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
@php
    $authUser = request()->attributes->get('auth_user');
    $headerNotifications = collect();
    $headerUnread = 0;
    if ($authUser) {
        $headerNotifications = \App\Models\Notification::query()
            ->forTenant($authUser->tenant_id, $authUser->role_slug)
            ->where('user_id', $authUser->id)
            ->latest('id')
            ->limit(6)
            ->get();
        $headerUnread = $headerNotifications->whereNull('read_at')->count();
    }
    $can = function (string $permission) use ($authUser): bool {
        if (!$authUser) {
            return false;
        }
        if ($authUser->role_slug === 'super_admin') {
            return true;
        }
        return $authUser->hasPermission($permission);
    };
@endphp
<div class="layout">
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>
    <aside class="sidebar">
        <h1>Vertue CRM</h1>
        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">Dashboard</a>
        @if($can('students.view'))
            <a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="/students">Students</a>
        @endif
        @if($can('applications.view'))
            <a class="nav-link {{ request()->is('applications*') ? 'active' : '' }}" href="/applications">Applications</a>
        @endif
        @if($can('students.view'))
            <a class="nav-link {{ request()->is('pipeline*') ? 'active' : '' }}" href="/pipeline">Pipeline Board</a>
        @endif
        @if($can('tasks.view') || $can('students.view'))
            <a class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}" href="/tasks">Tasks</a>
        @endif
        @if($can('messages.view') || $can('students.view'))
            <a class="nav-link {{ request()->is('messages*') ? 'active' : '' }}" href="/messages">Messages</a>
        @endif
        @if($can('universities.view'))
            <a class="nav-link {{ request()->is('scholarships*') ? 'active' : '' }}" href="/scholarships">Scholarships</a>
            <a class="nav-link {{ request()->is('universities*') ? 'active' : '' }}" href="/universities">Universities</a>
        @endif
        @if($can('finance.view'))
            <a class="nav-link {{ request()->is('finance*') ? 'active' : '' }}" href="/finance">Finance</a>
        @endif
        @if($can('student_requests.view'))
            <a class="nav-link {{ request()->is('student-requests*') ? 'active' : '' }}" href="/student-requests">Student Requests</a>
        @endif
        @if($can('users.view'))
            <a class="nav-link {{ request()->is('agents*') ? 'active' : '' }}" href="/agents">Agents & Roles</a>
            <a class="nav-link {{ request()->is('agents/performance*') ? 'active' : '' }}" href="/agents/performance">Agent Performance</a>
        @endif
        @if($can('students.view'))
            <a class="nav-link {{ request()->is('reports/advanced*') ? 'active' : '' }}" href="/reports/advanced">Advanced Reports</a>
        @endif
        @if($can('users.view'))
            <a class="nav-link {{ request()->is('audit-logs*') ? 'active' : '' }}" href="/audit-logs">Audit Logs</a>
        @endif
        @if($can('settings.update'))
            <a class="nav-link {{ request()->is('templates*') ? 'active' : '' }}" href="/templates">Templates</a>
            <a class="nav-link {{ request()->is('automation-rules*') ? 'active' : '' }}" href="/automation-rules">Automation Rules</a>
            <a class="nav-link {{ request()->is('api-tokens*') ? 'active' : '' }}" href="/api-tokens">API Tokens</a>
        @endif
        @if($can('settings.view'))
            <a class="nav-link {{ request()->is('health*') ? 'active' : '' }}" href="/health">Health Checks</a>
        @endif
        <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" href="/settings">Settings</a>
        <form method="POST" action="/logout" style="margin-top:16px;">
            @csrf
            <button type="submit" class="secondary" style="width:100%;">Logout</button>
        </form>
    </aside>
    <main class="main">
        <div class="topbar">
            <div style="display:flex;flex-direction:column;">
                <button type="button" class="mobile-menu-btn" onclick="toggleSidebar()">☰ Menu</button>
                <strong>{{ $title ?? 'CRM Workspace' }}</strong>
                <span class="footer-note">Multi-tenant admissions CRM</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <form method="GET" action="/search" class="toolbar" style="margin:0;">
                    <input id="global-search" name="q" placeholder="Search students, applications, universities..." style="min-width:320px;">
                    <button type="submit" class="secondary">Search</button>
                </form>
            </div>
            <div class="topbar-actions">
                <a class="icon-btn" title="Advanced Search" href="/search">S</a>
                <button class="icon-btn" title="Notifications" onclick="toggleNotificationPanel()">
                    N
                    @if($headerUnread > 0)
                        <span class="notif-dot">{{ $headerUnread }}</span>
                    @endif
                </button>
                <button class="icon-btn" title="Toggle Theme" onclick="toggleTheme()">T</button>
            </div>
        </div>

        <div id="notifPanel" class="notif-panel hidden" hidden>
            <div class="notif-panel-head">
                <strong>Notifications</strong>
                <a href="/notifications" class="tab">Open all</a>
            </div>
            @forelse($headerNotifications as $n)
                @php
                    $meta = json_decode((string) $n->meta_json, true) ?: [];
                    $target = null;
                    if (!empty($meta['student_request_id'])) {
                        $target = '/student-requests/'.$meta['student_request_id'];
                    } elseif (!empty($meta['student_id'])) {
                        $target = '/students/'.$meta['student_id'];
                    } elseif (!empty($meta['message_id'])) {
                        $target = '/messages'.(!empty($meta['student_id']) ? '?student_id='.$meta['student_id'] : '');
                    }
                @endphp
                <div class="notif-item {{ $n->read_at ? '' : 'unread' }}">
                    <div><strong>{{ $n->title }}</strong></div>
                    <div class="footer-note">{{ $n->body }}</div>
                    @if($target)
                        <div style="margin-top:6px;"><a class="tab" href="{{ $target }}">Open</a></div>
                    @endif
                    @if(!$n->read_at)
                        <form method="POST" action="/notifications/{{ $n->id }}/read">
                            @csrf
                            <button class="secondary">Mark read</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="footer-note">No notifications.</div>
            @endforelse
        </div>

        @if(session('success'))
            <div class="card" style="border-color:#22c55e;margin-bottom:10px;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="card" style="border-color:#ef4444;margin-bottom:10px;">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</div>
<script>
    function toggleTheme() {
        const html = document.documentElement;
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        document.cookie = 'theme=' + next + ';path=/;max-age=31536000';
    }

    function toggleNotificationPanel() {
        const panel = document.getElementById('notifPanel');
        if (panel) {
            panel.classList.toggle('hidden');
            panel.hidden = panel.classList.contains('hidden');
        }
    }

    function toggleSidebar() {
        const open = document.body.classList.toggle('sidebar-open');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) {
            backdrop.classList.toggle('show', open);
        }
    }

    function closeSidebar() {
        document.body.classList.remove('sidebar-open');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }
</script>
</body>
</html>
