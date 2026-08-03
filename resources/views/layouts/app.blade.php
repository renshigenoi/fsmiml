<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <style>
        :root {
            --bg: #f1f5f9;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --sidebar-bg: #0f172a;
            --sidebar-text: #cbd5e1;
            --sidebar-active: #2563eb;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed; inset: 0 auto 0 0; width: 240px; background: var(--sidebar-bg);
            display: flex; flex-direction: column; z-index: 50;
        }
        .sidebar .brand {
            display: flex; align-items: center; gap: 10px; padding: 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar .brand img { height: 38px; width: auto; border-radius: 8px; background: #fff; padding: 3px; }
        .sidebar .brand span { color: #fff; font-weight: 700; font-size: 15px; }
        .sidebar nav { flex: 1; padding: 12px 8px; overflow-y: auto; }
        .sidebar nav a {
            display: flex; align-items: center; gap: 10px; padding: 11px 14px; margin: 2px 0;
            color: var(--sidebar-text); text-decoration: none; border-radius: 8px; font-size: 14px;
        }
        .sidebar nav a:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar nav a.active { background: var(--sidebar-active); color: #fff; font-weight: 600; }
        .sidebar nav a .ico { width: 20px; text-align: center; font-size: 16px; }
        .sidebar-foot { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,.08); }
        .sidebar-foot a, .sidebar-foot button {
            display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; margin: 2px 0;
            background: none; border: 0; color: var(--sidebar-text); cursor: pointer; text-decoration: none;
            border-radius: 8px; font-size: 14px; font-family: inherit;
        }
        .sidebar-foot a:hover, .sidebar-foot button:hover { background: rgba(255,255,255,.07); color: #fff; }

        /* ---------- Main ---------- */
        .main { margin-left: 240px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: var(--card); border-bottom: 1px solid var(--border); padding: 14px 24px;
            display: flex; align-items: center; gap: 14px; position: sticky; top: 0; z-index: 40;
        }
        .topbar h1 { margin: 0; font-size: 18px; flex: 1; }
        .topbar .user { display: flex; align-items: center; gap: 10px; color: var(--muted); font-size: 14px; }
        .topbar .user .avatar {
            width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;
        }
        .hamburger { display: none; background: none; border: 0; font-size: 22px; cursor: pointer; color: var(--text); }
        .content { max-width: 1150px; width: 100%; margin: 0 auto; padding: 24px; flex: 1; }
        .content.guest { max-width: 460px; }

        /* ---------- Components ---------- */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
        .card h2 { margin: 0 0 14px; font-size: 16px; }
        .card-title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
        .card-title-row h2 { margin: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .stat { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
        .stat .num { font-size: 28px; font-weight: 800; line-height: 1.1; }
        .stat .lbl { color: var(--muted); font-size: 13px; margin-top: 4px; }
        .stat .ico { font-size: 22px; }
        .stat-head { display: flex; justify-content: space-between; align-items: flex-start; }

        input[type="text"], input[type="password"], input[type="email"], input[type="date"], textarea, select {
            width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; margin-bottom: 12px;
        }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: var(--muted); }
        .btn { background: var(--primary); color: #fff; border: 0; padding: 10px 18px; border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: var(--primary-dark); }
        .btn-secondary { background: #e2e8f0; color: var(--text); }
        .btn-danger { background: #dc2626; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .hidden { display: none; }
        .flash { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .flash-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .flash-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
        .table-wrap { overflow-x: auto; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .b-gray { background: #e2e8f0; color: #334155; }
        .b-amber { background: #fef3c7; color: #92400e; }
        .b-blue { background: #dbeafe; color: #1e40af; }
        .b-indigo { background: #e0e7ff; color: #3730a3; }
        .b-cyan { background: #cffafe; color: #155e75; }
        .b-violet { background: #ede9fe; color: #5b21b6; }
        .b-green { background: #dcfce7; color: #166534; }
        .b-rose { background: #ffe4e6; color: #9f1239; }
        .b-red { background: #fee2e2; color: #991b1b; }
        .suggest { list-style: none; margin: 0; padding: 0; border: 1px solid var(--border); border-radius: 8px; background: #fff; max-height: 260px; overflow-y: auto; }
        .suggest li { padding: 10px 12px; border-bottom: 1px solid var(--border); cursor: pointer; font-size: 14px; }
        .suggest li:hover { background: #eff6ff; }
        .suggest small { color: var(--muted); display: block; }
        .tech-list { max-height: 240px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 8px; }
        .tech-list label { display: flex; gap: 8px; align-items: center; font-weight: 400; color: var(--text); font-size: 14px; padding: 5px 0; }
        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        .muted { color: var(--muted); }
        .mt { margin-top: 12px; }
        .filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 12px; }
        .filter-bar select { width: auto; margin-bottom: 0; }
        .search-bar { display: flex; gap: 8px; margin-bottom: 14px; }
        .search-bar input { margin-bottom: 0; }
        .empty { padding: 26px; text-align: center; color: var(--muted); }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); transition: transform .2s ease; }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .hamburger { display: block; }
            .content { padding: 16px 12px; }
            th, td { padding: 8px; white-space: nowrap; }
            .btn { width: 100%; }
            .meta-grid { grid-template-columns: 1fr; }
            .topbar { padding: 12px 14px; }
            .topbar .user span { display: none; }
        }
    </style>
</head>
<body>
@auth
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="{{ asset('assets/images/iml-logo.png') }}" alt="IML" onerror="this.style.display='none'">
            <span>FSM Admin</span>
        </div>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="ico">🏠</span> Dashboard
            </a>
            <a href="{{ route('dashboard.input') }}" class="{{ request()->routeIs('dashboard.input') ? 'active' : '' }}">
                <span class="ico">➕</span> Input SPK
            </a>
            <a href="{{ route('dashboard.work-orders') }}" class="{{ request()->routeIs('dashboard.work-orders', 'dashboard.work-orders.show') ? 'active' : '' }}">
                <span class="ico">📋</span> Work Orders
            </a>
            <a href="{{ route('dashboard.technicians') }}" class="{{ request()->routeIs('dashboard.technicians') ? 'active' : '' }}">
                <span class="ico">👷</span> Teknisi
            </a>
            <a href="{{ route('dashboard.profile') }}" class="{{ request()->routeIs('dashboard.profile') ? 'active' : '' }}">
                <span class="ico">👤</span> Profil
            </a>
        </nav>
        <div class="sidebar-foot">
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit"><span class="ico">🚪</span> Logout</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="user">
                <span>{{ auth()->user()->name }}</span>
                <div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            </div>
        </header>
        <main class="content">
            @if (session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="flash flash-error">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
@else
    <main class="content guest">
        @if (session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash flash-error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
@endauth
@stack('scripts')
</body>
</html>
