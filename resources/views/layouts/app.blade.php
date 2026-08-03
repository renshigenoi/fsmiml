<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FSM Admin') - {{ config('app.name') }}</title>
    <style>
        :root {
            --bg: #f1f5f9;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", system-ui, sans-serif; background: var(--bg); color: var(--text); }
        header { background: var(--card); border-bottom: 1px solid var(--border); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        header .brand { font-weight: 700; font-size: 18px; color: var(--primary); }
        header .user { color: var(--muted); font-size: 14px; display: flex; gap: 12px; align-items: center; }
        main { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .card h2 { margin-top: 0; font-size: 17px; }
        input[type="text"], input[type="password"], input[type="email"], input[type="date"], textarea, select {
            width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; margin-bottom: 12px;
        }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: var(--muted); }
        .btn { background: var(--primary); color: #fff; border: 0; padding: 10px 18px; border-radius: 8px; font-size: 14px; cursor: pointer; }
        .btn:hover { background: var(--primary-dark); }
        .btn-secondary { background: #e2e8f0; color: var(--text); }
        .btn-danger { background: #dc2626; }
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
        .tech-list { max-height: 220px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 8px; }
        .tech-list label { display: flex; gap: 8px; align-items: center; font-weight: 400; color: var(--text); font-size: 14px; padding: 5px 0; }
        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        .muted { color: var(--muted); }
        .mt { margin-top: 12px; }
        .filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 12px; }
        .filter-bar select { width: auto; margin-bottom: 0; }
        @media (max-width: 720px) {
            header { flex-direction: column; align-items: flex-start; gap: 8px; }
            main { margin: 16px auto; padding: 0 10px; }
            .card { padding: 14px; }
            th, td { padding: 8px; white-space: nowrap; }
            .btn { width: 100%; }
            .meta-grid { grid-template-columns: 1fr; }
            .tech-list { max-height: 260px; }
        }
    </style>
</head>
<body>
<header>
    <div class="brand">{{ config('app.name') }}</div>
    @auth
        <div class="user">
            <span>{{ auth()->user()->name }} ({{ auth()->user()->role->value }})</span>
            <a href="{{ url('/dashboard/profile') }}" style="color:var(--muted);text-decoration:none;">Profil</a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button class="btn btn-secondary" type="submit">Logout</button>
            </form>
        </div>
    @endauth
</header>
<main>
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
@stack('scripts')
</body>
</html>
