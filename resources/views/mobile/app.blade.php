<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0b2044">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/mobile/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/images/icon.png">
    <link rel="shortcut icon" href="/assets/images/icon.png" type="image/x-icon" />
    <title>FSM Teknisi — Indo Motor Lestari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* =========================================================
           DESIGN SYSTEM — IML FSM Mobile (Optimized for Mobile Web)
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
            --navy-200: #b0caf5;
            --navy-100: #dce9fc;

            /* Red palette */
            --red-700: #8b0c1e;
            --red-600: #a81226;
            --red-500: #c8102e;
            --red-400: #e01836;
            --red-300: #f74d6a;
            --red-200: #ffa5b5;
            --red-100: #ffe4e9;

            /* Gradients */
            --brand-grad: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 60%, var(--navy-600) 100%);
            --red-grad: linear-gradient(135deg, var(--red-400) 0%, var(--red-700) 100%);

            /* Backgrounds */
            --bg: #f0f4fb;
            --bg-2: #e8eef9;
            --surface: #ffffff;
            --surface-2: #f6f9ff;
            --glass: rgba(255, 255, 255, .10);
            --glass-bdr: rgba(255, 255, 255, .18);

            /* Text */
            --ink: #0d1b35;
            --ink-2: #2c3e65;
            --muted: #64748b;
            --muted-2: #94a3b8;
            --on-dark: #ffffff;
            --on-dark-2: rgba(255, 255, 255, .75);
            --on-dark-3: rgba(255, 255, 255, .45);

            /* Borders */
            --line: #e2e8f4;
            --line-2: #cbd5e8;

            /* Status */
            --green: #059669;
            --green-bg: #d1fae5;
            --amber: #d97706;
            --amber-bg: #fef3c7;
            --sky: #0284c7;
            --sky-bg: #e0f2fe;
            --violet: #7c3aed;
            --vlt-bg: #ede9fe;
            --rose: #e11d48;
            --rose-bg: #ffe4e6;
            --slate: #475569;
            --slate-bg: #e2e8f0;

            /* Shadows */
            --shadow-sm: 0 1px 4px rgba(11, 32, 68, .06);
            --shadow: 0 4px 16px rgba(11, 32, 68, .10);
            --shadow-lg: 0 10px 32px rgba(11, 32, 68, .14);
            --shadow-xl: 0 20px 56px rgba(11, 32, 68, .20);

            /* Shape */
            --r-sm: 10px;
            --r: 16px;
            --r-lg: 22px;
            --r-xl: 28px;

            /* Easing */
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
            --easef: cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ---- Reset & Mobile Enhancements ---- */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            overscroll-behavior-y: contain;
            touch-action: manipulation;
            user-select: none;
            -webkit-user-select: none;
            background: var(--navy-900);
            /* Mengubah background dasar html/body ke navy gelap */
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink);
            font-size: 15px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        [v-cloak] {
            display: none;
        }

        #app {
            width: 100%;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
        }

        /* Padding bottom dialokasikan khusus ke tampilan app saat sudah login */
        .app-main-content {
            padding-bottom: calc(75px + env(safe-area-inset-bottom));
        }

        .muted {
            color: var(--muted);
        }

        .small {
            font-size: 13px;
        }

        .hidden {
            display: none !important;
        }

        /* =========================================================
           LOGIN SCREEN
        ========================================================= */
        .login-screen {
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: calc(env(safe-area-inset-top, 20px) + 20px) 20px calc(env(safe-area-inset-bottom, 20px) + 20px);
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        .login-top {
            flex: initial;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 380px;
            margin-bottom: 20px;
        }

        .login-logo-wrap {
            background: #ffffff;
            border-radius: var(--r-lg);
            padding: 10px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .3);
            margin-bottom: 16px;
        }

        .login-logo-wrap img {
            height: 44px;
            width: auto;
            display: block;
        }

        .login-screen h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 4px;
            text-align: center;
        }

        .login-tagline {
            text-align: center;
            color: var(--on-dark-2);
            font-size: 13.5px;
            margin: 0;
        }

        .login-card {
            background: var(--surface);
            color: var(--ink);
            border-radius: var(--r-xl);
            padding: 24px 20px;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 380px;
            user-select: text;
            -webkit-user-select: text;
        }

        .login-card label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            margin: 14px 0 6px;
        }

        .login-card label:first-of-type {
            margin-top: 0;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            opacity: .5;
            pointer-events: none;
        }

        .login-card input {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            font-size: 15px;
            font-family: inherit;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .login-card input:focus {
            border-color: var(--red-500);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(200, 16, 46, .12);
        }

        .btn-login {
            width: 100%;
            margin-top: 20px;
            border: 0;
            border-radius: var(--r-sm);
            padding: 15px;
            background: var(--red-grad);
            color: #fff;
            font-size: 15.5px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(200, 16, 46, .35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:active {
            transform: scale(.98);
        }

        .login-error {
            background: var(--red-100);
            color: var(--red-700);
            border: 1px solid var(--red-200);
            border-radius: var(--r-sm);
            padding: 11px 14px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 14px;
            display: flex;
            gap: 8px;
        }

        /* =========================================================
           APP HEADER & TOP NAV
        ========================================================= */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: env(safe-area-inset-top, 0px) 16px 0;
            box-shadow: 0 4px 20px rgba(6, 20, 41, .15);
        }

        .app-header-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0 14px;
        }

        .logo-chip {
            background: #ffffff;
            border-radius: 10px;
            padding: 5px 9px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
        }

        .logo-chip img {
            height: 22px;
            display: block;
        }

        .header-title {
            flex: 1;
            min-width: 0;
        }

        .header-title strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.2;
        }

        .header-title span {
            font-size: 11px;
            color: var(--on-dark-2);
        }

        .icon-btn {
            background: var(--glass);
            border: 1px solid var(--glass-bdr);
            color: var(--on-dark);
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: 17px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s;
        }

        .icon-btn:active {
            background: rgba(255, 255, 255, .25);
        }

        /* =========================================================
           GREETING & STATS
        ========================================================= */
        .greet-band {
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: 4px 16px 28px;
        }

        .greet-band h2 {
            margin: 0 0 2px;
            font-size: 20px;
            font-weight: 800;
        }

        .greet-band p {
            margin: 0;
            color: var(--on-dark-2);
            font-size: 13px;
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 0 16px;
            margin-top: -20px;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--r);
            padding: 14px 16px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
        }

        .stat-card-icon {
            font-size: 20px;
            margin-bottom: 6px;
            display: block;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 2px;
        }

        .stat-card .lbl {
            font-size: 11.5px;
            color: var(--muted);
            font-weight: 600;
        }

        .stat-card.s-red .num {
            color: var(--red-500);
        }

        .stat-card.s-green .num {
            color: var(--green);
        }

        .stat-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3.5px;
            width: 100%;
        }

        .s-red .stat-bar {
            background: linear-gradient(90deg, var(--red-500), var(--red-300));
        }

        .s-green .stat-bar {
            background: linear-gradient(90deg, var(--green), #34d399);
        }

        /* =========================================================
           SECTIONS & CARDS
        ========================================================= */
        .section {
            padding: 0 16px 8px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 18px 0 10px;
        }

        .section-header h3 {
            margin: 0;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .section-line {
            flex: 1;
            height: 1px;
            background: var(--line);
        }

        .wo-card {
            background: var(--surface);
            border-radius: var(--r);
            box-shadow: var(--shadow-sm);
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            border: 1px solid var(--line);
            display: block;
            width: 100%;
            text-align: left;
            font-family: inherit;
            color: inherit;
            transition: transform .12s, border-color .12s;
        }

        .wo-card:active {
            transform: scale(.98);
            border-color: var(--navy-300);
        }

        .wo-card .row1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .wo-card .number {
            font-weight: 800;
            font-size: 15px;
            color: var(--navy-700);
        }

        .wo-card .cust {
            font-weight: 700;
            font-size: 14.5px;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .wo-card .sub {
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .b-amber {
            background: var(--amber-bg);
            color: #92400e;
        }

        .b-green {
            background: var(--green-bg);
            color: #065f46;
        }

        .b-sky {
            background: var(--sky-bg);
            color: #075985;
        }

        .b-violet {
            background: var(--vlt-bg);
            color: #5b21b6;
        }

        .b-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .b-indigo {
            background: #e0e7ff;
            color: #3730a3;
        }

        .b-rose {
            background: var(--rose-bg);
            color: #9f1239;
        }

        .b-red {
            background: var(--red-100);
            color: var(--red-700);
        }

        .b-gray {
            background: var(--slate-bg);
            color: var(--slate);
        }

        .empty {
            background: var(--surface);
            border: 1.5px dashed var(--line-2);
            border-radius: var(--r);
            padding: 24px 16px;
            text-align: center;
            color: var(--muted);
            font-size: 13.5px;
        }

        .empty .big {
            font-size: 32px;
            display: block;
            margin-bottom: 6px;
        }

        /* =========================================================
           DETAIL VIEW & MAP
        ========================================================= */
        .detail-head {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: env(safe-area-inset-top, 0) 14px 0;
            box-shadow: 0 4px 20px rgba(6, 20, 41, .15);
        }

        .detail-head-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
        }

        .detail-head .d-num {
            font-size: 16px;
            font-weight: 800;
        }

        .detail-head .d-sub {
            font-size: 11.5px;
            color: var(--on-dark-2);
        }

        .back-btn {
            background: var(--glass);
            border: 1px solid var(--glass-bdr);
            color: var(--on-dark);
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-body {
            padding: 14px 16px 100px;
        }

        .status-banner {
            border-radius: var(--r);
            padding: 16px;
            color: #fff;
            margin-bottom: 14px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .status-banner .s-label {
            font-size: 17px;
            font-weight: 800;
        }

        .status-banner .s-hint {
            opacity: .9;
            font-size: 12.5px;
            margin-top: 4px;
            line-height: 1.4;
        }

        .card {
            background: var(--surface);
            border-radius: var(--r);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--line);
            padding: 16px;
            margin-bottom: 12px;
            user-select: text;
            -webkit-user-select: text;
        }

        .card h4 {
            margin: 0 0 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .kv {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid var(--bg-2);
            font-size: 14px;
        }

        .kv:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .kv .k {
            color: var(--muted);
            flex-shrink: 0;
            font-size: 13.5px;
        }

        .kv .v {
            text-align: right;
            font-weight: 600;
            color: var(--ink-2);
        }

        .phone-link {
            color: var(--red-500);
            font-weight: 700;
            text-decoration: none;
        }

        #detail-map {
            height: 180px;
            border-radius: var(--r-sm);
            border: 1px solid var(--line);
            overflow: hidden;
            margin-bottom: 10px;
        }

        .btn-maps-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: 0;
            border-radius: var(--r-sm);
            padding: 12px;
            background: #4285f4;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(66, 133, 244, .3);
        }

        .btn-maps-action:active {
            transform: scale(.98);
        }

        /* Stepper */
        .stepper {
            display: flex;
            justify-content: space-between;
            margin: 4px 0 2px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step .dot {
            width: 26px;
            height: 26px;
            margin: 0 auto;
            border-radius: 50%;
            background: var(--surface-2);
            border: 2px solid var(--line-2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            position: relative;
            z-index: 1;
        }

        .step .lbl {
            font-size: 10px;
            color: var(--muted);
            margin-top: 4px;
            font-weight: 500;
        }

        .step.done .dot {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

        .step.active .dot {
            background: var(--red-500);
            border-color: var(--red-500);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(200, 16, 46, .15);
        }

        .step.active .lbl {
            color: var(--red-500);
            font-weight: 700;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 12px;
            right: 50%;
            width: 100%;
            height: 2px;
            background: var(--line);
            z-index: 0;
        }

        .step:first-child::before {
            display: none;
        }

        .step.done::before,
        .step.active::before {
            background: var(--green);
        }

        /* =========================================================
           FIXED BOTTOM ACTION BAR (Mobile Ergonomics)
        ========================================================= */
        .sticky-actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 60;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--line);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            box-shadow: 0 -8px 24px rgba(11, 32, 68, .12);
            display: grid;
            gap: 8px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: 0;
            border-radius: var(--r-sm);
            padding: 15px;
            font-size: 15px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            color: #fff;
            box-shadow: var(--shadow);
        }

        .action-btn:active {
            transform: scale(.98);
        }

        .action-btn:disabled {
            opacity: .55;
            pointer-events: none;
        }

        .action-btn.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .action-btn.red {
            background: var(--red-grad);
        }

        .action-btn.violet {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        .action-btn.blue {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
        }

        .action-btn.amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .action-btn.ghost {
            background: transparent;
            color: var(--red-500);
            border: 1.5px solid rgba(200, 16, 46, .25);
            box-shadow: none;
        }

        /* GPS Status */
        .gps-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .gps-on {
            background: var(--green-bg);
            color: #065f46;
        }

        .gps-off {
            background: var(--red-100);
            color: var(--red-700);
        }

        .gps-live-note {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: 1px solid #6ee7b7;
            border-radius: var(--r-sm);
            padding: 11px 14px;
            font-size: 12.5px;
            font-weight: 600;
            margin: 12px 16px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0;
            animation: pulse-anim 1.4s ease-in-out infinite;
        }

        @keyframes pulse-anim {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .3;
                transform: scale(.8);
            }
        }

        /* Timeline */
        .timeline {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .timeline li {
            position: relative;
            padding: 0 0 16px 28px;
            font-size: 13.5px;
        }

        .timeline li::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--red-500);
            box-shadow: 0 0 0 3px var(--red-100);
        }

        .timeline li::after {
            content: '';
            position: absolute;
            left: 9px;
            top: 20px;
            bottom: 0;
            width: 2px;
            background: var(--line);
        }

        .timeline li:last-child {
            padding-bottom: 0;
        }

        .timeline li:last-child::after {
            display: none;
        }

        .timeline li:last-child::before {
            background: var(--green);
            box-shadow: 0 0 0 3px var(--green-bg);
        }

        /* Toast */
        .toast {
            position: fixed;
            top: calc(env(safe-area-inset-top, 12px) + 12px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            background: var(--navy-800);
            color: #fff;
            padding: 12px 20px;
            border-radius: var(--r-sm);
            font-size: 13.5px;
            font-weight: 600;
            box-shadow: var(--shadow-xl);
            max-width: 90vw;
            text-align: center;
        }

        .toast.error {
            background: var(--red-700);
        }

        .toast.success {
            background: #065f46;
        }

        /* Bottom Sheet Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(6, 20, 41, .65);
            z-index: 90;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal {
            background: var(--surface);
            width: 100%;
            max-width: 500px;
            border-radius: var(--r-xl) var(--r-xl) 0 0;
            padding: 20px 20px calc(20px + env(safe-area-inset-bottom));
            box-shadow: 0 -12px 48px rgba(0, 0, 0, .25);
            user-select: text;
            -webkit-user-select: text;
        }

        .modal-grip {
            width: 36px;
            height: 4px;
            background: var(--line-2);
            border-radius: 2px;
            margin: -6px auto 14px;
        }

        .modal h3 {
            margin: 0 0 4px;
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
        }

        .modal .desc {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 14px;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .chip {
            border: 1.5px solid var(--line);
            background: var(--surface-2);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-family: inherit;
            cursor: pointer;
            color: var(--ink-2);
        }

        .chip.selected {
            border-color: var(--red-500);
            background: var(--red-100);
            color: var(--red-600);
            font-weight: 700;
        }

        .modal textarea,
        .modal input[type="password"] {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            padding: 12px;
            font-size: 14px;
            font-family: inherit;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }

        .modal-actions button {
            flex: 1;
            border: 0;
            border-radius: var(--r-sm);
            padding: 14px;
            font-size: 14.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
        }

        .modal-actions .cancel {
            background: var(--bg);
            color: var(--muted);
        }

        .modal-actions .ok-red {
            background: var(--red-grad);
            color: #fff;
        }

        .loading {
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid var(--line);
            border-top-color: var(--red-500);
            border-radius: 50%;
            animation: spin .75s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .page-slide {
            animation: pg-slide .25s var(--ease);
        }

        @keyframes pg-slide {
            from {
                opacity: 0;
                transform: translateX(16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* =========================================================
           BOTTOM NAVIGATION BAR (Mobile Native Feel)
        ========================================================= */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-around;
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
            box-shadow: 0 -4px 16px rgba(11, 32, 68, .06);
        }

        .nav-item {
            background: none;
            border: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            flex: 1;
        }

        .nav-item .nav-icon {
            font-size: 19px;
            line-height: 1;
        }

        .nav-item.active {
            color: var(--red-500);
            font-weight: 800;
        }
    </style>
</head>

<body>

    @verbatim
        <div id="app" v-cloak>

            <!-- ================================================
                                         LOGIN SCREEN
                                    ================================================ -->
            <div v-if="!token" class="login-screen">
                <div class="login-top">
                    <div class="login-logo-wrap">
                        <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                    </div>
                    <h1>FSM Teknisi</h1>
                    <p class="login-tagline">Sistem Manajemen Field Service</p>
                </div>

                <div class="login-card">
                    <label>Email</label>
                    <div class="input-wrap">
                        <span class="input-icon">📧</span>
                        <input type="email" v-model.trim="loginForm.email" placeholder="nama@indomotorlestari.co.id"
                            autocomplete="email" inputmode="email">
                    </div>
                    <label>Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" v-model="loginForm.password" placeholder="••••••••"
                            autocomplete="current-password" @keyup.enter="doLogin">
                    </div>
                    <button class="btn-login" :disabled="busy" @click="doLogin" id="btn-login">
                        <span v-if="busy">⏳ Memeriksa akun…</span>
                        <span v-else>Masuk ke Akun</span>
                    </button>
                    <div v-if="loginError" class="login-error">
                        <span>⚠️</span><span>{{ loginError }}</span>
                    </div>
                </div>
                <div style="text-align:center; margin-top:20px; font-size:12px; color:var(--on-dark-3);">
                    Indo Motor Lestari © 2026 · FSM Mobile<br />
                    Develop by Abekz Computer
                </div>
            </div>

            <!-- ================================================
                                         MAIN APP CONTENT
                                    ================================================ -->
            <template v-else>
                <div class="app-main-content">
                    <!-- ========== HOME VIEW ========== -->
                    <div v-if="view === 'home'" class="page-slide">
                        <div class="app-header">
                            <div class="app-header-inner">
                                <div class="logo-chip">
                                    <img src="/assets/images/iml-logo.png" alt="IML">
                                </div>
                                <div class="header-title">
                                    <strong>FSM Teknisi</strong>
                                    <span>{{ todayLabel }}</span>
                                </div>
                                <button class="icon-btn" @click="refresh" title="Muat ulang" id="btn-refresh">⟳</button>
                            </div>
                        </div>

                        <div class="greet-band">
                            <h2>Halo, {{ firstName }}! 👋</h2>
                            <p>{{ greetingLine }}</p>
                        </div>

                        <div class="stats">
                            <div class="stat-card s-red">
                                <span class="stat-card-icon">📋</span>
                                <div class="num">{{ pendingOrders . length }}</div>
                                <div class="lbl">Menunggu konfirmasi</div>
                                <div class="stat-bar"></div>
                            </div>
                            <div class="stat-card s-green">
                                <span class="stat-card-icon">🚀</span>
                                <div class="num">{{ activeOrders . length }}</div>
                                <div class="lbl">Sedang diproses</div>
                                <div class="stat-bar"></div>
                            </div>
                        </div>

                        <div v-if="onTrip" class="gps-live-note">
                            <span class="pulse" style="color:#059669"></span>
                            <span>Live tracking aktif — posisimu sedang dibagikan ke pelanggan. Biarkan browser tetap
                                terbuka!</span>
                        </div>

                        <div v-if="loading" class="loading">
                            <div class="spinner"></div>
                        </div>

                        <template v-else>
                            <div v-if="noTechnicianLink" class="section" style="margin-top:14px;">
                                <div
                                    style="background:var(--red-100); color:var(--red-700); padding:14px; border-radius:var(--r); border:1px solid var(--red-200); font-size:13px; display:flex; gap:10px;">
                                    <span>⚠️</span>
                                    <span>Akun ini belum terhubung ke data teknisi. Hubungi koordinator agar akunmu
                                        ditautkan.</span>
                                </div>
                            </div>

                            <!-- Pending -->
                            <div class="section" style="margin-top:14px;">
                                <div class="section-header">
                                    <h3>🔔 Menunggu Konfirmasi</h3>
                                    <div class="section-line"></div>
                                </div>
                                <div v-if="pendingOrders.length === 0" class="empty">
                                    <span class="big">🎉</span>
                                    Tidak ada tugas baru saat ini.<br>Santai sebentar, tugas akan segera datang!
                                </div>
                                <button v-for="wo in pendingOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)" :id="'wo-' + wo.id">
                                    <div class="row1">
                                        <span class="number">{{ wo . number }}</span>
                                        <span class="badge b-amber">⏳ Konfirmasi</span>
                                    </div>
                                    <div class="cust">{{ wo . customer ? wo . customer . name : 'Customer' }}</div>
                                    <div class="sub">
                                        <span>📅 {{ fmtDate(wo . scheduled_start_at) }}</span>
                                        <span>·</span>
                                        <span>👤 Untukmu</span>
                                    </div>
                                </button>
                            </div>

                            <!-- Active -->
                            <div class="section">
                                <div class="section-header">
                                    <h3>🚀 Sedang Diproses</h3>
                                    <div class="section-line"></div>
                                </div>
                                <div v-if="activeOrders.length === 0" class="empty">
                                    <span class="big">😴</span>
                                    Belum ada pekerjaan berjalan.
                                </div>
                                <button v-for="wo in activeOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)" :id="'wo-active-' + wo.id">
                                    <div class="row1">
                                        <span class="number">{{ wo . number }}</span>
                                        <span class="badge"
                                            :class="statusBadge(wo.status)">{{ statusLabel(wo . status) }}</span>
                                    </div>
                                    <div class="cust">{{ wo . customer ? wo . customer . name : 'Customer' }}</div>
                                    <div class="sub">
                                        <span>📍 {{ wo . service_location ? wo . service_location . address : '-' }}</span>
                                        <span v-if="superseded(wo)"> · Diambil teknisi lain</span>
                                    </div>
                                </button>
                            </div>

                            <!-- History -->
                            <div class="section" v-if="historyOrders.length">
                                <div class="section-header">
                                    <h3>🗂️ Riwayat Pekerjaan</h3>
                                    <div class="section-line"></div>
                                </div>
                                <button v-for="wo in historyOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)" :id="'wo-hist-' + wo.id" style="opacity:.85;">
                                    <div class="row1">
                                        <span class="number">{{ wo . number }}</span>
                                        <span class="badge"
                                            :class="statusBadge(historyStatus(wo))">{{ historyStatusLabel(wo) }}</span>
                                    </div>
                                    <div class="cust">{{ wo . customer ? wo . customer . name : 'Customer' }}</div>
                                    <div class="sub">📅 {{ fmtDate(wo . scheduled_start_at) }}</div>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- ========== DETAIL VIEW ========== -->
                    <div v-else-if="view === 'detail' && current" class="page-slide">
                        <div class="detail-head">
                            <div class="detail-head-inner">
                                <button class="back-btn" @click="goHome" id="btn-back">←</button>
                                <div>
                                    <div class="d-num">{{ current . number }}</div>
                                    <div class="d-sub">{{ current . work_type || 'Pemasangan' }}</div>
                                </div>
                                <div style="flex:1"></div>
                                <span class="badge"
                                    :class="statusBadge(current.status)">{{ statusLabel(current . status) }}</span>
                            </div>
                        </div>

                        <div class="detail-body">

                            <!-- Status Banner -->
                            <div class="status-banner" :style="bannerStyle">
                                <div class="s-label">{{ statusLabel(current . status) }}</div>
                                <div class="s-hint">{{ statusHint }}</div>
                            </div>

                            <!-- Stepper -->
                            <div class="card">
                                <h4>📊 Progres Pemasangan</h4>
                                <div class="stepper">
                                    <div v-for="(step, i) in steps" :key="i" class="step"
                                        :class="{ done: i < stepIndex, active: i === stepIndex }">
                                        <div class="dot">{{ i < stepIndex ? '✓' : i + 1 }}</div>
                                        <div class="lbl">{{ step }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Customer -->
                            <div class="card">
                                <h4>📄 Informasi Pekerjaan</h4>
                                <div class="kv"><span class="k">Customer</span><span
                                        class="v">{{ current . customer ? current . customer . name : '-' }}</span>
                                </div>
                                <div class="kv" v-if="current.customer && current.customer.phone">
                                    <span class="k">Telepon</span>
                                    <span class="v"><a class="phone-link" :href="'tel:' + current.customer.phone">📞
                                            {{ current . customer . phone }}</a></span>
                                </div>
                                <div class="kv"><span class="k">Jadwal</span><span
                                        class="v">{{ fmtDateTime(current . scheduled_start_at) }}</span></div>
                                <div class="kv" v-if="current.notes">
                                    <span class="k">Catatan</span>
                                    <span class="v"
                                        style="text-align:left;font-weight:500;color:var(--ink-2);">{{ current . notes }}</span>
                                </div>
                                <div class="kv" v-if="current.items && current.items.length">
                                    <span class="k">Item</span>
                                    <span class="v"
                                        style="text-align:left;">{{ getProductNames(current . items) }}</span>
                                </div>
                            </div>

                            <!-- Lokasi + Direct Maps Navigation Button -->
                            <div class="card">
                                <h4>📍 Lokasi Pemasangan</h4>
                                <div style="font-size:14px;font-weight:600;color:var(--ink-2);margin-bottom:10px;">
                                    {{ current . service_location ? current . service_location . address : '-' }}
                                </div>
                                <div v-if="current.service_location && current.service_location.latitude">
                                    <div id="detail-map"></div>
                                    <a class="btn-maps-action" :href="getNavigationUrl(current.service_location)"
                                        target="_blank" rel="noopener">
                                        🗺️ Buka Navigasi (Google Maps)
                                    </a>
                                </div>
                                <div v-else class="muted small">Koordinat lokasi belum diatur oleh koordinator.</div>
                            </div>

                            <!-- GPS Tracker Status -->
                            <div v-if="current.status === 'on_the_way'" class="card">
                                <h4>📡 Lokasi Langsung</h4>
                                <span class="gps-pill" :class="gpsState === 'active' ? 'gps-on' : 'gps-off'">
                                    <span class="pulse"></span>
                                    {{ gpsState === 'active'
                                        ? '✅ Lokasi dikirim · ' + (gpsSentLabel || 'baru saja')
                                        : (gpsState === 'error'
                                            ? '⚠️ GPS bermasalah, cek izin lokasi HP'
                                            : '⌛ Menyiapkan GPS…') }}
                                </span>
                                <p class="muted small" style="margin:0;font-size:12.5px;line-height:1.4;">
                                    Pelanggan memantau posisimu secara real-time. Pastikan GPS menyala! 🙏
                                </p>
                            </div>

                            <!-- History Timeline -->
                            <div class="card" v-if="current.status_histories && current.status_histories.length">
                                <h4>📜 Riwayat Status</h4>
                                <ul class="timeline">
                                    <li v-for="h in current.status_histories" :key="h.id">
                                        <div style="font-weight:700;color:var(--ink-2)">{{ statusLabel(h . to_status) }}
                                        </div>
                                        <div style="color:var(--muted);font-size:12px;">
                                            {{ fmtDateTime(h . occurred_at) }}<span v-if="h.reason"> —
                                                {{ h . reason }}</span></div>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <!-- Sticky Bottom Action Bar for Ergonomics -->
                        <div class="sticky-actions-bar" v-if="actionList.length">
                            <button v-for="act in actionList" :key="act.key" class="action-btn"
                                :class="act.cls" :disabled="busy" @click="runAction(act)"
                                :id="'act-' + act.key">
                                {{ act . label }}
                            </button>
                        </div>
                    </div>

                    <!-- ========== BOTTOM NAVIGATION BAR (Main Tabs) ========== -->
                    <nav class="bottom-nav" v-if="view === 'home'">
                        <button class="nav-item active">
                            <span class="nav-icon">🏠</span>
                            <span>Beranda</span>
                        </button>
                        <button class="nav-item" @click="openPassModal">
                            <span class="nav-icon">🔑</span>
                            <span>Password</span>
                        </button>
                        <button class="nav-item" @click="doLogout">
                            <span class="nav-icon">🚪</span>
                            <span>Keluar</span>
                        </button>
                    </nav>
                </div>
            </template>

            <!-- TOAST -->
            <div v-if="toast.show" class="toast" :class="toast.type" id="toast-msg">{{ toast . message }}</div>

            <!-- MODAL ALASAN -->
            <div v-if="modal.show" class="modal-backdrop" @click.self="modal.show = false">
                <div class="modal">
                    <div class="modal-grip"></div>
                    <h3>{{ modal . title }}</h3>
                    <p class="desc">{{ modal . desc }}</p>
                    <div class="chip-row">
                        <button v-for="chip in modal.chips" :key="chip" class="chip"
                            :class="{ selected: modal.reason === chip }"
                            @click="modal.reason = chip">{{ chip }}</button>
                    </div>
                    <textarea v-model="modal.reason" placeholder="Atau tulis alasanmu di sini…" rows="2"></textarea>
                    <div class="modal-actions">
                        <button class="cancel" @click="modal.show = false">Batal</button>
                        <button class="ok-red" :disabled="busy || !modal.reason.trim()" @click="submitReason">
                            {{ busy ? '⏳ Mengirim…' : 'Kirim' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL GANTI PASSWORD -->
            <div v-if="passModal.show" class="modal-backdrop" @click.self="passModal.show = false">
                <div class="modal">
                    <div class="modal-grip"></div>
                    <h3>Ganti Password 🔑</h3>
                    <p class="desc">Password awal teknisi adalah <b>12345</b>. Segera ganti demi keamanan!</p>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <input type="password" v-model.trim="passModal.current" placeholder="Password saat ini"
                            autocomplete="current-password">
                        <input type="password" v-model.trim="passModal.next" placeholder="Password baru (min 6 karakter)"
                            autocomplete="new-password">
                        <input type="password" v-model.trim="passModal.confirm" placeholder="Ulangi password baru"
                            autocomplete="new-password" @keyup.enter="submitPasswordChange">
                    </div>
                    <div v-if="passModal.error" style="color:var(--red-700); font-size:12.5px; margin-top:8px;">⚠️
                        {{ passModal . error }}</div>
                    <div class="modal-actions">
                        <button class="cancel" @click="passModal.show = false">Batal</button>
                        <button class="ok-red" :disabled="busy" @click="submitPasswordChange">
                            {{ busy ? '⏳ Menyimpan…' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            const STATUS_META = {
                draft: {
                    label: 'Draf',
                    color: '#64748b'
                },
                waiting_acceptance: {
                    label: 'Menunggu Konfirmasi',
                    color: '#d97706'
                },
                accepted: {
                    label: 'Diterima',
                    color: '#0284c7'
                },
                on_the_way: {
                    label: 'Dalam Perjalanan',
                    color: '#7c3aed'
                },
                arrived: {
                    label: 'Sudah Tiba',
                    color: '#0369a1'
                },
                installation: {
                    label: 'Sedang Pemasangan',
                    color: '#4f46e5'
                },
                finished: {
                    label: 'Selesai',
                    color: '#059669'
                },
                rejected: {
                    label: 'Ditolak',
                    color: '#e11d48'
                },
                superseded: {
                    label: 'Diambil Teknisi Lain',
                    color: '#64748b'
                },
                cancelled: {
                    label: 'Dibatalkan',
                    color: '#64748b'
                },
                failed: {
                    label: 'Gagal',
                    color: '#991b1b'
                },
            };
            const BADGE_CLASS = {
                waiting_acceptance: 'b-amber',
                accepted: 'b-sky',
                on_the_way: 'b-violet',
                arrived: 'b-blue',
                installation: 'b-indigo',
                finished: 'b-green',
                rejected: 'b-rose',
                superseded: 'b-gray',
                cancelled: 'b-gray',
                failed: 'b-red',
                draft: 'b-gray',
            };
            const HINTS = {
                waiting_acceptance: 'Konfirmasi dulu ya — apakah kamu bisa mengerjakan tugas ini?',
                accepted: 'Pekerjaan sudah kamu terima. Siapkan perjalananmu! 🗺️',
                on_the_way: 'GPS aktif — pelanggan bisa melihat posisimu. Hati-hati di jalan! 🛵',
                arrived: 'Kamu sudah di lokasi. Kabari pelanggan bahwa kamu sudah tiba.',
                installation: 'Fokus kerjakan yang terbaik. Pelanggan menantikan hasilnya! 🔧',
                finished: 'Kerja bagus! Pekerjaan selesai dengan sempurna. 👏',
                rejected: 'Kamu menolak pekerjaan ini. Koordinator akan segera menindaklanjuti.',
                cancelled: 'Pekerjaan ini dibatalkan oleh koordinator.',
                failed: 'Pekerjaan terkendala. Tim koordinator akan menindaklanjuti.',
                draft: 'Pekerjaan masih disiapkan oleh koordinator.',
            };

            const app = Vue.createApp({
                data() {
                    return {
                        token: localStorage.getItem('fsm_tech_token') || '',
                        user: JSON.parse(localStorage.getItem('fsm_tech_user') || 'null'),
                        view: 'home',
                        loginForm: {
                            email: '',
                            password: ''
                        },
                        loginError: '',
                        busy: false,
                        loading: true,
                        orders: [],
                        current: null,
                        toast: {
                            show: false,
                            message: '',
                            type: 'info'
                        },
                        modal: {
                            show: false,
                            mode: 'reject',
                            reason: '',
                            title: '',
                            desc: '',
                            chips: []
                        },
                        passModal: {
                            show: false,
                            current: '',
                            next: '',
                            confirm: '',
                            error: ''
                        },
                        gpsState: 'off',
                        gpsSentLabel: '',
                        watchId: null,
                        gpsTimer: null,
                        lastPos: null,
                        tripSessionId: null,
                        pollTimer: null,
                        mapInstance: null,
                        mapMarker: null,
                        mapPosMarker: null,
                    };
                },
                computed: {
                    todayLabel() {
                        const d = new Date();
                        return d.toLocaleDateString('id-ID', {
                            weekday: 'short',
                            day: 'numeric',
                            month: 'short'
                        });
                    },
                    firstName() {
                        return this.user ? (this.user.name || 'Teknisi').split(' ')[0] : 'Teknisi';
                    },
                    greetingLine() {
                        const h = new Date().getHours();
                        if (h < 11) return 'Pagi yang cerah, waktunya berkarya! ☀️';
                        if (h < 15) return 'Siang hari, tetap semangat kerjanya! ⚡';
                        if (h < 18) return 'Sore hari, tinggal sedikit lagi! 🌤️';
                        return 'Malam hari, kerja kerasmu luar biasa! 🌙';
                    },
                    pendingOrders() {
                        return this.orders.filter(function(wo) {
                            const a = this.myAssignment(wo);
                            return a && a.status === 'pending' && wo.status === 'waiting_acceptance';
                        }.bind(this));
                    },
                    activeOrders() {
                        return this.orders.filter(function(wo) {
                            const a = this.myAssignment(wo);
                            return a && (a.status === 'accepted' ||
                                a.status === 'superseded' || ['on_the_way', 'arrived', 'installation']
                                .includes(wo.status));
                        }.bind(this));
                    },
                    historyOrders() {
                        return this.orders.filter(function(wo) {
                            const a = this.myAssignment(wo);
                            if (!a) return false;
                            return ['rejected', 'superseded'].includes(a.status) || ['finished', 'cancelled',
                                'failed'
                            ].includes(wo.status);
                        }.bind(this));
                    },
                    noTechnicianLink() {
                        return this.user && !this.user.technician_id;
                    },
                    onTrip() {
                        return this.orders.some(function(wo) {
                            return wo.status === 'on_the_way' && this.myAssignment(wo) && this.myAssignment(
                                wo).status === 'accepted';
                        }.bind(this));
                    },
                    steps() {
                        return ['Diterima', 'Berangkat', 'Tiba', 'Pasang', 'Selesai'];
                    },
                    stepIndex() {
                        const map = {
                            accepted: 0,
                            on_the_way: 1,
                            arrived: 2,
                            installation: 3,
                            finished: 4
                        };
                        return this.current ? (map[this.current.status] ?? -1) : -1;
                    },
                    bannerStyle() {
                        const meta = STATUS_META[this.current ? this.current.status : 'draft'] || STATUS_META.draft;
                        const c = meta.color;
                        return {
                            background: 'linear-gradient(135deg, ' + c + 'ee, ' + c + '99)'
                        };
                    },
                    statusHint() {
                        return HINTS[this.current ? this.current.status : 'draft'] || '';
                    },
                    actionList() {
                        if (!this.current) return [];
                        const s = this.current.status;
                        const a = this.myAssignment(this.current);
                        const mine = a && a.status === 'accepted';
                        const list = [];
                        if (s === 'waiting_acceptance' && a && a.status === 'pending') {
                            list.push({
                                key: 'accept',
                                label: '✅ Terima Pekerjaan',
                                cls: 'green'
                            });
                            list.push({
                                key: 'reject',
                                label: '❌ Tolak Pekerjaan',
                                cls: 'ghost'
                            });
                        } else if (s === 'accepted' && mine) {
                            list.push({
                                key: 'start-trip',
                                label: '🚗 Mulai Perjalanan',
                                cls: 'violet'
                            });
                            list.push({
                                key: 'fail',
                                label: '⚠️ Laporkan Kendala',
                                cls: 'ghost'
                            });
                        } else if (s === 'on_the_way' && mine) {
                            list.push({
                                key: 'arrive',
                                label: '📍 Saya Sudah Tiba',
                                cls: 'blue'
                            });
                            list.push({
                                key: 'fail',
                                label: '⚠️ Laporkan Kendala',
                                cls: 'ghost'
                            });
                        } else if (s === 'arrived' && mine) {
                            list.push({
                                key: 'start-installation',
                                label: '🔧 Mulai Pemasangan',
                                cls: 'amber'
                            });
                            list.push({
                                key: 'fail',
                                label: '⚠️ Laporkan Kendala',
                                cls: 'ghost'
                            });
                        } else if (s === 'installation' && mine) {
                            list.push({
                                key: 'finish',
                                label: '🎉 Selesaikan Pemasangan',
                                cls: 'green'
                            });
                            list.push({
                                key: 'fail',
                                label: '⚠️ Laporkan Kendala',
                                cls: 'ghost'
                            });
                        }
                        return list;
                    },
                },
                mounted() {
                    if (this.token) {
                        this.loadOrders();
                        this.pollTimer = setInterval(function() {
                            this.loadOrders(true);
                        }.bind(this), 45000);
                    }
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/mobile/sw.js').catch(function() {});
                    }
                },
                methods: {
                    getProductNames(items) {
                        if (!items || !items.length) return '-';
                        return items.map(function(i) {
                            return i.product_name;
                        }).join(', ');
                    },
                    getNavigationUrl(loc) {
                        if (!loc || !loc.latitude) return '#';
                        return 'https://www.google.com/maps/dir/?api=1&destination=' + loc.latitude + ',' + loc
                            .longitude;
                    },
                    myAssignment(wo) {
                        if (!wo || !wo.assignments || !this.user) return null;
                        const techId = this.user.technician_id;
                        return wo.assignments.find(function(a) {
                            return a.technician_id === techId;
                        }) || null;
                    },
                    superseded(wo) {
                        const a = this.myAssignment(wo);
                        return a && a.status === 'superseded';
                    },
                    statusLabel(status) {
                        return (STATUS_META[status] || STATUS_META.draft).label;
                    },
                    statusBadge(status) {
                        return BADGE_CLASS[status] || 'b-gray';
                    },
                    historyStatus(wo) {
                        const a = this.myAssignment(wo);
                        if (a && a.status === 'superseded') return 'superseded';
                        if (a && a.status === 'rejected') return 'rejected';
                        return wo.status;
                    },
                    historyStatusLabel(wo) {
                        return this.statusLabel(this.historyStatus(wo));
                    },
                    fmtDate(value) {
                        if (!value) return '-';
                        return new Date(value).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                    },
                    fmtDateTime(value) {
                        if (!value) return '-';
                        const d = new Date(value);
                        return d.toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short'
                            }) +
                            ' ' + d.toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                    },
                    async api(path, options = {}) {
                        const config = {
                            method: options.method || 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                        };
                        if (this.token) config.headers.Authorization = 'Bearer ' + this.token;
                        if (options.body) config.body = JSON.stringify(options.body);
                        const response = await fetch('/api/v1' + path, config);
                        if (response.status === 401) {
                            this.forceLogout();
                            throw new Error('Sesi berakhir, silakan masuk lagi.');
                        }
                        const data = await response.json().catch(function() {
                            return {};
                        });
                        if (!response.ok) {
                            const message = data.message ||
                                Object.values(data.errors || {}).flat()[0] ||
                                'Terjadi kesalahan. Coba lagi.';
                            throw new Error(message);
                        }
                        return data;
                    },
                    showToast(message, type = 'info') {
                        this.toast = {
                            show: true,
                            message,
                            type
                        };
                        clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(function() {
                            this.toast.show = false;
                        }.bind(this), 3200);
                    },
                    async doLogin() {
                        if (!this.loginForm.email || !this.loginForm.password) {
                            this.loginError = 'Email dan password wajib diisi.';
                            return;
                        }
                        this.busy = true;
                        this.loginError = '';
                        try {
                            const response = await fetch('/api/v1/auth/login', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    email: this.loginForm.email,
                                    password: this.loginForm.password,
                                    device_name: 'fsm-mobile-web'
                                }),
                            });
                            const data = await response.json().catch(function() {
                                return {};
                            });
                            if (!response.ok) throw new Error(data.message || 'Login gagal.');

                            this.token = data.token;
                            this.user = data.user;
                            localStorage.setItem('fsm_tech_token', this.token);
                            localStorage.setItem('fsm_tech_user', JSON.stringify(this.user));
                            this.view = 'home';
                            this.loadOrders();
                            this.pollTimer = setInterval(function() {
                                this.loadOrders(true);
                            }.bind(this), 45000);
                        } catch (err) {
                            this.loginError = err.message;
                        } finally {
                            this.busy = false;
                        }
                    },
                    async loadOrders(silent = false) {
                        if (!this.token) return;
                        if (!silent) this.loading = true;
                        try {
                            const data = await this.api('/work-orders');
                            this.orders = data.data || [];
                            if (!this.user) {
                                const me = await this.api('/auth/me');
                                this.user = me.data || me;
                                localStorage.setItem('fsm_tech_user', JSON.stringify(this.user));
                            }
                            await this.syncTracking();
                        } catch (err) {
                            if (!silent) this.showToast(err.message, 'error');
                        } finally {
                            this.loading = false;
                        }
                    },
                    async refresh() {
                        this.showToast('Memuat ulang…');
                        await this.loadOrders();
                        this.showToast('Data terbaru dimuat ✓', 'success');
                    },
                    async openDetail(wo) {
                        this.view = 'detail';
                        this.current = wo;
                        try {
                            const data = await this.api('/work-orders/' + wo.id);
                            this.current = data.data || data;
                            const session = this.current.tracking_sessions ?
                                this.current.tracking_sessions.find(function(s) {
                                    return s.status === 'active';
                                }) : null;
                            if (this.current.status === 'on_the_way' && session) this.tripSessionId = session.id;
                            this.$nextTick(function() {
                                this.initMap();
                            }.bind(this));
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        }
                    },
                    goHome() {
                        this.view = 'home';
                        this.destroyMap();
                        this.current = null;
                        if (!this.onTrip) this.stopGps();
                    },
                    async runAction(act) {
                        if (act.key === 'accept') {
                            this.busy = true;
                            try {
                                const a = this.myAssignment(this.current);
                                await this.api('/assignments/' + a.id + '/accept', {
                                    method: 'POST'
                                });
                                this.showToast('Pekerjaan diterima. Gas! 🔥', 'success');
                                await this.reloadDetail();
                            } catch (err) {
                                this.showToast(err.message, 'error');
                            } finally {
                                this.busy = false;
                            }
                            return;
                        }
                        if (act.key === 'reject') {
                            this.modal = {
                                show: true,
                                mode: 'reject',
                                reason: '',
                                title: 'Tolak Pekerjaan?',
                                desc: 'Kasih tahu koordinator alasannya:',
                                chips: ['Jadwal bentrok', 'Lokasi jauh', 'Ada tugas lain', 'Izin / Sakit']
                            };
                            return;
                        }
                        if (act.key === 'fail') {
                            this.modal = {
                                show: true,
                                mode: 'fail',
                                reason: '',
                                title: 'Laporkan Kendala',
                                desc: 'Jelaskan kendala di lapangan:',
                                chips: ['Customer tidak ada', 'Akses terhambat', 'Masalah teknis',
                                    'Kendaraan mogok'
                                ]
                            };
                            return;
                        }
                        if (act.key === 'finish') {
                            if (!confirm('Yakin pekerjaan ini sudah selesai? 🙌')) return;
                        }
                        this.busy = true;
                        try {
                            await this.api('/work-orders/' + this.current.id + '/' + act.key, {
                                method: 'POST'
                            });
                            this.showToast('Berhasil! 🎉', 'success');
                            await this.reloadDetail();
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        } finally {
                            this.busy = false;
                        }
                    },
                    async submitReason() {
                        const reason = this.modal.reason.trim();
                        if (!reason || this.busy) return;
                        this.busy = true;
                        try {
                            if (this.modal.mode === 'reject') {
                                const a = this.myAssignment(this.current);
                                await this.api('/assignments/' + a.id + '/reject', {
                                    method: 'POST',
                                    body: {
                                        reason: reason
                                    }
                                });
                                this.showToast('Pekerjaan ditolak.', 'success');
                            } else {
                                await this.api('/work-orders/' + this.current.id + '/fail', {
                                    method: 'POST',
                                    body: {
                                        reason: reason
                                    }
                                });
                                this.showToast('Kendala sudah dilaporkan.', 'success');
                            }
                            this.modal.show = false;
                            await this.reloadDetail();
                            await this.loadOrders(true);
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        } finally {
                            this.busy = false;
                        }
                    },
                    async reloadDetail() {
                        if (!this.current) return;
                        try {
                            const data = await this.api('/work-orders/' + this.current.id);
                            this.current = data.data || data;
                            if (this.current.status === 'on_the_way') {
                                const session = this.current.tracking_sessions ?
                                    this.current.tracking_sessions.find(function(s) {
                                        return s.status === 'active';
                                    }) : null;
                                if (session) this.tripSessionId = session.id;
                                this.startGps();
                            } else {
                                this.tripSessionId = null;
                                this.stopGps();
                            }
                            this.$nextTick(function() {
                                this.initMap();
                            }.bind(this));
                            await this.loadOrders(true);
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        }
                    },
                    async syncTracking() {
                        if (!this.token) return;
                        const trip = this.orders.find(function(wo) {
                            return wo.status === 'on_the_way' && this.myAssignment(wo) && this.myAssignment(
                                wo).status === 'accepted';
                        }.bind(this));
                        if (!trip) {
                            this.tripSessionId = null;
                            this.stopGps();
                            return;
                        }
                        if (!this.tripSessionId) {
                            try {
                                const data = await this.api('/work-orders/' + trip.id);
                                const wo = data.data || data;
                                const session = (wo.tracking_sessions || []).find(function(s) {
                                    return s.status === 'active';
                                });
                                if (session) this.tripSessionId = session.id;
                            } catch (err) {
                                return;
                            }
                        }
                        if (this.tripSessionId) this.startGps();
                    },
                    activeSession() {
                        if (!this.current || !this.current.tracking_sessions) return null;
                        return this.current.tracking_sessions.find(function(s) {
                            return s.status === 'active';
                        }) || null;
                    },
                    startGps() {
                        if (this.watchId !== null) return;
                        if (!('geolocation' in navigator)) {
                            this.gpsState = 'error';
                            return;
                        }
                        this.gpsState = 'starting';
                        this.watchId = navigator.geolocation.watchPosition(
                            function(pos) {
                                this.lastPos = pos;
                                this.gpsState = 'active';
                                this.sendLocation();
                            }.bind(this),
                            function() {
                                this.gpsState = 'error';
                            }.bind(this), {
                                enableHighAccuracy: true,
                                maximumAge: 5000,
                                timeout: 15000
                            }
                        );
                        this.gpsTimer = setInterval(function() {
                            if (this.lastPos && this.onTrip) this.sendLocation();
                        }.bind(this), 15000);
                    },
                    async sendLocation() {
                        const sessionId = this.tripSessionId || (this.activeSession() ? this.activeSession().id :
                            null);
                        if (!sessionId || !this.lastPos) return;
                        const pos = this.lastPos.coords;
                        try {
                            await this.api('/tracking-sessions/' + sessionId + '/locations', {
                                method: 'POST',
                                body: {
                                    latitude: +pos.latitude.toFixed(7),
                                    longitude: +pos.longitude.toFixed(7),
                                    accuracy_meters: pos.accuracy != null ? Math.round(pos.accuracy) : null,
                                    speed_mps: pos.speed != null ? Math.round(pos.speed * 100) / 100 : null,
                                    heading_degrees: pos.heading != null ? Math.round(pos.heading) : null,
                                    recorded_at: new Date().toISOString(),
                                },
                            });
                            this.gpsSentLabel = new Date().toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        } catch (err) {}
                    },
                    stopGps() {
                        if (this.watchId !== null) {
                            navigator.geolocation.clearWatch(this.watchId);
                            this.watchId = null;
                        }
                        if (this.gpsTimer) {
                            clearInterval(this.gpsTimer);
                            this.gpsTimer = null;
                        }
                        this.gpsState = 'off';
                        this.lastPos = null;
                    },
                    initMap() {
                        if (typeof L === 'undefined') return;
                        const loc = this.current && this.current.service_location;
                        const el = document.getElementById('detail-map');
                        if (!loc || !loc.latitude || !el) return;
                        this.destroyMap();
                        const lat = parseFloat(loc.latitude),
                            lng = parseFloat(loc.longitude);
                        this.mapInstance = L.map('detail-map', {
                            zoomControl: false
                        }).setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(this.mapInstance);
                        this.mapMarker = L.marker([lat, lng]).addTo(this.mapInstance);
                    },
                    destroyMap() {
                        if (this.mapInstance) {
                            this.mapInstance.remove();
                            this.mapInstance = null;
                        }
                        this.mapMarker = null;
                        this.mapPosMarker = null;
                    },
                    forceLogout() {
                        this.stopGps();
                        this.token = '';
                        this.user = null;
                        this.current = null;
                        this.view = 'home';
                        localStorage.removeItem('fsm_tech_token');
                        localStorage.removeItem('fsm_tech_user');
                        clearInterval(this.pollTimer);
                    },
                    async doLogout() {
                        try {
                            await this.api('/auth/logout', {
                                method: 'DELETE'
                            });
                        } catch (err) {}
                        this.forceLogout();
                        this.showToast('Sampai jumpa! 👋');
                    },
                    openPassModal() {
                        this.passModal = {
                            show: true,
                            current: '',
                            next: '',
                            confirm: '',
                            error: ''
                        };
                    },
                    async submitPasswordChange() {
                        const p = this.passModal;
                        if (!p.current || !p.next || !p.confirm) {
                            p.error = 'Kolom wajib diisi.';
                            return;
                        }
                        if (p.next !== p.confirm) {
                            p.error = 'Password tidak cocok.';
                            return;
                        }
                        if (p.next.length < 6) {
                            p.error = 'Password minimal 6 karakter.';
                            return;
                        }
                        this.busy = true;
                        p.error = '';
                        try {
                            await this.api('/auth/change-password', {
                                method: 'POST',
                                body: {
                                    current_password: p.current,
                                    new_password: p.next,
                                    new_password_confirmation: p.confirm
                                },
                            });
                            p.show = false;
                            this.showToast('Password berhasil diganti! 🔐', 'success');
                        } catch (err) {
                            p.error = err.message;
                        } finally {
                            this.busy = false;
                        }
                    },
                },
            });
            app.mount('#app');
        </script>
    @endverbatim

</body>

</html>
