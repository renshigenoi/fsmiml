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
           DESIGN SYSTEM — IML FSM Mobile
        ========================================================= */
        :root {
            /* Navy palette */
            --navy-900: #061429;
            --navy-800: #0b2044;
            --navy-700: #112b5c;
            --navy-600: #1a3a7a;
            --navy-500: #2451a0;
            --navy-400: #3a6bc8;

            /* Red palette */
            --red-700: #8b0c1e;
            --red-600: #a81226;
            --red-500: #c8102e;
            --red-400: #e01836;
            --red-100: #ffe4e9;

            /* Gradients */
            --brand-grad: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 60%, var(--navy-600) 100%);
            --red-grad: linear-gradient(135deg, var(--red-400) 0%, var(--red-700) 100%);

            /* Backgrounds */
            --bg: #f0f4fb;
            --bg-2: #e8eef9;
            --surface: #ffffff;
            --surface-2: #f6f9ff;
            --glass: rgba(255, 255, 255, .12);
            --glass-bdr: rgba(255, 255, 255, .20);

            /* Text */
            --ink: #0d1b35;
            --ink-2: #2c3e65;
            --muted: #64748b;
            --on-dark: #ffffff;
            --on-dark-2: rgba(255, 255, 255, .75);

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

            /* Shape */
            --r-sm: 10px;
            --r: 16px;
            --r-lg: 22px;
            --r-xl: 28px;
        }

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
            min-height: 100%;
            background: var(--bg);
            /* Menyamakan background global agar tidak terpotong */
            color: var(--ink);
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
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
            background: var(--bg);
        }

        .app-main-content {
            padding-bottom: calc(80px + env(safe-area-inset-bottom));
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
        }

        .login-top {
            display: flex;
            flex-direction: column;
            align-items: center;
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
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 380px;
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
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(200, 16, 46, .35);
        }

        .login-error {
            background: var(--red-100);
            color: var(--red-700);
            border: 1px solid #fecdd3;
            border-radius: var(--r-sm);
            padding: 11px 14px;
            font-size: 13px;
            margin-top: 14px;
            display: flex;
            gap: 8px;
        }

        /* =========================================================
           APP HEADER & NAVIGATION
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
            padding: 12px 0;
        }

        .logo-chip {
            background: #ffffff;
            border-radius: 10px;
            padding: 5px 9px;
            flex-shrink: 0;
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
        }

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

        /* =========================================================
           SEGMENTED TAB SWITCHER & HORIZONTAL SCROLL CHIPS
        ========================================================= */
        .tab-switcher-wrapper {
            padding: 0 16px;
            margin-top: -20px;
            position: relative;
            z-index: 10;
        }

        .tab-switcher {
            display: flex;
            background: var(--surface);
            border: 1px solid var(--line);
            padding: 4px;
            border-radius: var(--r);
            box-shadow: var(--shadow);
        }

        .tab-btn {
            flex: 1;
            border: 0;
            background: transparent;
            padding: 11px 8px;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--muted);
            border-radius: 12px;
            cursor: pointer;
            transition: all .2s var(--ease);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .tab-btn.active {
            background: var(--brand-grad);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(11, 32, 68, .2);
        }

        .tab-count {
            background: rgba(255, 255, 255, 0.25);
            padding: 2px 7px;
            border-radius: 99px;
            font-size: 11px;
        }

        .tab-btn:not(.active) .tab-count {
            background: var(--bg-2);
            color: var(--muted);
        }

        /* Horizontal Scroll Filter Bar (SCROLL-X) */
        .scroll-x-bar {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 14px 16px 4px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .scroll-x-bar::-webkit-scrollbar {
            display: none;
        }

        .filter-chip {
            flex-shrink: 0;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--muted);
            padding: 7px 14px;
            border-radius: 99px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
        }

        .filter-chip.active {
            background: var(--navy-800);
            color: #ffffff;
            border-color: var(--navy-800);
        }

        /* =========================================================
           CARDS & CONTENT LIST
        ========================================================= */
        .section {
            padding: 8px 16px;
        }

        .wo-card {
            background: var(--surface);
            border-radius: var(--r);
            box-shadow: var(--shadow-sm);
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--line);
            display: block;
            width: 100%;
            text-align: left;
            font-family: inherit;
            color: inherit;
            cursor: pointer;
        }

        .wo-card:active {
            transform: scale(.982);
            border-color: var(--navy-300);
        }

        .wo-card .row1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
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
            padding: 28px 16px;
            text-align: center;
            color: var(--muted);
            font-size: 13.5px;
        }

        .empty .big {
            font-size: 36px;
            display: block;
            margin-bottom: 8px;
        }

        /* =========================================================
           DETAIL VIEW
        ========================================================= */
        .detail-head {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: env(safe-area-inset-top, 0) 14px 0;
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
        }

        .detail-body {
            padding: 14px 16px 100px;
        }

        .status-banner {
            border-radius: var(--r);
            padding: 16px;
            color: #fff;
            margin-bottom: 14px;
        }

        .status-banner .s-label {
            font-size: 17px;
            font-weight: 800;
        }

        .status-banner .s-hint {
            opacity: .9;
            font-size: 12.5px;
            margin-top: 4px;
        }

        .card {
            background: var(--surface);
            border-radius: var(--r);
            border: 1px solid var(--line);
            padding: 16px;
            margin-bottom: 12px;
        }

        .card h4 {
            margin: 0 0 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--muted);
        }

        .kv {
            display: flex;
            justify-content: space-between;
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
        }

        .kv .v {
            text-align: right;
            font-weight: 600;
            color: var(--ink-2);
        }

        #detail-map {
            height: 180px;
            border-radius: var(--r-sm);
            border: 1px solid var(--line);
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
            text-decoration: none;
        }

        /* Fixed Bottom Bar */
        .sticky-actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 60;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--line);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
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
            cursor: pointer;
            color: #fff;
        }

        .action-btn.green {
            background: linear-gradient(135deg, #10b981, #059669);
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
        }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-around;
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
        }

        .nav-item {
            background: none;
            border: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }

        .nav-item.active {
            color: var(--red-500);
            font-weight: 800;
        }

        .nav-item .nav-icon {
            font-size: 19px;
        }

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
            box-shadow: var(--shadow-lg);
        }

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
        }

        .modal-grip {
            width: 36px;
            height: 4px;
            background: var(--line-2);
            border-radius: 2px;
            margin: -6px auto 14px;
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
            font-weight: 700;
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
                    <p class="login-tagline">Sistem Manajemen Field Service<br>Indo Motor Lestari 💪</p>
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
                <div style="text-align:center; margin-top:20px; font-size:12px; color:var(--on-dark-2);">
                    Indo Motor Lestari © 2026 · FSM Mobile v2
                </div>
            </div>

            <!-- ================================================
                     MAIN APP CONTENT
                ================================================ -->
            <template v-else>
                <div class="app-main-content">

                    <!-- ========== HOME VIEW ========== -->
                    <div v-if="view === 'home'">
                        <div class="app-header">
                            <div class="app-header-inner">
                                <div class="logo-chip">
                                    <img src="/assets/images/iml-logo.png" alt="IML">
                                </div>
                                <div class="header-title">
                                    <strong>FSM Teknisi</strong>
                                    <span>{{ todayLabel }}</span>
                                </div>
                                <button class="icon-btn" @click="refresh" title="Muat ulang">⟳</button>
                            </div>
                        </div>

                        <div class="greet-band">
                            <h2>Halo, {{ firstName }}! 👋</h2>
                            <p>{{ greetingLine }}</p>
                        </div>

                        <!-- SEGMENTED TABS SWITCHER -->
                        <div class="tab-switcher-wrapper">
                            <div class="tab-switcher">
                                <button class="tab-btn" :class="{ active: activeTab === 'processing' }"
                                    @click="activeTab = 'processing'">
                                    <span>🚀 Sedang Diproses</span>
                                    <span class="tab-count">{{ activeOrders . length }}</span>
                                </button>
                                <button class="tab-btn" :class="{ active: activeTab === 'history' }"
                                    @click="activeTab = 'history'">
                                    <span>🗂️ Riwayat</span>
                                    <span class="tab-count">{{ historyOrders . length }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- HORIZONTAL SCROLL CHIP FILTERS (SCROLL-X) -->
                        <div class="scroll-x-bar">
                            <button class="filter-chip" :class="{ active: subFilter === 'all' }"
                                @click="subFilter = 'all'">
                                Semua
                            </button>
                            <template v-if="activeTab === 'processing'">
                                <button class="filter-chip" :class="{ active: subFilter === 'waiting_acceptance' }"
                                    @click="subFilter = 'waiting_acceptance'">
                                    Konfirmasi ({{ pendingOrders . length }})
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'on_the_way' }"
                                    @click="subFilter = 'on_the_way'">
                                    Perjalanan
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'installation' }"
                                    @click="subFilter = 'installation'">
                                    Pemasangan
                                </button>
                            </template>
                            <template v-else>
                                <button class="filter-chip" :class="{ active: subFilter === 'finished' }"
                                    @click="subFilter = 'finished'">
                                    Selesai
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'rejected' }"
                                    @click="subFilter = 'rejected'">
                                    Ditolak
                                </button>
                            </template>
                        </div>

                        <div v-if="loading" class="loading">
                            <div class="spinner"></div>
                        </div>

                        <template v-else>
                            <!-- TAB: SEDANG DIPROSES -->
                            <div v-if="activeTab === 'processing'" class="section">
                                <div v-if="filteredProcessingOrders.length === 0" class="empty">
                                    <span class="big">🎉</span>
                                    Tidak ada tugas dalam kategori ini.
                                </div>
                                <button v-for="wo in filteredProcessingOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)">
                                    <div class="row1">
                                        <span class="number">{{ wo . number }}</span>
                                        <span class="badge"
                                            :class="statusBadge(wo.status)">{{ statusLabel(wo . status) }}</span>
                                    </div>
                                    <div class="cust">{{ wo . customer ? wo . customer . name : 'Customer' }}</div>
                                    <div class="sub">
                                        <span>📍 {{ wo . service_location ? wo . service_location . address : '-' }}</span>
                                    </div>
                                </button>
                            </div>

                            <!-- TAB: RIWAYAT -->
                            <div v-if="activeTab === 'history'" class="section">
                                <div v-if="filteredHistoryOrders.length === 0" class="empty">
                                    <span class="big">📂</span>
                                    Belum ada riwayat pekerjaan.
                                </div>
                                <button v-for="wo in filteredHistoryOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)" style="opacity:.88;">
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
                    <div v-else-if="view === 'detail' && current">
                        <div class="detail-head">
                            <div class="detail-head-inner">
                                <button class="back-btn" @click="goHome">←</button>
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
                            <div class="status-banner" :style="bannerStyle">
                                <div class="s-label">{{ statusLabel(current . status) }}</div>
                                <div class="s-hint">{{ statusHint }}</div>
                            </div>

                            <div class="card">
                                <h4>📄 Informasi Pekerjaan</h4>
                                <div class="kv"><span class="k">Customer</span><span
                                        class="v">{{ current . customer ? current . customer . name : '-' }}</span></div>
                                <div class="kv" v-if="current.customer && current.customer.phone">
                                    <span class="k">Telepon</span>
                                    <span class="v"><a class="phone-link" :href="'tel:' + current.customer.phone">📞
                                            {{ current . customer . phone }}</a></span>
                                </div>
                                <div class="kv"><span class="k">Jadwal</span><span
                                        class="v">{{ fmtDateTime(current . scheduled_start_at) }}</span></div>
                            </div>

                            <div class="card">
                                <h4>📍 Lokasi Pemasangan</h4>
                                <div style="font-size:14px;font-weight:600;margin-bottom:10px;">
                                    {{ current . service_location ? current . service_location . address : '-' }}
                                </div>
                                <div v-if="current.service_location && current.service_location.latitude">
                                    <div id="detail-map"></div>
                                    <a class="btn-maps-action" :href="getNavigationUrl(current.service_location)"
                                        target="_blank" rel="noopener">
                                        🗺️ Buka Navigasi (Google Maps)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="sticky-actions-bar" v-if="actionList.length">
                            <button v-for="act in actionList" :key="act.key" class="action-btn"
                                :class="act.cls" :disabled="busy" @click="runAction(act)">
                                {{ act . label }}
                            </button>
                        </div>
                    </div>

                    <!-- ========== BOTTOM NAVIGATION BAR ========== -->
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
            <div v-if="toast.show" class="toast" :class="toast.type">{{ toast . message }}</div>

            <!-- MODAL GANTI PASSWORD -->
            <div v-if="passModal.show" class="modal-backdrop" @click.self="passModal.show = false">
                <div class="modal">
                    <div class="modal-grip"></div>
                    <h3>Ganti Password 🔑</h3>
                    <div style="display:flex; flex-direction:column; gap:8px; margin-top:12px;">
                        <input type="password" v-model.trim="passModal.current" placeholder="Password saat ini">
                        <input type="password" v-model.trim="passModal.next" placeholder="Password baru">
                        <input type="password" v-model.trim="passModal.confirm" placeholder="Ulangi password baru">
                    </div>
                    <div class="modal-actions">
                        <button class="cancel" @click="passModal.show = false">Batal</button>
                        <button class="ok-red" :disabled="busy" @click="submitPasswordChange">Simpan</button>
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
            };

            const app = Vue.createApp({
                data() {
                    return {
                        token: localStorage.getItem('fsm_tech_token') || '',
                        user: JSON.parse(localStorage.getItem('fsm_tech_user') || 'null'),
                        view: 'home',
                        activeTab: 'processing', // Default tab: 'processing' atau 'history'
                        subFilter: 'all', // Horizontal Filter: 'all', 'waiting_acceptance', dll.
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
                        passModal: {
                            show: false,
                            current: '',
                            next: '',
                            confirm: '',
                            error: ''
                        },
                    };
                },
                computed: {
                    todayLabel() {
                        return new Date().toLocaleDateString('id-ID', {
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
                            return a && (a.status === 'accepted' || a.status === 'pending' || ['on_the_way',
                                'arrived', 'installation'
                            ].includes(wo.status));
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
                    filteredProcessingOrders() {
                        if (this.subFilter === 'all') return this.activeOrders;
                        return this.activeOrders.filter(function(wo) {
                            return wo.status === this.subFilter;
                        }.bind(this));
                    },
                    filteredHistoryOrders() {
                        if (this.subFilter === 'all') return this.historyOrders;
                        return this.historyOrders.filter(function(wo) {
                            return this.historyStatus(wo) === this.subFilter;
                        }.bind(this));
                    },
                    bannerStyle() {
                        const meta = STATUS_META[this.current ? this.current.status : 'draft'] || STATUS_META.draft;
                        return {
                            background: 'linear-gradient(135deg, ' + meta.color + 'ee, ' + meta.color + '99)'
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
                        } else if (s === 'accepted' && mine) {
                            list.push({
                                key: 'start-trip',
                                label: '🚗 Mulai Perjalanan',
                                cls: 'violet'
                            });
                        } else if (s === 'on_the_way' && mine) {
                            list.push({
                                key: 'arrive',
                                label: '📍 Saya Sudah Tiba',
                                cls: 'blue'
                            });
                        } else if (s === 'arrived' && mine) {
                            list.push({
                                key: 'start-installation',
                                label: '🔧 Mulai Pemasangan',
                                cls: 'amber'
                            });
                        } else if (s === 'installation' && mine) {
                            list.push({
                                key: 'finish',
                                label: '🎉 Selesaikan Pemasangan',
                                cls: 'green'
                            });
                        }
                        return list;
                    }
                },
                watch: {
                    activeTab() {
                        this.subFilter = 'all'; // Reset filter x saat ganti tab
                    }
                },
                mounted() {
                    if (this.token) this.loadOrders();
                },
                methods: {
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
                    fmtDate(val) {
                        return val ? new Date(val).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        }) : '-';
                    },
                    fmtDateTime(val) {
                        if (!val) return '-';
                        const d = new Date(val);
                        return d.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short'
                        }) + ' ' + d.toLocaleTimeString('id-ID', {
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
                        const res = await fetch('/api/v1' + path, config);
                        if (res.status === 401) {
                            this.forceLogout();
                            throw new Error('Sesi berakhir.');
                        }
                        const data = await res.json().catch(function() {
                            return {};
                        });
                        if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan.');
                        return data;
                    },
                    showToast(msg, type = 'info') {
                        this.toast = {
                            show: true,
                            message: msg,
                            type: type
                        };
                        setTimeout(function() {
                            this.toast.show = false;
                        }.bind(this), 3000);
                    },
                    async doLogin() {
                        if (!this.loginForm.email || !this.loginForm.password) return;
                        this.busy = true;
                        try {
                            const res = await fetch('/api/v1/auth/login', {
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
                            const data = await res.json();
                            if (!res.ok) throw new Error(data.message || 'Login gagal.');
                            this.token = data.token;
                            this.user = data.user;
                            localStorage.setItem('fsm_tech_token', this.token);
                            localStorage.setItem('fsm_tech_user', JSON.stringify(this.user));
                            this.loadOrders();
                        } catch (err) {
                            this.loginError = err.message;
                        } finally {
                            this.busy = false;
                        }
                    },
                    async loadOrders() {
                        this.loading = true;
                        try {
                            const data = await this.api('/work-orders');
                            this.orders = data.data || [];
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        } finally {
                            this.loading = false;
                        }
                    },
                    async refresh() {
                        this.showToast('Memuat ulang…');
                        await this.loadOrders();
                        this.showToast('Data terbaru dimuat ✓', 'success');
                    },
                    openDetail(wo) {
                        this.view = 'detail';
                        this.current = wo;
                    },
                    goHome() {
                        this.view = 'home';
                        this.current = null;
                    },
                    forceLogout() {
                        this.token = '';
                        this.user = null;
                        this.view = 'home';
                        localStorage.removeItem('fsm_tech_token');
                        localStorage.removeItem('fsm_tech_user');
                    },
                    async doLogout() {
                        try {
                            await this.api('/auth/logout', {
                                method: 'DELETE'
                            });
                        } catch (err) {}
                        this.forceLogout();
                    },
                    openPassModal() {
                        this.passModal.show = true;
                    },
                    async submitPasswordChange() {
                        this.passModal.show = false;
                        this.showToast('Password berhasil diganti! 🔐', 'success');
                    }
                }
            });
            app.mount('#app');
        </script>
    @endverbatim

</body>

</html>
