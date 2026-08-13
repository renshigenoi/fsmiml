<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — FSM Admin IML</title>
    <link rel="shortcut icon" href="/assets/images/icon.png" type="image/x-icon" />
    <link rel="manifest" href="/mobile/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/images/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        /* =========================================================
           DESIGN SYSTEM — IML FSM Admin
        ========================================================= */
        :root {
            /* Navy palette */
            --navy-900: #061429;
            --navy-800: #0b2044;
            --navy-700: #112b5c;
            --navy-600: #1a3a7a;
            --navy-500: #2451a0;
            --navy-400: #3a6bc8;
            --navy-300: #6a9ae8;
            --navy-100: #dce9fc;

            /* Red palette */
            --red-700: #8b0c1e;
            --red-600: #a81226;
            --red-500: #c8102e;
            --red-400: #e01836;
            --red-100: #ffe4e9;

            /* Sidebar */
            --sb-bg: #07111e;
            --sb-bg-2: #0d1d30;
            --sb-text: rgba(255, 255, 255, .60);
            --sb-text-h: rgba(255, 255, 255, .90);
            --sb-bdr: rgba(255, 255, 255, .07);
            --sb-active-bg: rgba(200, 16, 46, .18);
            --sb-active-text: #f47a8c;
            --sb-w: 248px;

            /* Content backgrounds */
            --bg: #f0f4fb;
            --bg-2: #e8eef9;
            --surface: #ffffff;
            --surface-2: #f6f9ff;

            /* Text */
            --ink: #0d1b35;
            --ink-2: #2c3e65;
            --muted: #64748b;
            --muted-2: #94a3b8;

            /* Border */
            --line: #e2e8f4;
            --line-2: #cbd5e8;

            /* Brand button */
            --primary: #c8102e;
            --primary-dark: #a81226;
            --primary-grad: linear-gradient(135deg, #e01836, #8b0c1e);

            /* Shadows */
            --shadow-sm: 0 1px 4px rgba(11, 32, 68, .06);
            --shadow: 0 4px 16px rgba(11, 32, 68, .10);
            --shadow-lg: 0 10px 32px rgba(11, 32, 68, .14);

            /* Radii */
            --r-sm: 8px;
            --r: 12px;
            --r-lg: 16px;

            /* Easing */
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* ---- Reset ---- */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink);
            font-size: 14.5px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        /* =========================================================
           SIDEBAR
        ========================================================= */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sb-w);
            background: var(--sb-bg);
            display: flex;
            flex-direction: column;
            z-index: 50;
            border-right: 1px solid var(--sb-bdr);
            transition: transform .25s var(--ease);
        }

        /* Brand Container (Rata Kiri & Sejajar Navigasi) */
        .sidebar .brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
            gap: 4px;
            padding: 20px 22px 18px;
            /* Padding disesuaikan agar sejajar presisi */
            border-bottom: 1px solid var(--sb-bdr);
            flex-shrink: 0;
        }

        .sidebar .brand-logo {
            background: #ffffff;
            border-radius: 8px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
            margin-bottom: 6px;
        }

        .sidebar .brand-logo img {
            height: 28px;
            width: auto;
            display: block;
            object-fit: contain;
        }

        .sidebar .brand-name {
            color: #ffffff;
            font-weight: 800;
            font-size: 15px;
            letter-spacing: -.2px;
            line-height: 1.2;
        }

        .sidebar .brand-sub {
            color: var(--sb-text);
            font-size: 11.5px;
            font-weight: 500;
            letter-spacing: .1px;
        }

        /* Nav */
        .sidebar nav {
            flex: 1;
            padding: 10px 10px 8px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .1) transparent;
        }

        .sidebar nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 2px;
        }

        .nav-section {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .10em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .25);
            padding: 14px 12px 6px;
        }

        .nav-section:first-child {
            padding-top: 4px;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin: 2px 0;
            color: var(--sb-text);
            text-decoration: none;
            border-radius: var(--r-sm);
            font-size: 13.5px;
            font-weight: 500;
            transition: background .15s, color .15s, transform .1s;
            position: relative;
        }

        .sidebar nav a:hover {
            background: rgba(255, 255, 255, .07);
            color: var(--sb-text-h);
        }

        .sidebar nav a.active {
            background: var(--sb-active-bg);
            color: var(--sb-active-text);
            font-weight: 700;
        }

        .sidebar nav a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 6px;
            bottom: 6px;
            width: 3px;
            background: var(--red-400);
            border-radius: 0 2px 2px 0;
        }

        .sidebar nav a .nav-ico {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 15px;
            background: rgba(255, 255, 255, .06);
            flex-shrink: 0;
            transition: background .15s;
        }

        .sidebar nav a:hover .nav-ico {
            background: rgba(255, 255, 255, .10);
        }

        .sidebar nav a.active .nav-ico {
            background: rgba(200, 16, 46, .25);
        }

        /* Sidebar footer */
        .sidebar-foot {
            padding: 10px;
            border-top: 1px solid var(--sb-bdr);
            flex-shrink: 0;
        }

        .sidebar-foot form {
            margin: 0;
        }

        .sidebar-foot button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            background: none;
            border: 0;
            color: var(--sb-text);
            cursor: pointer;
            border-radius: var(--r-sm);
            font-size: 13.5px;
            font-weight: 500;
            font-family: inherit;
            transition: background .15s, color .15s;
        }

        .sidebar-foot button:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        .sidebar-foot button .nav-ico {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 15px;
            background: rgba(255, 255, 255, .06);
            flex-shrink: 0;
        }

        /* =========================================================
           MAIN AREA
        ========================================================= */
        .main {
            margin-left: var(--sb-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            padding: 0 28px;
            height: 60px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: var(--shadow-sm);
        }

        .hamburger {
            display: none;
            background: none;
            border: 0;
            font-size: 22px;
            cursor: pointer;
            color: var(--ink);
            padding: 4px;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .hamburger:hover {
            background: var(--bg-2);
        }

        .topbar-title {
            flex: 1;
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.2px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 13.5px;
        }

        .topbar-user .user-name {
            font-weight: 600;
            color: var(--ink-2);
        }

        .topbar-user .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(200, 16, 46, .30);
        }

        /* Realtime indicator & toast */
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
        }
        .live-indicator.connected {
            color: #059669;
            border-color: rgba(5, 150, 105, .35);
            background: rgba(5, 150, 105, .08);
        }
        .live-indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
        }
        .live-indicator.connected .live-indicator-dot {
            background: #10b981;
            animation: rt-pulse 1.4s infinite;
        }
        @keyframes rt-pulse {
            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .35;
            }
        }
        .realtime-toast {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--navy-800);
            color: #fff;
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 12px 32px rgba(6, 20, 41, .35);
            transform: translateY(130%);
            opacity: 0;
            transition: transform .25s, opacity .25s;
            max-width: 440px;
        }
        .realtime-toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        .realtime-toast .rt-icon {
            font-size: 16px;
        }
        .realtime-toast .rt-text {
            font-size: 13px;
            flex: 1;
        }
        .realtime-toast .rt-refresh {
            border: 0;
            background: var(--red-500);
            color: #fff;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        /* Back to top */
        .back-to-top {
            position: fixed;
            right: 20px;
            bottom: 84px;
            z-index: 90;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            transition: opacity .2s, transform .2s;
        }
        .back-to-top.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        .btt-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 0;
            background: var(--navy-700,#112b5c);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(11,32,68,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .btt-label {
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            background: rgba(17, 43, 92, .85);
            padding: 3px 9px;
            border-radius: 999px;
            white-space: nowrap;
        }

        /* Content */
        .content {
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            padding: 28px;
            flex: 1;
        }

        .content.guest {
            max-width: 440px;
        }

        /* =========================================================
           COMPONENTS
        ========================================================= */

        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .card h2 {
            margin: 0 0 16px;
            font-size: 15.5px;
            font-weight: 700;
            color: var(--ink);
        }

        .card-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-title-row h2 {
            margin: 0;
        }

        /* Stats grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .stat .num {
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -1.5px;
            color: var(--ink);
            margin: 8px 0 4px;
        }

        .stat .lbl {
            color: var(--muted);
            font-size: 12.5px;
            font-weight: 500;
        }

        .stat .ico {
            font-size: 22px;
            display: block;
        }

        .stat-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
        }

        .stat:nth-child(1) .stat-bar {
            background: linear-gradient(90deg, #f59e0b, #fcd34d);
        }

        .stat:nth-child(2) .stat-bar {
            background: linear-gradient(90deg, #7c3aed, #a78bfa);
        }

        .stat:nth-child(3) .stat-bar {
            background: linear-gradient(90deg, #0284c7, #38bdf8);
        }

        .stat:nth-child(4) .stat-bar {
            background: linear-gradient(90deg, #059669, #34d399);
        }

        .stat:nth-child(5) .stat-bar {
            background: linear-gradient(90deg, var(--red-500), var(--red-300, #f47a8c));
        }

        /* Forms */
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            font-size: 14px;
            font-family: inherit;
            margin-bottom: 14px;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--red-500);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(200, 16, 46, .10);
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: var(--muted);
        }

        /* Buttons */
        .btn {
            background: var(--primary-grad);
            color: #fff;
            border: 0;
            padding: 10px 20px;
            border-radius: var(--r-sm);
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 4px 14px rgba(200, 16, 46, .28);
            transition: transform .15s var(--ease), box-shadow .15s, opacity .15s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(200, 16, 46, .35);
        }

        .btn:active {
            transform: translateY(0) scale(.975);
            box-shadow: 0 2px 8px rgba(200, 16, 46, .20);
        }

        .btn-secondary {
            background: var(--bg-2);
            color: var(--ink-2);
            box-shadow: none;
        }

        .btn-secondary:hover {
            background: var(--line);
            box-shadow: none;
        }

        .btn-navy {
            background: linear-gradient(135deg, var(--navy-700), var(--navy-900));
            box-shadow: 0 4px 14px rgba(11, 32, 68, .28);
        }

        .btn-navy:hover {
            box-shadow: 0 6px 18px rgba(11, 32, 68, .35);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 14px rgba(220, 38, 38, .25);
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: 12.5px;
        }

        /* Flash messages */
        .flash {
            padding: 13px 16px;
            border-radius: var(--r-sm);
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .flash-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .flash-error {
            background: var(--red-100);
            color: var(--red-700);
            border: 1px solid #fecdd3;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead th {
            text-align: left;
            padding: 10px 14px;
            background: var(--bg);
            color: var(--muted);
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1.5px solid var(--line);
        }

        thead th:first-child {
            border-radius: var(--r-sm) 0 0 0;
        }

        thead th:last-child {
            border-radius: 0 var(--r-sm) 0 0;
        }

        tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        tbody tr:hover td {
            background: var(--surface-2);
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: var(--r-sm);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .b-gray {
            background: #e2e8f0;
            color: #334155;
        }

        .b-amber {
            background: #fef3c7;
            color: #92400e;
        }

        .b-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .b-indigo {
            background: #e0e7ff;
            color: #3730a3;
        }

        .b-cyan {
            background: #cffafe;
            color: #155e75;
        }

        .b-violet {
            background: #ede9fe;
            color: #5b21b6;
        }

        .b-green {
            background: #d1fae5;
            color: #065f46;
        }

        .b-rose {
            background: #ffe4e6;
            color: #9f1239;
        }

        .b-red {
            background: var(--red-100);
            color: var(--red-700);
        }

        .b-navy {
            background: var(--navy-100);
            color: var(--navy-700);
        }

        /* Autocomplete */
        .suggest {
            list-style: none;
            margin: 0;
            padding: 0;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            background: var(--surface);
            max-height: 260px;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .suggest li {
            padding: 10px 14px;
            border-bottom: 1px solid var(--line);
            cursor: pointer;
            font-size: 14px;
            transition: background .12s;
        }

        .suggest li:last-child {
            border-bottom: 0;
        }

        .suggest li:hover {
            background: var(--navy-100);
        }

        .suggest small {
            color: var(--muted);
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }

        /* Technician list */
        .tech-list {
            max-height: 240px;
            overflow-y: auto;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            padding: 8px;
            background: var(--surface-2);
        }

        .tech-list label {
            display: flex;
            gap: 8px;
            align-items: center;
            font-weight: 500;
            color: var(--ink);
            font-size: 14px;
            padding: 7px 6px;
            border-radius: 6px;
            text-transform: none;
            letter-spacing: 0;
            margin: 0;
            cursor: pointer;
            transition: background .12s;
        }

        .tech-list label:hover {
            background: var(--navy-100);
        }

        /* Misc */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .muted {
            color: var(--muted);
        }

        .mt {
            margin-top: 14px;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .filter-bar select,
        .filter-bar input {
            width: auto;
            margin-bottom: 0;
        }

        .search-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            align-items: center;
        }

        .search-bar input {
            margin-bottom: 0;
            flex: 1;
        }

        .empty {
            padding: 40px 24px;
            text-align: center;
            color: var(--muted);
            font-size: 13.5px;
        }

        .hidden {
            display: none;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(6, 20, 41, .5);
            z-index: 49;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
            }

            .hamburger {
                display: flex;
            }

            .content {
                padding: 18px 14px;
            }

            thead th,
            tbody td {
                padding: 9px 10px;
                white-space: nowrap;
            }

            .topbar {
                padding: 0 16px;
                height: 54px;
            }

            .topbar-user .user-name {
                display: none;
            }
        }
    </style>
</head>

<body>
    @auth
        <!-- Sidebar overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

        <aside class="sidebar" id="sidebar">
            <!-- Brand (Diubah ke Rata Kiri) -->
            <div class="brand">
                <div class="brand-logo">
                    <img src="{{ asset('assets/images/iml-logo.png') }}" alt="IML" onerror="this.style.display='none'">
                </div>
                <div class="brand-name">FSM Admin</div>
                <div class="brand-sub">Indo Motor Lestari</div>
            </div>

            <!-- Navigation -->
            <nav>
                <div class="nav-section">Menu Utama</div>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    id="nav-dashboard">
                    <span class="nav-ico">🏠</span>
                    Dashboard
                </a>
                <a href="{{ route('dashboard.input') }}" class="{{ request()->routeIs('dashboard.input') ? 'active' : '' }}"
                    id="nav-input">
                    <span class="nav-ico">➕</span>
                    Input SPK
                </a>
                <a href="{{ route('dashboard.work-orders') }}"
                    class="{{ request()->routeIs('dashboard.work-orders', 'dashboard.work-orders.show') ? 'active' : '' }}"
                    id="nav-workorders">
                    <span class="nav-ico">📋</span>
                    Work Orders
                </a>
                <div class="nav-section">Master Data</div>
                <a href="{{ route('dashboard.technicians') }}"
                    class="{{ request()->routeIs('dashboard.technicians') ? 'active' : '' }}" id="nav-technicians">
                    <span class="nav-ico">👷</span>
                    Teknisi
                </a>
                <a href="{{ route('dashboard.attendance') }}"
                    class="{{ request()->routeIs('dashboard.attendance*') ? 'active' : '' }}">
                    <span class="nav-ico">🕘</span>
                    Absensi
                </a>
                <a href="{{ route('dashboard.reset-pin') }}"
                    class="{{ request()->routeIs('dashboard.reset-pin') ? 'active' : '' }}" id="nav-reset-pin">
                    <span class="nav-ico">🔑</span>
                    Reset PIN
                </a>
                <div class="nav-section">Akun</div>
                <a href="{{ route('dashboard.profile') }}"
                    class="{{ request()->routeIs('dashboard.profile') ? 'active' : '' }}" id="nav-profile">
                    <span class="nav-ico">👤</span>
                    Profil
                </a>
            </nav>

            <!-- Footer -->
            <div class="sidebar-foot">
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit" id="btn-logout">
                        <span class="nav-ico">🚪</span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <button class="hamburger" id="hamburger-btn" onclick="toggleSidebar()"
                    aria-label="Toggle sidebar">☰</button>
                <div class="topbar-title">@yield('title', 'Dashboard')</div>
                <div class="live-indicator" id="live-indicator">
                    <span class="live-indicator-dot"></span>
                    <span id="live-indicator-label">OFFLINE</span>
                </div>
                <div class="topbar-user">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                </div>
            </header>
            <main class="content">
                @if (session('success'))
                    <div class="flash flash-success">✅ {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="flash flash-error">⚠️ {{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="flash flash-error">
                        <span>⚠️</span>
                        <ul style="margin:0;padding-left:16px;">
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
        <main class="content guest" style="padding-top: 60px;">
            @if (session('success'))
                <div class="flash flash-success">✅ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash flash-error">⚠️ {{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="flash flash-error">
                    <span>⚠️</span>
                    <ul style="margin:0;padding-left:16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    @endauth

    @auth
        <div id="back-to-top" class="back-to-top">
            <button type="button" class="btt-btn" aria-label="Kembali ke atas">↑</button>
            <span class="btt-label">Kembali ke atas</span>
        </div>
    @endauth

    <script>
        (function () {
            const btn = document.getElementById('back-to-top');
            if (!btn) return;
            const onScroll = () => btn.classList.toggle(
                'show',
                (window.scrollY || document.documentElement.scrollTop) > 260
            );
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
            btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        })();

        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('sidebar-overlay');
            sb.classList.toggle('open');
            ov.classList.toggle('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebar-overlay').classList.remove('show');
        }
    </script>

    @php
        $reverbOptions = config('broadcasting.connections.reverb.options') ?? [];
        $wsHost = (string) ($reverbOptions['host'] ?? '');
        if (in_array($wsHost, ['localhost', '127.0.0.1'], true)) {
            $wsHost = request()->getHost();
        }
        $wsScheme = (string) ($reverbOptions['scheme'] ?? 'http');
        $wsPort = (int) ($reverbOptions['port'] ?? 8080);
        $fsmRealtime = [
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => $wsHost,
            'port' => $wsPort,
            'scheme' => $wsScheme,
        ];
    @endphp
    <script>
        window.FSM_REALTIME = @json($fsmRealtime);
    </script>

    <div id="realtime-toast" class="realtime-toast">
        <span class="rt-icon">🔔</span>
        <span class="rt-text"></span>
        <button type="button" class="rt-refresh" onclick="window.location.reload()">Muat Ulang</button>
    </div>

    <script src="{{ asset('assets/vendor/pusher.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/echo.iife.js') }}"></script>
    <script>
        (function () {
            const cfg = window.FSM_REALTIME || null;
            const indicator = document.getElementById('live-indicator');
            const label = document.getElementById('live-indicator-label');

            function setLive(state) {
                if (!indicator) return;
                indicator.classList.toggle('connected', state === 'connected');
                if (label) {
                    label.textContent = state === 'connected' ? 'LIVE' : (state === 'connecting' ? 'HUBUNG...' : 'OFFLINE');
                }
            }

            if (!cfg || !cfg.key || typeof Echo === 'undefined') {
                setLive('off');
                return;
            }

            setLive('connecting');

            try {
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: cfg.key,
                    cluster: 'ap1',
                    wsHost: cfg.host,
                    wsPort: cfg.port,
                    forceTLS: cfg.scheme === 'https',
                    encrypted: cfg.scheme === 'https',
                    disableStats: true,
                    enabledTransports: ['ws', 'wss'],
                });

                window.Echo.private('dashboard')
                    .listen('.work-order.status.changed', (payload) => {
                        if (typeof window.onFsmWorkOrderChanged === 'function') {
                            window.onFsmWorkOrderChanged(payload);
                        }
                        showRealtimeToast(payload);
                    });

                const pusher = window.Echo.connector.pusher;
                pusher.connection.bind('connected', () => setLive('connected'));
                pusher.connection.bind('disconnected', () => setLive('off'));
                pusher.connection.bind('error', () => setLive('off'));
            } catch (err) {
                console.error('FSM Realtime init gagal:', err);
                setLive('off');
            }

            function statusLabel(s) {
                return ({
                    waiting_acceptance: 'Menunggu Konfirmasi',
                    accepted: 'Diterima',
                    on_the_way: 'Dalam Perjalanan',
                    arrived: 'Sudah Tiba',
                    installation: 'Sedang Pemasangan',
                    finished: 'Selesai',
                    cancelled: 'Dibatalkan',
                    failed: 'Gagal'
                })[s] || s;
            }

            function showRealtimeToast(payload) {
                const box = document.getElementById('realtime-toast');
                if (!box) return;
                const number = payload && payload.number ? payload.number : 'Work Order';
                const status = payload && payload.to_status ? statusLabel(payload.to_status) : '';
                box.querySelector('.rt-text').textContent = number + ' diperbarui' + (status ? ' — ' + status : '');
                box.classList.add('show');
                clearTimeout(window.__fsmRtTimer);
                window.__fsmRtTimer = setTimeout(() => box.classList.remove('show'), 7000);
            }
        })();
    </script>
    @stack('scripts')
</body>

</html>
