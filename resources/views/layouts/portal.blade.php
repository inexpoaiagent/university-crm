<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Portal</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<div class="layout portal-layout">
    <div class="sidebar-backdrop" id="portalSidebarBackdrop" onclick="closePortalSidebar()"></div>
    <aside class="sidebar">
        <h1>Student Portal</h1>
        <a class="nav-link {{ request()->is('portal/dashboard') ? 'active' : '' }}" href="/portal/dashboard">Dashboard</a>
        <a class="nav-link {{ request()->is('portal/applications') ? 'active' : '' }}" href="/portal/applications">My Applications</a>
        <a class="nav-link {{ request()->is('portal/documents') ? 'active' : '' }}" href="/portal/documents">My Documents</a>
        <a class="nav-link {{ request()->is('portal/universities') ? 'active' : '' }}" href="/portal/universities">Universities</a>
        <a class="nav-link {{ request()->is('portal/messages') ? 'active' : '' }}" href="/portal/messages">Messages</a>
        <form method="POST" action="/portal/logout" style="margin-top:16px;">
            @csrf
            <button type="submit" class="secondary" style="width:100%;">Logout</button>
        </form>
    </aside>
    <main class="main">
        <div class="portal-mobile-topbar">
            <button type="button" class="mobile-menu-btn" onclick="togglePortalSidebar()">☰ Menu</button>
            <strong>Student Portal</strong>
        </div>
        <div class="topbar">
            <div style="display:flex;flex-direction:column;">
                <strong>Student Workspace</strong>
                <span class="footer-note">Track your admission progress in real time</span>
            </div>
            <a class="tab" href="/portal/dashboard">Home</a>
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
    function togglePortalSidebar() {
        const open = document.body.classList.toggle('sidebar-open');
        const backdrop = document.getElementById('portalSidebarBackdrop');
        if (backdrop) {
            backdrop.classList.toggle('show', open);
        }
    }

    function closePortalSidebar() {
        document.body.classList.remove('sidebar-open');
        const backdrop = document.getElementById('portalSidebarBackdrop');
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }
</script>
</body>
</html>
