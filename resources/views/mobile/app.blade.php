@verbatim
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b1f4b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/mobile/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/images/iml-logo.png">
    <title>FSM Teknisi</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root {
            --navy: #0b1f4b;
            --navy-2: #16347f;
            --navy-grad: linear-gradient(135deg, #0b1f4b 0%, #16347f 100%);
            --red: #d00202;
            --red-dark: #a30000;
            --red-grad: linear-gradient(135deg, #e11d1d 0%, #a30000 100%);
            --bg: #f4f6fb;
            --card: #ffffff;
            --ink: #101828;
            --muted: #667085;
            --line: #e6eaf2;
            --green: #16a34a;
            --amber: #d97706;
            --sky: #0284c7;
            --violet: #7c3aed;
            --rose: #e11d48;
            --shadow: 0 6px 20px rgba(11, 31, 75, .08);
            --radius: 16px;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, sans-serif;
            background: var(--bg); color: var(--ink); font-size: 15px;
        }
        [v-cloak] { display: none; }
        #app { max-width: 480px; margin: 0 auto; min-height: 100vh; position: relative; }
        .muted { color: var(--muted); }
        .small { font-size: 13px; }
        .hidden { display: none !important; }

        /* ---------- Login ---------- */
        .login-screen {
            min-height: 100vh; display: flex; flex-direction: column;
            background: radial-gradient(1200px 500px at 50% -10%, #1c3a8a 0%, var(--navy) 55%, #071231 100%);
            color: #fff; padding: 44px 24px 32px;
        }
        .login-logo { display: flex; justify-content: center; margin-bottom: 10px; }
        .login-logo img { background: #fff; border-radius: 14px; padding: 10px 14px; height: 54px; width: auto; }
        .login-screen h1 { text-align: center; font-size: 24px; margin: 14px 0 4px; }
        .login-screen .tagline { text-align: center; color: #b9c7ea; font-size: 14px; margin-bottom: 28px; }
        .login-card { background: #fff; color: var(--ink); border-radius: 20px; padding: 22px; box-shadow: 0 18px 45px rgba(0,0,0,.35); }
        .login-card label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin: 12px 0 6px; }
        .login-card input {
            width: 100%; padding: 13px 14px; border: 1.5px solid var(--line); border-radius: 12px;
            font-size: 15px; background: #f8fafc; outline: none;
        }
        .login-card input:focus { border-color: var(--red); background: #fff; }
        .btn-login {
            width: 100%; margin-top: 18px; border: 0; border-radius: 12px; padding: 14px;
            background: var(--red-grad); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer;
            letter-spacing: .2px;
        }
        .btn-login:disabled { opacity: .6; }
        .login-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 10px; padding: 10px 12px; font-size: 13px; margin-top: 14px; }
        .login-foot { text-align: center; margin-top: 22px; color: #8fa3cf; font-size: 12px; }

        /* ---------- App shell ---------- */
        .app-header {
            position: sticky; top: 0; z-index: 30;
            background: var(--navy-grad); color: #fff; padding: 14px 16px 16px;
            border-radius: 0 0 24px 24px;
            display: flex; align-items: center; gap: 12px;
        }
        .app-header .logo-chip { background: #fff; border-radius: 10px; padding: 5px 8px; flex-shrink: 0; }
        .app-header .logo-chip img { height: 26px; display: block; }
        .app-header .title { flex: 1; min-width: 0; }
        .app-header .title strong { display: block; font-size: 15px; line-height: 1.2; }
        .app-header .title span { font-size: 12px; color: #b9c7ea; }
        .icon-btn {
            background: rgba(255,255,255,.12); border: 0; color: #fff; width: 40px; height: 40px;
            border-radius: 12px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .icon-btn:active { background: rgba(255,255,255,.22); }

        .greet { background: var(--navy-grad); color: #fff; margin: -6px 0 0; padding: 4px 18px 18px; }
        .greet h2 { margin: 0; font-size: 20px; }
        .greet p { margin: 4px 0 0; color: #b9c7ea; font-size: 13px; }

        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 14px 16px; margin-top: -18px; }
        .stat-card {
            background: var(--card); border-radius: 14px; padding: 14px; box-shadow: var(--shadow);
            border-left: 4px solid var(--amber); position: relative; overflow: hidden;
        }
        .stat-card.red { border-left-color: var(--red); }
        .stat-card.green { border-left-color: var(--green); }
        .stat-card .num { font-size: 26px; font-weight: 800; }
        .stat-card .lbl { font-size: 12px; color: var(--muted); margin-top: 2px; }

        .section { padding: 0 16px 8px; }
        .section h3 { font-size: 13px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin: 18px 4px 10px; display: flex; align-items: center; gap: 6px; }
        .wo-card {
            background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 14px 16px; margin-bottom: 12px; cursor: pointer; border: 1px solid var(--line);
            display: block; width: 100%; text-align: left; font-family: inherit; color: inherit;
        }
        .wo-card:active { transform: scale(.99); }
        .wo-card .row1 { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .wo-card .number { font-weight: 800; font-size: 15px; }
        .badge {
            display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px;
            font-size: 11.5px; font-weight: 700; white-space: nowrap;
        }
        .b-amber { background: #fef3c7; color: #92400e; }
        .b-green { background: #dcfce7; color: #166534; }
        .b-sky { background: #e0f2fe; color: #075985; }
        .b-violet { background: #ede9fe; color: #5b21b6; }
        .b-blue { background: #dbeafe; color: #1e40af; }
        .b-indigo { background: #e0e7ff; color: #3730a3; }
        .b-rose { background: #ffe4e6; color: #9f1239; }
        .b-red { background: #fee2e2; color: #991b1b; }
        .b-gray { background: #e2e8f0; color: #475569; }
        .wo-card .cust { font-weight: 600; margin-top: 6px; }
        .wo-card .sub { font-size: 12.5px; color: var(--muted); margin-top: 4px; display: flex; align-items: center; gap: 5px; }
        .empty {
            background: var(--card); border: 1.5px dashed #cbd5e1; border-radius: var(--radius);
            padding: 26px 16px; text-align: center; color: var(--muted); font-size: 13.5px;
        }
        .empty .big { font-size: 34px; display: block; margin-bottom: 6px; }

        /* ---------- Detail ---------- */
        .detail-head {
            position: sticky; top: 0; z-index: 30; background: var(--navy-grad); color: #fff;
            padding: 12px 14px; display: flex; align-items: center; gap: 10px;
            border-radius: 0 0 22px 22px;
        }
        .detail-head .num { font-size: 15px; font-weight: 800; }
        .detail-head .sub { font-size: 12px; color: #b9c7ea; }
        .detail-body { padding: 16px; padding-bottom: 40px; }
        .status-banner { border-radius: var(--radius); padding: 16px; color: #fff; margin-bottom: 14px; box-shadow: var(--shadow); }
        .status-banner .big { font-size: 17px; font-weight: 800; }
        .status-banner .small { opacity: .9; font-size: 12.5px; margin-top: 3px; }
        .card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--line); padding: 16px; margin-bottom: 14px; }
        .card h4 { margin: 0 0 10px; font-size: 13px; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
        .kv { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px dashed #eef1f7; font-size: 14px; }
        .kv:last-child { border-bottom: 0; }
        .kv .k { color: var(--muted); flex-shrink: 0; }
        .kv .v { text-align: right; font-weight: 600; }
        .phone-link { color: var(--red); font-weight: 700; text-decoration: none; }
        #detail-map { height: 200px; border-radius: 12px; border: 1px solid var(--line); z-index: 1; }
        .map-note { font-size: 12px; color: var(--muted); margin-top: 8px; display: flex; align-items: center; gap: 6px; }

        .stepper { display: flex; justify-content: space-between; margin: 4px 2px 2px; }
        .step { flex: 1; text-align: center; position: relative; }
        .step .dot { width: 26px; height: 26px; margin: 0 auto; border-radius: 50%; background: #eef1f7; border: 2px solid #d3dae7; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--muted); position: relative; z-index: 1; }
        .step .lbl { font-size: 10.5px; color: var(--muted); margin-top: 5px; }
        .step.done .dot { background: var(--green); border-color: var(--green); color: #fff; }
        .step.active .dot { background: var(--red); border-color: var(--red); color: #fff; box-shadow: 0 0 0 4px rgba(208,2,2,.15); }
        .step.active .lbl { color: var(--red); font-weight: 700; }
        .step::before { content: ""; position: absolute; top: 13px; right: 50%; width: 100%; height: 2px; background: #e6eaf2; z-index: 0; }
        .step:first-child::before { display: none; }
        .step.done::before, .step.active::before { background: var(--green); }

        .actions { display: grid; gap: 10px; }
        .action-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
            border: 0; border-radius: 14px; padding: 15px; font-size: 15px; font-weight: 800; cursor: pointer;
            color: #fff; box-shadow: var(--shadow);
        }
        .action-btn:disabled { opacity: .55; }
        .action-btn.green { background: linear-gradient(135deg, #22c55e, #15803d); }
        .action-btn.red { background: var(--red-grad); }
        .action-btn.violet { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .action-btn.blue { background: linear-gradient(135deg, #0ea5e9, #0369a1); }
        .action-btn.amber { background: linear-gradient(135deg, #f59e0b, #b45309); }
        .action-btn.ghost { background: #fff; color: var(--rose); border: 1.5px solid #fecdd3; box-shadow: none; }
        .gps-pill {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 700; margin-bottom: 10px;
        }
        .gps-on { background: #dcfce7; color: #166534; }
        .gps-off { background: #fee2e2; color: #991b1b; }
        .pulse { width: 8px; height: 8px; border-radius: 50%; background: currentColor; animation: pulse 1.2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

        .timeline { list-style: none; margin: 0; padding: 0; }
        .timeline li { position: relative; padding: 0 0 16px 22px; font-size: 13.5px; }
        .timeline li::before { content: ""; position: absolute; left: 5px; top: 5px; width: 10px; height: 10px; border-radius: 50%; background: var(--red); }
        .timeline li::after { content: ""; position: absolute; left: 9px; top: 20px; bottom: 0; width: 2px; background: #e6eaf2; }
        .timeline li:last-child::after { display: none; }
        .timeline .when { color: var(--muted); font-size: 12px; }

        /* ---------- Toast & modal ---------- */
        .toast {
            position: fixed; top: 14px; left: 50%; transform: translateX(-50%); z-index: 90;
            background: var(--navy); color: #fff; padding: 11px 18px; border-radius: 12px;
            font-size: 13.5px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,.3);
            max-width: 90vw; text-align: center;
        }
        .toast.error { background: #991b1b; }
        .toast.success { background: #166534; }
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(7,18,49,.55); z-index: 80;
            display: flex; align-items: flex-end; justify-content: center; backdrop-filter: blur(2px);
        }
        .modal {
            background: #fff; width: 100%; max-width: 480px; border-radius: 22px 22px 0 0;
            padding: 22px 20px calc(24px + env(safe-area-inset-bottom)); box-shadow: 0 -10px 40px rgba(0,0,0,.2);
        }
        .modal h3 { margin: 0 0 4px; font-size: 17px; }
        .modal .desc { color: var(--muted); font-size: 13px; margin-bottom: 14px; }
        .chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip {
            border: 1.5px solid var(--line); background: #f8fafc; border-radius: 999px; padding: 8px 13px;
            font-size: 13px; cursor: pointer; color: var(--ink);
        }
        .chip.selected { border-color: var(--red); background: #fef2f2; color: var(--red); font-weight: 700; }
        .modal textarea {
            width: 100%; border: 1.5px solid var(--line); border-radius: 12px; padding: 12px; font-size: 14px;
            font-family: inherit; resize: none; outline: none; min-height: 74px;
        }
        .modal textarea:focus { border-color: var(--red); }
        .modal-actions { display: flex; gap: 10px; margin-top: 14px; }
        .modal-actions button {
            flex: 1; border: 0; border-radius: 12px; padding: 13px; font-size: 14.5px; font-weight: 700; cursor: pointer;
        }
        .modal-actions .cancel { background: #eef1f7; color: var(--muted); }
        .modal-actions .ok-red { background: var(--red-grad); color: #fff; }
        .modal-actions .ok-green { background: linear-gradient(135deg, #22c55e, #15803d); color: #fff; }
        .mfield { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin: 12px 0 6px; }
        .modal input[type="password"] {
            width: 100%; padding: 12px 13px; border: 1.5px solid var(--line); border-radius: 12px;
            font-size: 14px; background: #f8fafc; outline: none;
        }
        .modal input[type="password"]:focus { border-color: var(--red); background: #fff; }
        .modal-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 10px; padding: 9px 11px; font-size: 12.5px; margin-top: 10px; }

        .loading { display: flex; justify-content: center; padding: 40px 0; }
        .spinner { width: 30px; height: 30px; border: 3.5px solid #dbe4f5; border-top-color: var(--red); border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .bottom-safe { height: env(safe-area-inset-bottom); }
    </style>
</head>
<body>
<div id="app" v-cloak>
    <!-- ============ LOGIN ============ -->
    <div v-if="!token" class="login-screen">
        <div class="login-logo"><img src="/assets/images/iml-logo.png" alt="IML"></div>
        <h1>FSM Teknisi</h1>
        <p class="tagline">Semangat kerja hari ini! 💪<br>Siap jadi pahlawan pemasangan?</p>

        <div class="login-card">
            <label>Email</label>
            <input type="email" v-model.trim="loginForm.email" placeholder="nama@indomotorlestari.co.id" autocomplete="username">
            <label>Password</label>
            <input type="password" v-model="loginForm.password" placeholder="••••••••" autocomplete="current-password" @keyup.enter="doLogin">
            <button class="btn-login" :disabled="busy" @click="doLogin">
                {{ busy ? 'Memeriksa akun...' : 'Masuk' }}
            </button>
            <div v-if="loginError" class="login-error">{{ loginError }}</div>
        </div>
        <div class="login-foot">Indo Motor Lestari © 2026</div>
    </div>

    <!-- ============ APP ============ -->
    <template v-else>
        <!-- HOME -->
        <div v-if="view === 'home'">
            <div class="app-header">
                <div class="logo-chip"><img src="/assets/images/iml-logo.png" alt="IML"></div>
                <div class="title">
                    <strong>FSM Teknisi</strong>
                    <span>{{ todayLabel }}</span>
                </div>
                <button class="icon-btn" @click="refresh" title="Muat ulang">⟳</button>
                <button class="icon-btn" @click="openPassModal" title="Ganti password">🔑</button>
                <button class="icon-btn" @click="doLogout" title="Keluar">⎋</button>
            </div>
            <div class="greet">
                <h2>Halo, {{ firstName }}! 👋</h2>
                <p>{{ greetingLine }}</p>
            </div>
            <div class="stats">
                <div class="stat-card red">
                    <div class="num">{{ pendingOrders.length }}</div>
                    <div class="lbl">Menunggu konfirmasi</div>
                </div>
                <div class="stat-card green">
                    <div class="num">{{ activeOrders.length }}</div>
                    <div class="lbl">Sedang diproses</div>
                </div>
            </div>

            <div v-if="loading" class="loading"><div class="spinner"></div></div>

            <template v-else>
                <div class="section">
                    <h3>🔔 Menunggu konfirmasi</h3>
                    <div v-if="pendingOrders.length === 0" class="empty"><span class="big">🎉</span>Tidak ada pekerjaan baru. Santai dulu, tugas akan segera datang!</div>
                    <button v-for="wo in pendingOrders" :key="wo.id" class="wo-card" @click="openDetail(wo)">
                        <div class="row1">
                            <span class="number">{{ wo.number }}</span>
                            <span class="badge b-amber">Konfirmasi</span>
                        </div>
                        <div class="cust">{{ wo.customer ? wo.customer.name : 'Customer' }}</div>
                        <div class="sub">📅 {{ fmtDate(wo.scheduled_start_at) }} &nbsp;·&nbsp; 👥 Untukmu</div>
                    </button>
                </div>

                <div class="section">
                    <h3>🚀 Sedang diproses</h3>
                    <div v-if="activeOrders.length === 0" class="empty"><span class="big">😴</span>Belum ada pekerjaan berjalan.</div>
                    <button v-for="wo in activeOrders" :key="wo.id" class="wo-card" @click="openDetail(wo)">
                        <div class="row1">
                            <span class="number">{{ wo.number }}</span>
                            <span class="badge" :class="statusBadge(wo.status)">{{ statusLabel(wo.status) }}</span>
                        </div>
                        <div class="cust">{{ wo.customer ? wo.customer.name : 'Customer' }}</div>
                        <div class="sub">
                            📍 {{ wo.service_location ? wo.service_location.address : '-' }}
                            <span v-if="superseded(wo)"> · Diambil teknisi lain</span>
                        </div>
                    </button>
                </div>
            </template>
        </div>

        <!-- DETAIL -->
        <div v-else-if="view === 'detail' && current">
            <div class="detail-head">
                <button class="icon-btn" @click="goHome">←</button>
                <div>
                    <div class="num">{{ current.number }}</div>
                    <div class="sub">{{ current.work_type || 'Pemasangan' }}</div>
                </div>
            </div>

            <div class="detail-body">
                <div class="status-banner" :style="bannerStyle">
                    <div class="big">{{ statusLabel(current.status) }}</div>
                    <div class="small">{{ statusHint }}</div>
                </div>

                <div class="card">
                    <h4>Progres Pemasangan</h4>
                    <div class="stepper">
                        <div v-for="(step, i) in steps" :key="i" class="step" :class="{ done: i < stepIndex, active: i === stepIndex }">
                            <div class="dot">{{ i < stepIndex ? '✓' : i + 1 }}</div>
                            <div class="lbl">{{ step }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h4>Informasi Pekerjaan</h4>
                    <div class="kv"><span class="k">Customer</span><span class="v">{{ current.customer ? current.customer.name : '-' }}</span></div>
                    <div class="kv" v-if="current.customer && current.customer.phone">
                        <span class="k">Telepon</span>
                        <span class="v"><a class="phone-link" :href="'tel:' + current.customer.phone">{{ current.customer.phone }}</a></span>
                    </div>
                    <div class="kv"><span class="k">Jadwal</span><span class="v">{{ fmtDateTime(current.scheduled_start_at) }}</span></div>
                    <div class="kv" v-if="current.notes"><span class="k">Catatan</span><span class="v">{{ current.notes }}</span></div>
                    <div class="kv" v-if="current.items && current.items.length">
                        <span class="k">Item</span>
                        <span class="v">{{ current.items.map(i => i.product_name).join(', ') }}</span>
                    </div>
                </div>

                <div class="card">
                    <h4>📍 Lokasi Pemasangan</h4>
                    <div style="font-size:14px; font-weight:600; margin-bottom:10px;">{{ current.service_location ? current.service_location.address : '-' }}</div>
                    <div v-if="current.service_location && current.service_location.latitude">
                        <div id="detail-map"></div>
                        <div class="map-note">
                            <span class="pulse" style="color:var(--red)"></span>
                            Pin merah = lokasi tujuan. Gunakan aplikasi navigasi untuk petunjuk jalan.
                        </div>
                    </div>
                    <div v-else class="muted small">Koordinat lokasi belum diatur oleh koordinator.</div>
                </div>

                <div class="card" v-if="actionList.length">
                    <h4>Aksi</h4>
                    <div class="actions">
                        <button v-for="act in actionList" :key="act.key" class="action-btn" :class="act.cls" :disabled="busy" @click="runAction(act)">
                            {{ act.label }}
                        </button>
                    </div>
                </div>

                <div v-if="current.status === 'on_the_way'" class="card">
                    <h4>Lokasi Langsung</h4>
                    <span class="gps-pill" :class="gpsState === 'active' ? 'gps-on' : 'gps-off'">
                        <span class="pulse"></span>
                        {{ gpsState === 'active' ? 'Lokasi dikirim otomatis · ' + (gpsSentLabel || 'baru saja') : (gpsState === 'error' ? 'GPS bermasalah, cek izin lokasi' : 'Menyiapkan GPS...') }}
                    </span>
                    <p class="muted small" style="margin:0;">Pelanggan bisa memantau posisimu lewat link tracking. Pastikan GPS HP menyala ya! 🙏</p>
                </div>

                <div class="card" v-if="current.status_histories && current.status_histories.length">
                    <h4>Riwayat Status</h4>
                    <ul class="timeline">
                        <li v-for="h in current.status_histories" :key="h.id">
                            <b>{{ statusLabel(h.to_status) }}</b>
                            <div class="when">{{ fmtDateTime(h.occurred_at) }}<span v-if="h.reason"> — {{ h.reason }}</span></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </template>

    <!-- TOAST -->
    <div v-if="toast.show" class="toast" :class="toast.type">{{ toast.message }}</div>

    <!-- MODAL ALASAN -->
    <div v-if="modal.show" class="modal-backdrop" @click.self="modal.show = false">
        <div class="modal">
            <h3>{{ modal.title }}</h3>
            <p class="desc">{{ modal.desc }}</p>
            <div class="chip-row">
                <button v-for="chip in modal.chips" :key="chip" class="chip" :class="{ selected: modal.reason === chip }" @click="modal.reason = chip">
                    {{ chip }}
                </button>
            </div>
            <textarea v-model="modal.reason" placeholder="Atau tulis alasanmu..." rows="2"></textarea>
            <div class="modal-actions">
                <button class="cancel" @click="modal.show = false">Batal</button>
                <button class="ok-red" :disabled="busy || !modal.reason.trim()" @click="submitReason">
                    {{ busy ? 'Mengirim...' : 'Kirim' }}
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL GANTI PASSWORD -->
    <div v-if="passModal.show" class="modal-backdrop" @click.self="passModal.show = false">
        <div class="modal">
            <h3>Ganti Password 🔑</h3>
            <p class="desc">Password awal teknisi adalah <b>12345</b>. Segera ganti dengan password pribadimu ya!</p>
            <label class="mfield">Password saat ini</label>
            <input type="password" v-model.trim="passModal.current" placeholder="Password lama" autocomplete="current-password">
            <label class="mfield">Password baru (min. 6 karakter)</label>
            <input type="password" v-model.trim="passModal.next" placeholder="Password baru" autocomplete="new-password">
            <label class="mfield">Ulangi password baru</label>
            <input type="password" v-model.trim="passModal.confirm" placeholder="Ulangi password baru" autocomplete="new-password" @keyup.enter="submitPasswordChange">
            <div v-if="passModal.error" class="modal-error">{{ passModal.error }}</div>
            <div class="modal-actions">
                <button class="cancel" @click="passModal.show = false">Batal</button>
                <button class="ok-red" :disabled="busy" @click="submitPasswordChange">
                    {{ busy ? 'Menyimpan...' : 'Simpan Password' }}
                </button>
            </div>
        </div>
    </div>
    <div class="bottom-safe"></div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const STATUS_META = {
        draft:              { label: 'Draf',                 color: '#64748b' },
        waiting_acceptance: { label: 'Menunggu Konfirmasi',  color: '#d97706' },
        accepted:           { label: 'Diterima',             color: '#0284c7' },
        on_the_way:         { label: 'Dalam Perjalanan',     color: '#7c3aed' },
        arrived:            { label: 'Sudah Tiba',           color: '#0369a1' },
        installation:       { label: 'Sedang Pemasangan',    color: '#4f46e5' },
        finished:           { label: 'Selesai',              color: '#16a34a' },
        rejected:           { label: 'Ditolak',              color: '#e11d48' },
        cancelled:          { label: 'Dibatalkan',           color: '#64748b' },
        failed:             { label: 'Gagal',                color: '#991b1b' },
    };
    const BADGE_CLASS = {
        waiting_acceptance: 'b-amber', accepted: 'b-sky', on_the_way: 'b-violet',
        arrived: 'b-blue', installation: 'b-indigo', finished: 'b-green',
        rejected: 'b-rose', cancelled: 'b-gray', failed: 'b-red', draft: 'b-gray',
    };
    const HINTS = {
        waiting_acceptance: 'Konfirmasi dulu ya, apakah kamu bisa mengerjakan tugas ini?',
        accepted: 'Pekerjaan sudah kamu terima. Siapkan perjalananmu!',
        on_the_way: 'GPS aktif — pelanggan bisa melihat posisimu. Hati-hati di jalan! 🛵',
        arrived: 'Kamu sudah di lokasi. Kabari kalau mulai pemasangan.',
        installation: 'Fokus kerjakan yang terbaik. Pelanggan menantikan hasilnya! 🔧',
        finished: 'Kerja bagus! Pekerjaan selesai dengan baik. 👏',
        rejected: 'Kamu menolak pekerjaan ini. Koordinator akan segera menindaklanjuti.',
        cancelled: 'Pekerjaan dibatalkan oleh koordinator.',
        failed: 'Pekerjaan terkendala. Tim koordinator akan menindaklanjuti.',
        draft: 'Pekerjaan masih disiapkan koordinator.',
    };

    const app = Vue.createApp({
        data() {
            return {
                token: localStorage.getItem('fsm_tech_token') || '',
                user: null,
                view: 'home',
                loginForm: { email: '', password: '' },
                loginError: '',
                busy: false,
                loading: true,
                orders: [],
                current: null,
                toast: { show: false, message: '', type: 'info' },
                modal: { show: false, mode: 'reject', reason: '', title: '', desc: '', chips: [] },
                passModal: { show: false, current: '', next: '', confirm: '', error: '' },
                gpsState: 'off',
                gpsSentLabel: '',
                watchId: null,
                gpsTimer: null,
                lastPos: null,
                pollTimer: null,
                mapInstance: null,
                mapMarker: null,
                mapPosMarker: null,
            };
        },
        computed: {
            todayLabel() {
                const d = new Date();
                return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
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
                return this.orders.filter(wo => {
                    const a = this.myAssignment(wo);
                    return a && a.status === 'pending' && wo.status === 'waiting_acceptance';
                });
            },
            activeOrders() {
                return this.orders.filter(wo => {
                    const a = this.myAssignment(wo);
                    return a && (a.status === 'accepted' || ['on_the_way', 'arrived', 'installation'].includes(wo.status));
                });
            },
            steps() {
                return ['Diterima', 'Berangkat', 'Tiba', 'Pasang', 'Selesai'];
            },
            stepIndex() {
                const map = { accepted: 0, on_the_way: 1, arrived: 2, installation: 3, finished: 4 };
                return this.current ? (map[this.current.status] ?? -1) : -1;
            },
            bannerStyle() {
                const meta = STATUS_META[this.current ? this.current.status : 'draft'] || STATUS_META.draft;
                return { background: 'linear-gradient(135deg, ' + meta.color + ', ' + meta.color + 'cc)' };
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
                    list.push({ key: 'accept', label: '✅ Terima Pekerjaan', cls: 'green' });
                    list.push({ key: 'reject', label: 'Tolak (isi alasan)', cls: 'ghost' });
                } else if (s === 'accepted' && mine) {
                    list.push({ key: 'start-trip', label: '🚗 Mulai Perjalanan', cls: 'violet' });
                    list.push({ key: 'fail', label: '⚠️ Laporkan Kendala', cls: 'ghost' });
                } else if (s === 'on_the_way' && mine) {
                    list.push({ key: 'arrive', label: '📍 Saya Sudah Tiba', cls: 'blue' });
                    list.push({ key: 'fail', label: '⚠️ Laporkan Kendala', cls: 'ghost' });
                } else if (s === 'arrived' && mine) {
                    list.push({ key: 'start-installation', label: '🔧 Mulai Pemasangan', cls: 'amber' });
                    list.push({ key: 'fail', label: '⚠️ Laporkan Kendala', cls: 'ghost' });
                } else if (s === 'installation' && mine) {
                    list.push({ key: 'finish', label: '🎉 Selesaikan Pemasangan', cls: 'green' });
                    list.push({ key: 'fail', label: '⚠️ Laporkan Kendala', cls: 'ghost' });
                }
                return list;
            },
        },
        mounted() {
            if (this.token) {
                this.loadOrders();
                this.pollTimer = setInterval(() => this.loadOrders(true), 45000);
            }
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/mobile/sw.js').catch(() => {});
            }
        },
        methods: {
            myAssignment(wo) {
                if (!wo || !wo.assignments || !this.user) return null;
                return wo.assignments.find(a => a.technician_id === this.user.technician_id) || null;
            },
            superseded(wo) {
                const a = this.myAssignment(wo);
                return a && a.status === 'superseded';
            },
            statusLabel(status) { return (STATUS_META[status] || STATUS_META.draft).label; },
            statusBadge(status) { return BADGE_CLASS[status] || 'b-gray'; },
            fmtDate(value) {
                if (!value) return '-';
                return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            },
            fmtDateTime(value) {
                if (!value) return '-';
                const d = new Date(value);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                    + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },
            async api(path, options = {}) {
                const config = {
                    method: options.method || 'GET',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                };
                if (this.token) config.headers.Authorization = 'Bearer ' + this.token;
                if (options.body) config.body = JSON.stringify(options.body);
                const response = await fetch('/api/v1' + path, config);
                if (response.status === 401) { this.forceLogout(); throw new Error('Sesi berakhir, silakan masuk lagi.'); }
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const message = data.message
                        || Object.values(data.errors || {}).flat()[0]
                        || 'Terjadi kesalahan. Coba lagi.';
                    throw new Error(message);
                }
                return data;
            },
            showToast(message, type = 'info') {
                this.toast = { show: true, message, type };
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => { this.toast.show = false; }, 3200);
            },
            async doLogin() {
                if (!this.loginForm.email || !this.loginForm.password) {
                    this.loginError = 'Email dan password wajib diisi.';
                    return;
                }
                this.busy = true;
                this.loginError = '';
                try {
                    const data = await fetch('/api/v1/auth/login', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email: this.loginForm.email, password: this.loginForm.password, device_name: 'fsm-mobile-web' }),
                    }).then(async r => { const j = await r.json().catch(() => ({})); if (!r.ok) throw new Error(j.message || 'Login gagal.'); return j; });
                    this.token = data.token;
                    this.user = data.user;
                    localStorage.setItem('fsm_tech_token', this.token);
                    localStorage.setItem('fsm_tech_user', JSON.stringify(this.user));
                    this.view = 'home';
                    this.loadOrders();
                    this.pollTimer = setInterval(() => this.loadOrders(true), 45000);
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
                        this.user = me;
                        localStorage.setItem('fsm_tech_user', JSON.stringify(me));
                    }
                } catch (err) {
                    if (!silent) this.showToast(err.message, 'error');
                } finally {
                    this.loading = false;
                }
            },
            async refresh() {
                this.showToast('Memuat ulang...');
                await this.loadOrders();
                this.showToast('Data terbaru sudah dimuat ✓', 'success');
            },
            async openDetail(wo) {
                this.view = 'detail';
                this.current = wo;
                try {
                    const data = await this.api('/work-orders/' + wo.id);
                    this.current = data;
                    this.$nextTick(() => this.initMap());
                } catch (err) {
                    this.showToast(err.message, 'error');
                }
            },
            goHome() {
                this.view = 'home';
                this.stopGps();
                this.destroyMap();
                this.current = null;
            },
            async runAction(act) {
                if (act.key === 'accept') {
                    this.busy = true;
                    try {
                        const a = this.myAssignment(this.current);
                        await this.api('/assignments/' + a.id + '/accept', { method: 'POST' });
                        this.showToast('Pekerjaan diterima. Gas! 🔥', 'success');
                        await this.reloadDetail();
                    } catch (err) { this.showToast(err.message, 'error'); }
                    finally { this.busy = false; }
                    return;
                }
                if (act.key === 'reject') {
                    this.modal = { show: true, mode: 'reject', reason: '', title: 'Tolak Pekerjaan?', desc: 'Kasih tahu koordinator alasannya, biar bisa dicarikan solusi.', chips: ['Jadwal bentrok', 'Lokasi terlalu jauh', 'Sedang ada pekerjaan lain', 'Sakit / izin'] };
                    return;
                }
                if (act.key === 'fail') {
                    this.modal = { show: true, mode: 'fail', reason: '', title: 'Laporkan Kendala', desc: 'Jelaskan kendalanya, tim koordinator akan segera tindak lanjut.', chips: ['Customer tidak di tempat', 'Akses lokasi terhambat', 'Kendala teknis', 'Kendaraan bermasalah'] };
                    return;
                }
                if (act.key === 'finish') {
                    if (!confirm('Yakin pekerjaan ini sudah selesai? 🙌')) return;
                }
                this.busy = true;
                try {
                    await this.api('/work-orders/' + this.current.id + '/' + act.key, { method: 'POST' });
                    this.showToast('Berhasil! 🎉', 'success');
                    await this.reloadDetail();
                } catch (err) { this.showToast(err.message, 'error'); }
                finally { this.busy = false; }
            },
            async submitReason() {
                const reason = this.modal.reason.trim();
                if (!reason || this.busy) return;
                this.busy = true;
                try {
                    if (this.modal.mode === 'reject') {
                        const a = this.myAssignment(this.current);
                        await this.api('/assignments/' + a.id + '/reject', { method: 'POST', body: { reason } });
                        this.showToast('Pekerjaan ditolak. Terima kasih atas kejujurannya.', 'success');
                    } else {
                        await this.api('/work-orders/' + this.current.id + '/fail', { method: 'POST', body: { reason } });
                        this.showToast('Kendala sudah dilaporkan ke koordinator.', 'success');
                    }
                    this.modal.show = false;
                    await this.reloadDetail();
                    await this.loadOrders(true);
                } catch (err) { this.showToast(err.message, 'error'); }
                finally { this.busy = false; }
            },
            async reloadDetail() {
                if (!this.current) return;
                try {
                    const data = await this.api('/work-orders/' + this.current.id);
                    this.current = data;
                    if (data.status === 'on_the_way') this.startGps(); else this.stopGps();
                    this.$nextTick(() => this.initMap());
                    await this.loadOrders(true);
                } catch (err) { this.showToast(err.message, 'error'); }
            },
            activeSession() {
                if (!this.current || !this.current.tracking_sessions) return null;
                return this.current.tracking_sessions.find(s => s.status === 'active') || null;
            },
            startGps() {
                if (this.watchId !== null) return;
                if (!('geolocation' in navigator)) { this.gpsState = 'error'; return; }
                this.gpsState = 'starting';
                this.watchId = navigator.geolocation.watchPosition(
                    (pos) => {
                        this.lastPos = pos;
                        this.gpsState = 'active';
                        this.sendLocation();
                    },
                    () => { this.gpsState = 'error'; },
                    { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 },
                );
                this.gpsTimer = setInterval(() => {
                    if (this.lastPos && this.current && this.current.status === 'on_the_way') this.sendLocation();
                }, 15000);
            },
            async sendLocation() {
                const session = this.activeSession();
                if (!session || !this.lastPos) return;
                const pos = this.lastPos.coords;
                try {
                    await this.api('/tracking-sessions/' + session.id + '/locations', {
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
                    this.gpsSentLabel = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                } catch (err) { /* lokasi dikirim ulang pada interval berikutnya */ }
            },
            stopGps() {
                if (this.watchId !== null) { navigator.geolocation.clearWatch(this.watchId); this.watchId = null; }
                if (this.gpsTimer) { clearInterval(this.gpsTimer); this.gpsTimer = null; }
                this.gpsState = 'off';
                this.lastPos = null;
            },
            initMap() {
                if (typeof L === 'undefined') return;
                const loc = this.current && this.current.service_location;
                const el = document.getElementById('detail-map');
                if (!loc || !loc.latitude || !el) return;
                this.destroyMap();
                const lat = parseFloat(loc.latitude), lng = parseFloat(loc.longitude);
                this.mapInstance = L.map('detail-map').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19, attribution: '&copy; OpenStreetMap',
                }).addTo(this.mapInstance);
                this.mapMarker = L.marker([lat, lng]).addTo(this.mapInstance);
                if (this.lastPos && this.lastPos.coords) {
                    const c = this.lastPos.coords;
                    this.mapPosMarker = L.circleMarker([c.latitude, c.longitude], { radius: 8, color: '#d00202', fillColor: '#d00202', fillOpacity: .35 }).addTo(this.mapInstance);
                }
            },
            destroyMap() {
                if (this.mapInstance) { this.mapInstance.remove(); this.mapInstance = null; }
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
                try { await this.api('/auth/logout', { method: 'DELETE' }); } catch (err) { /* tetap logout lokal */ }
                this.forceLogout();
                this.showToast('Sampai jumpa! 👋');
            },
            openPassModal() {
                this.passModal = { show: true, current: '', next: '', confirm: '', error: '' };
            },
            async submitPasswordChange() {
                const p = this.passModal;
                if (!p.current || !p.next || !p.confirm) { p.error = 'Semua kolom wajib diisi.'; return; }
                if (p.next !== p.confirm) { p.error = 'Password baru tidak sama dengan ulangannya.'; return; }
                if (p.next.length < 6) { p.error = 'Password baru minimal 6 karakter.'; return; }
                this.busy = true;
                p.error = '';
                try {
                    await this.api('/auth/change-password', {
                        method: 'POST',
                        body: {
                            current_password: p.current,
                            new_password: p.next,
                            new_password_confirmation: p.confirm,
                        },
                    });
                    p.show = false;
                    this.showToast('Password berhasil diganti. Simpan baik-baik ya! 🔐', 'success');
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
</body>
</html>
@endverbatim
