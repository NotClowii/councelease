<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — CounselEase</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3"></script>

    <style>
        :root {
            --primary:       #2D6A4F;
            --primary-light: #52B788;
            --primary-dark:  #1B4332;
            --accent:        #40916C;
            --accent-warm:   #74C69D;
            --surface:       #F8FAF9;
            --surface-2:     #EEF4F1;
            --surface-3:     #D8EDE3;
            --border:        #C8DDD5;
            --border-light:  #E2EFE9;
            --text-primary:  #0F2419;
            --text-secondary:#3D6B57;
            --text-muted:    #7A9E8E;
            --white:         #FFFFFF;
            --danger:        #E53E3E;
            --danger-light:  #FFF5F5;
            --warning:       #D69E2E;
            --warning-light: #FFFFF0;
            --info:          #3182CE;
            --info-light:    #EBF8FF;
            --success:       #38A169;
            --success-light: #F0FFF4;
            --sidebar-width: 260px;
            --header-height: 64px;
            --radius:        10px;
            --radius-lg:     16px;
            --shadow-sm:     0 1px 3px rgba(15,36,25,.06), 0 1px 2px rgba(15,36,25,.04);
            --shadow:        0 4px 12px rgba(15,36,25,.08), 0 2px 4px rgba(15,36,25,.04);
            --shadow-lg:     0 10px 30px rgba(15,36,25,.12), 0 4px 10px rgba(15,36,25,.06);
            --transition:    all 0.2s ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--surface);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
        }

        /* ─── SIDEBAR ─────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--primary-dark);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar-logo {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 40px; height: 40px;
            background: var(--primary-light);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-logo-icon i { font-size: 20px; color: white; }

        .sidebar-logo-text { color: white; }
        .sidebar-logo-text .name { font-size: 16px; font-weight: 700; letter-spacing: -.3px; }
        .sidebar-logo-text .sub  { font-size: 11px; color: rgba(255,255,255,.5); letter-spacing: .3px; text-transform: uppercase; }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            padding: 12px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: rgba(255,255,255,.7);
            font-weight: 500;
            font-size: 13.5px;
            transition: var(--transition);
            margin-bottom: 2px;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(255,255,255,.08);
            color: white;
        }

        .nav-item.active {
            background: var(--primary-light);
            color: white;
            font-weight: 600;
        }

        .nav-item i { font-size: 18px; flex-shrink: 0; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent-warm);
            color: var(--primary-dark);
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 100px;
            min-width: 18px;
            text-align: center;
        }

        .sidebar-user {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: white;
            flex-shrink: 0;
        }

        .user-info { flex: 1; overflow: hidden; }
        .user-info .user-name  { font-size: 13px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info .user-role  { font-size: 11px; color: rgba(255,255,255,.5); }

        .logout-btn {
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,.4); font-size: 18px;
            padding: 4px; border-radius: 6px;
            transition: var(--transition);
        }
        .logout-btn:hover { color: #fc8181; background: rgba(255,255,255,.08); }

        /* ─── MAIN CONTENT ──────────────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─── TOP HEADER ──────────────────────────────────── */
        .topbar {
            height: var(--header-height);
            background: var(--white);
            border-bottom: 1px solid var(--border-light);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
            box-shadow: var(--shadow-sm);
        }

        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-title { font-size: 17px; font-weight: 700; color: var(--text-primary); letter-spacing: -.3px; }
        .topbar-breadcrumb { font-size: 12px; color: var(--text-muted); }

        .topbar-right { display: flex; align-items: center; gap: 8px; }

        .topbar-btn {
            width: 38px; height: 38px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--white);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text-secondary);
            font-size: 18px;
            transition: var(--transition);
            position: relative;
            text-decoration: none;
        }

        .topbar-btn:hover { background: var(--surface-2); border-color: var(--border); color: var(--primary); }

        .notif-badge {
            position: absolute; top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Notification dropdown */
        .notif-dropdown {
            position: absolute; top: 44px; right: 0;
            width: 340px;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            z-index: 200;
            display: none;
        }
        .notif-dropdown.open { display: block; }
        .notif-header {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--border-light);
            display: flex; align-items: center; justify-content: space-between;
        }
        .notif-header h4 { font-size: 14px; font-weight: 700; }
        .notif-mark-all { font-size: 12px; color: var(--primary); cursor: pointer; background: none; border: none; }
        .notif-list { max-height: 320px; overflow-y: auto; }
        .notif-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light);
            display: flex; gap: 10px; align-items: flex-start;
            transition: var(--transition);
        }
        .notif-item.unread { background: var(--surface-2); }
        .notif-item:hover { background: var(--surface); }
        .notif-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--primary-light); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .notif-title { font-size: 13px; font-weight: 600; }
        .notif-msg   { font-size: 12px; color: var(--text-muted); margin-top: 2px; line-height: 1.4; }
        .notif-time  { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
        .notif-empty { padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px; }

        /* ─── PAGE CONTENT ──────────────────────────────────── */
        .page-content {
            flex: 1;
            padding: 28px;
        }

        /* ─── ALERTS ─────────────────────────────────────────── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px;
            border: 1px solid;
        }
        .alert i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .alert-success { background: var(--success-light); border-color: #9AE6B4; color: #276749; }
        .alert-error   { background: var(--danger-light);  border-color: #FEB2B2; color: #9B2C2C; }
        .alert-warning { background: var(--warning-light); border-color: #FAF089; color: #744210; }
        .alert-info    { background: var(--info-light);    border-color: #90CDF4; color: #2A4365; }

        /* ─── CARDS ────────────────────────────────────────────── */
        .card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--border-light);
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -.2px;
        }

        .card-body { padding: 22px; }

        /* ─── STAT CARDS ───────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 20px;
            display: flex; align-items: flex-start; gap: 14px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .stat-card:hover { box-shadow: var(--shadow); transform: translateY(-1px); }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-icon.green  { background: var(--success-light); color: var(--success); }
        .stat-icon.blue   { background: var(--info-light); color: var(--info); }
        .stat-icon.amber  { background: var(--warning-light); color: var(--warning); }
        .stat-icon.teal   { background: var(--surface-3); color: var(--primary); }

        .stat-info { flex: 1; }
        .stat-value { font-size: 26px; font-weight: 700; color: var(--text-primary); line-height: 1; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500; }

        /* ─── TABLES ────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 10px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }

        tbody td {
            padding: 12px 16px;
            font-size: 13.5px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: var(--surface); }

        /* ─── BADGES ───────────────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-success  { background: var(--success-light); color: var(--success); }
        .badge-danger   { background: var(--danger-light);  color: var(--danger); }
        .badge-warning  { background: var(--warning-light); color: var(--warning); }
        .badge-info     { background: var(--info-light);    color: var(--info); }
        .badge-secondary{ background: var(--surface-2);     color: var(--text-secondary); }
        .badge-primary  { background: #EBF4FF;              color: #2B6CB0; }

        /* ─── BUTTONS ───────────────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
            font-family: inherit;
        }

        .btn i { font-size: 16px; }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .btn-primary:hover { background: var(--accent); border-color: var(--accent); }

        .btn-outline {
            background: transparent;
            color: var(--text-secondary);
            border-color: var(--border);
        }
        .btn-outline:hover { background: var(--surface-2); color: var(--text-primary); }

        .btn-success { background: var(--success); color: white; border-color: var(--success); }
        .btn-success:hover { background: #2F855A; }

        .btn-danger  { background: var(--danger); color: white; border-color: var(--danger); }
        .btn-danger:hover  { background: #C53030; }

        .btn-warning { background: var(--warning); color: white; border-color: var(--warning); }

        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-sm i { font-size: 14px; }

        .btn-icon {
            width: 34px; height: 34px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
        }

        /* ─── FORMS ─────────────────────────────────────────────── */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 9px 13px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13.5px;
            color: var(--text-primary);
            background: var(--white);
            transition: var(--transition);
            font-family: inherit;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(82,183,136,.15);
        }

        textarea.form-control { resize: vertical; min-height: 100px; }

        .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 5px; }

        .field-error {
            font-size: 12px;
            color: var(--danger);
            margin-top: 5px;
            display: flex; align-items: center; gap: 4px;
        }

        .form-control.is-invalid { border-color: var(--danger); }

        /* ─── PAGINATION ────────────────────────────────────── */
        .pagination { display: flex; align-items: center; gap: 4px; }
        .pagination a, .pagination span {
            padding: 6px 11px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: var(--text-secondary);
            border: 1px solid transparent;
            transition: var(--transition);
        }
        .pagination a:hover { background: var(--surface-2); }
        .pagination .active span {
            background: var(--primary);
            color: white;
        }
        .pagination [disabled] span { color: var(--text-muted); }

        /* ─── UTILS ─────────────────────────────────────────── */
        .flex         { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mt-1 { margin-top: 4px; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .text-muted { color: var(--text-muted); }
        .text-sm { font-size: 12px; }
        .font-semibold { font-weight: 600; }
        .w-full { width: 100%; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

        /* Avatar initials */
        .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--primary-light);
            color: white;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            flex-shrink: 0;
        }
        .avatar.lg { width: 48px; height: 48px; font-size: 16px; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-muted);
        }
        .empty-state i   { font-size: 48px; display: block; margin-bottom: 12px; color: var(--border); }
        .empty-state h3  { font-size: 15px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
        .empty-state p   { font-size: 13px; }

        /* Filter bar */
        .filter-bar {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            padding: 14px 18px;
            background: var(--surface);
            border-bottom: 1px solid var(--border-light);
        }
        .filter-bar .form-control { width: auto; }

        /* Page header */
        .page-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
        }
        .page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -.4px; color: var(--text-primary); }
        .page-header p  { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        /* Divider */
        .divider { border: none; border-top: 1px solid var(--border-light); margin: 18px 0; }

        /* Detail list */
        .detail-list dt { font-size: 11.5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 3px; }
        .detail-list dd { font-size: 14px; color: var(--text-primary); margin-bottom: 14px; }

        /* Timeline */
        .timeline { position: relative; padding-left: 24px; }
        .timeline::before { content: ''; position: absolute; left: 7px; top: 4px; bottom: 4px; width: 2px; background: var(--border-light); }
        .timeline-item { position: relative; margin-bottom: 18px; }
        .timeline-dot { position: absolute; left: -20px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary-light); border: 2px solid white; box-shadow: 0 0 0 2px var(--primary-light); }
        .timeline-content { font-size: 13px; }
        .timeline-title { font-weight: 600; color: var(--text-primary); }
        .timeline-time  { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
    </style>

    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <i class="ph-bold ph-heart"></i>
        </div>
        <div class="sidebar-logo-text">
            <div class="name">CounselEase</div>
            <div class="sub">Guidance System</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Main</span>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="ph-bold ph-squares-four"></i>
            Dashboard
        </a>

        @if(!auth()->user()->isStudent())
        <a href="{{ route('students.index') }}" class="nav-item {{ request()->routeIs('students.*') ? 'active' : '' }}">
            <i class="ph-bold ph-student"></i>
            Students
        </a>
        @endif

        @if(auth()->user()->isSystemAdmin() || auth()->user()->isSchoolAdmin() || auth()->user()->isOfficeStaff())
        <a href="#" class="nav-item {{ request()->routeIs('counselors.*') ? 'active' : '' }}">
            <i class="ph-bold ph-users-three"></i>
            Counselors
        </a>
        @endif

        <span class="nav-section-label">Operations</span>

        <a href="{{ route('appointments.index') }}" class="nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
            <i class="ph-bold ph-calendar-check"></i>
            Appointments
            @php $pendingCount = auth()->user()->isCounselor() ? (auth()->user()->counselor?->appointments()->where('appointment_status','pending')->count() ?? 0) : (auth()->user()->isStudent() ? 0 : \App\Models\Appointment::where('appointment_status','pending')->count()) @endphp
            @if($pendingCount > 0)
            <span class="nav-badge">{{ $pendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('sessions.index') }}" class="nav-item {{ request()->routeIs('sessions.*') ? 'active' : '' }}">
            <i class="ph-bold ph-chats-circle"></i>
            Sessions
        </a>

        @if(auth()->user()->isCounselor() || auth()->user()->isSystemAdmin())
        <a href="{{ route('case-notes.index') }}" class="nav-item {{ request()->routeIs('case-notes.*') ? 'active' : '' }}">
            <i class="ph-bold ph-notebook"></i>
            Case Notes
        </a>
        @endif

        @if(auth()->user()->canAccessReports())
        <span class="nav-section-label">Insights</span>
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="ph-bold ph-chart-bar"></i>
            Reports & Analytics
        </a>
        @endif

        @if(auth()->user()->isSystemAdmin())
        <span class="nav-section-label">System</span>
        <a href="#" class="nav-item">
            <i class="ph-bold ph-gear"></i>
            Settings
        </a>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
         <i class="ph-bold ph-users"></i>
         User Management
        </a>
        @endif
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar">{{ auth()->user()->initials }}</div>
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->full_name }}</div>
            <div class="user-role">{{ auth()->user()->role?->role_name }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn" title="Log out">
                <i class="ph-bold ph-sign-out"></i>
            </button>
        </form>
    </div>
</aside>

{{-- MAIN --}}
<div class="main-wrapper">
    {{-- TOPBAR --}}
    <header class="topbar">
        <div class="topbar-left">
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                @hasSection('breadcrumb')
                <div class="topbar-breadcrumb">@yield('breadcrumb')</div>
                @endif
            </div>
        </div>
        <div class="topbar-right">
            {{-- Notifications --}}
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            <div style="position:relative;">
                <button class="topbar-btn" id="notifToggle" title="Notifications">
                    <i class="ph-bold ph-bell"></i>
                    @if($unreadCount > 0) <span class="notif-badge"></span> @endif
                </button>

                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <h4>Notifications @if($unreadCount > 0) <span class="badge badge-danger" style="font-size:10px;padding:1px 7px;">{{ $unreadCount }}</span> @endif</h4>
                        @if($unreadCount > 0)
                        <button class="notif-mark-all" onclick="markAllRead()">Mark all read</button>
                        @endif
                    </div>
                    <div class="notif-list" id="notifList">
                        @forelse(auth()->user()->notifications()->orderByDesc('date_sent')->take(8)->get() as $notif)
                        <div class="notif-item {{ $notif->is_read ? '' : 'unread' }}" data-id="{{ $notif->notif_id }}">
                            <div class="notif-icon">
                                <i class="ph-bold ph-{{ $notif->type === 'appointment' ? 'calendar' : ($notif->type === 'session' ? 'chats' : 'bell') }}"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="notif-title">{{ $notif->title }}</div>
                                <div class="notif-msg">{{ Str::limit($notif->message, 80) }}</div>
                                <div class="notif-time">{{ $notif->date_sent->diffForHumans() }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="notif-empty">
                            <i class="ph ph-bell-slash" style="font-size:32px;display:block;margin-bottom:6px;"></i>
                            No notifications yet
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Profile link --}}
            <a href="#" class="topbar-btn" title="My Profile">
                <i class="ph-bold ph-user-circle"></i>
            </a>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="page-content">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success">
            <i class="ph-bold ph-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <i class="ph-bold ph-x-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            <i class="ph-bold ph-warning-circle"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top:4px;padding-left:16px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
// Notification dropdown
const notifToggle = document.getElementById('notifToggle');
const notifDropdown = document.getElementById('notifDropdown');

notifToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle('open');
});

document.addEventListener('click', (e) => {
    if (!notifDropdown?.contains(e.target) && e.target !== notifToggle) {
        notifDropdown?.classList.remove('open');
    }
});

function markAllRead() {
    fetch('{{ route("notifications.read-all") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    }).then(() => {
        document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
        document.querySelector('.notif-badge')?.remove();
        document.querySelector('.notif-mark-all')?.remove();
    });
}
</script>

@stack('scripts')
</body>
</html>
