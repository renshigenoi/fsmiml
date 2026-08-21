<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b2044">
    <meta name="description" content="Pantau posisi teknisi Indo Motor Lestari secara real-time">
    <title>Live Tracking Pemasangan — Indo Motor Lestari</title>
    <link rel="shortcut icon" href="/assets/images/icon.png" type="image/x-icon" />
    <link rel="manifest" href="/mobile/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/images/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* =========================================================
           DESIGN SYSTEM — IML Live Tracking
        ========================================================= */
        :root {
            --navy-900: #061429;
            --navy-800: #0b2044;
            --navy-700: #112b5c;
            --navy-600: #1a3a7a;
            --navy-300: #6a9ae8;
            --navy-100: #dce9fc;

            --red-700: #8b0c1e;
            --red-500: #c8102e;
            --red-400: #e01836;
            --red-100: #ffe4e9;

            --brand-grad: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 60%, var(--navy-600) 100%);
            --red-grad: linear-gradient(135deg, var(--red-400), var(--red-700));

            --bg: #f0f4fb;
            --surface: #ffffff;
            --surface-2: #f6f9ff;
            --ink: #0d1b35;
            --ink-2: #2c3e65;
            --muted: #64748b;
            --line: #e2e8f4;
            --line-2: #cbd5e8;
            --green: #059669;
            --green-bg: #d1fae5;
            --violet: #7c3aed;
            --vlt-bg: #ede9fe;

            --shadow-sm: 0 1px 4px rgba(11, 32, 68, .06);
            --shadow: 0 4px 16px rgba(11, 32, 68, .10);
            --shadow-lg: 0 10px 32px rgba(11, 32, 68, .14);
            --shadow-xl: 0 20px 56px rgba(11, 32, 68, .20);

            --r-sm: 10px;
            --r: 16px;
            --r-lg: 22px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);

            --on-dark: #ffffff;
            --on-dark-2: rgba(255, 255, 255, .65);
            --on-dark-3: rgba(255, 255, 255, .40);
            --glass: rgba(255, 255, 255, .08);
            --glass-bdr: rgba(255, 255, 255, .14);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink);
            font-size: 15px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        #app {
            width: 100%;
            min-height: 100vh;
        }

        /* =========================================================
           HEADER
        ========================================================= */
        .track-header {
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: env(safe-area-inset-top, 0) 20px 0;
            position: relative;
            overflow: hidden;
        }

        .track-header::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200, 16, 46, .15) 0%, transparent 70%);
            top: -150px;
            right: -100px;
            pointer-events: none;
        }

        .track-header-inner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 0 20px;
            position: relative;
            z-index: 1;
        }

        .track-logo {
            background: #fff;
            border-radius: 12px;
            padding: 8px 12px;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .22);
        }

        .track-logo img {
            height: 32px;
            display: block;
        }

        .track-header-text {
            flex: 1;
        }

        .track-header-text h1 {
            margin: 0 0 3px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.3px;
        }

        .track-header-text p {
            margin: 0;
            color: var(--on-dark-2);
            font-size: 13px;
        }

        .live-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(200, 16, 46, .25);
            border: 1px solid rgba(200, 16, 46, .40);
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 700;
            color: #ffb3bc;
            flex-shrink: 0;
        }

        /* =========================================================
           BODY / CONTENT
        ========================================================= */
        .track-body {
            padding: 18px 16px 40px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Status card */
        .status-card {
            border-radius: var(--r);
            padding: 20px 18px;
            color: #fff;
            margin-bottom: 16px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            top: -70px;
            right: -50px;
            pointer-events: none;
        }

        .status-card .s-big {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.2px;
        }

        .status-card .s-small {
            opacity: .88;
            font-size: 13px;
            margin-top: 5px;
            line-height: 1.4;
        }

        /* Info card */
        .card {
            background: var(--surface);
            border-radius: var(--r);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--line);
            padding: 18px;
            margin-bottom: 14px;
        }

        .card h4 {
            margin: 0 0 14px;
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
            border-bottom: 1px solid var(--surface-2);
            font-size: 14px;
        }

        .kv:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .kv .k {
            color: var(--muted);
            flex-shrink: 0;
            font-size: 13px;
        }

        .kv .v {
            text-align: right;
            font-weight: 600;
            color: var(--ink-2);
        }

        /* Map */
        #track-map {
            height: 240px;
            border-radius: var(--r-sm);
            border: 1px solid var(--line);
            z-index: 1;
            overflow: hidden;
        }

        /* Marker posisi teknisi: ikon motor agar mudah dibedakan dari titik tujuan. */
        .motorcycle-marker {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--red-grad);
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(139, 12, 30, .38);
        }

        .motorcycle-marker svg {
            width: 25px;
            height: 25px;
            fill: none;
            stroke: #fff;
            stroke-width: 2.1;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .map-note {
            font-size: 12px;
            color: var(--muted);
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .eta-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(5, 150, 105, .08);
            border: 1px solid rgba(5, 150, 105, .25);
            border-radius: 12px;
            padding: 10px 12px;
            margin-top: 10px;
        }

        .eta-icon {
            font-size: 18px;
        }

        .eta-line {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ink);
        }

        .eta-sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* =========================================================
           STEPPER
        ========================================================= */
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
            width: 28px;
            height: 28px;
            margin: 0 auto;
            border-radius: 50%;
            background: var(--surface-2);
            border: 2px solid var(--line-2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--muted);
            position: relative;
            z-index: 1;
            transition: background .3s, border-color .3s, box-shadow .3s;
        }

        .step .lbl {
            font-size: 10px;
            color: var(--muted);
            margin-top: 5px;
            font-weight: 500;
        }

        .step.done .dot {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

        .step.active .dot {
            background: var(--violet);
            border-color: var(--violet);
            color: #fff;
            box-shadow: 0 0 0 5px rgba(124, 58, 237, .15);
        }

        .step.active .lbl {
            color: var(--violet);
            font-weight: 700;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 13px;
            right: 50%;
            width: 100%;
            height: 2px;
            background: var(--line);
            z-index: 0;
            transition: background .3s;
        }

        .step:first-child::before {
            display: none;
        }

        .step.done::before,
        .step.active::before {
            background: var(--green);
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
        }

        .b-violet {
            background: var(--vlt-bg);
            color: #5b21b6;
        }

        .b-green {
            background: var(--green-bg);
            color: #065f46;
        }

        .b-rose {
            background: #ffe4e6;
            color: #9f1239;
        }

        .b-gray {
            background: #e2e8f0;
            color: #475569;
        }

        .b-navy {
            background: var(--navy-100);
            color: var(--navy-700);
        }

        /* Pulse */
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

        /* =========================================================
           STATE SCREENS
        ========================================================= */
        .state-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            padding: 40px 24px;
            text-align: center;
        }

        .state-screen .big-icon {
            font-size: 56px;
            display: block;
            margin-bottom: 16px;
        }

        .state-screen .s-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--ink);
            margin: 0 0 8px;
        }

        .state-screen .s-desc {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            max-width: 300px;
        }

        /* Loading spinner */
        .spinner {
            width: 36px;
            height: 36px;
            border: 3.5px solid var(--line);
            border-top-color: var(--red-500);
            border-radius: 50%;
            animation: spin .75s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Footer */
        .track-footer {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            padding: 0 16px 24px;
        }

        .track-footer a {
            color: var(--navy-300);
            text-decoration: none;
        }

        /* Utility */
        .hidden {
            display: none;
        }

        .muted {
            color: var(--muted);
        }

        .small {
            font-size: 12.5px;
        }
    </style>
</head>

<body>
    <div id="app">

        <!-- ===== HEADER ===== -->
        <div class="track-header">
            <div class="track-header-inner">
                <div class="track-logo">
                    <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                </div>
                <div class="track-header-text">
                    <h1>Live Tracking</h1>
                    <p>Pantau posisi teknisi secara real-time</p>
                </div>
                <div class="live-pill" id="live-pill">
                    <span class="pulse" style="color:#ff6b7a"></span>
                    LIVE
                </div>
            </div>
        </div>

        <!-- ===== BODY ===== -->
        <div class="track-body">

            <!-- Loading -->
            <div id="state-loading" class="state-screen">
                <div class="spinner"></div>
                <div class="s-title">Memuat data…</div>
                <p class="s-desc">Sedang mengambil informasi tracking teknisi.</p>
            </div>

            <!-- Invalid / expired -->
            <div id="state-invalid" class="state-screen hidden">
                <span class="big-icon">😕</span>
                <div class="s-title">Link tracking tidak aktif</div>
                <p class="s-desc">Link sudah tidak berlaku atau sesi tracking telah berakhir. Hubungi tim Indo Motor
                    Lestari bila butuh bantuan.</p>
            </div>

            <!-- Active tracking -->
            <div id="state-active" class="hidden">

                <!-- Status banner -->
                <div id="status-card" class="status-card">
                    <div id="status-big" class="s-big">Teknisi dalam perjalanan 🚗</div>
                    <div id="status-small" class="s-small">Menuju lokasi pemasangan Anda</div>
                </div>

                <!-- Progress stepper -->
                <div class="card">
                    <h4>📊 Progres Pemasangan</h4>
                    <div class="stepper">
                        <div class="step done">
                            <div class="dot">✓</div>
                            <div class="lbl">Dipesan</div>
                        </div>
                        <div id="st-berangkat" class="step">
                            <div class="dot">2</div>
                            <div class="lbl">Berangkat</div>
                        </div>
                        <div id="st-tiba" class="step">
                            <div class="dot">3</div>
                            <div class="lbl">Tiba</div>
                        </div>
                        <div id="st-pasang" class="step">
                            <div class="dot">4</div>
                            <div class="lbl">Pasang</div>
                        </div>
                        <div id="st-selesai" class="step">
                            <div class="dot">5</div>
                            <div class="lbl">Selesai</div>
                        </div>
                    </div>
                </div>

                <!-- Live map -->
                <div class="card">
                    <h4>📍 Lokasi Teknisi</h4>
                    <div id="track-map"></div>
                    <div class="eta-bar" id="eta-bar">
                        <span class="eta-icon">🛵</span>
                        <div>
                            <div class="eta-line" id="eta-line">Menghitung perkiraan tiba...</div>
                            <div class="eta-sub" id="eta-sub"></div>
                        </div>
                    </div>
                    <div class="map-note" id="map-note">
                        <span class="pulse" style="color:var(--red-500)"></span>
                        <span>Posisi diperbarui otomatis &nbsp;<span id="last-updated"
                                style="font-weight:600;">-</span></span>
                    </div>
                </div>

                <!-- Trip summary -->
                <div class="card" id="trip-summary-card" style="display:none;">
                    <h4>🧾 Ringkasan Perjalanan</h4>
                    <div class="kv">
                        <span class="k">Berangkat</span>
                        <span class="v" id="ts-start">-</span>
                    </div>
                    <div class="kv">
                        <span class="k">Tiba di lokasi</span>
                        <span class="v" id="ts-arrive">-</span>
                    </div>
                    <div class="kv" id="ts-finish-row">
                        <span class="k">Selesai pemasangan</span>
                        <span class="v" id="ts-finish">-</span>
                    </div>
                    <div class="kv">
                        <span class="k">Jarak · Durasi</span>
                        <span class="v" id="ts-distance">-</span>
                    </div>
                </div>

                <!-- Work order info -->
                <div class="card">
                    <h4>📄 Informasi Pemasangan</h4>
                    <div class="kv">
                        <span class="k">Nomor SPK</span>
                        <span class="v" id="wo-number">-</span>
                    </div>
                    <div class="kv">
                        <span class="k">Jadwal</span>
                        <span class="v" id="wo-date">-</span>
                    </div>
                    <div class="kv">
                        <span class="k">Alamat</span>
                        <span class="v" id="wo-address" style="text-align:left;font-weight:500;">-</span>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="track-footer">
                <span>Indo Motor Lestari © 2026</span><br>
                <span>Layanan Pemasangan Profesional</span>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @php
        $reverbOptions = config('broadcasting.connections.reverb.options') ?? [];
        $wsHost = (string) ($reverbOptions['host'] ?? '');
        if (in_array($wsHost, ['localhost', '127.0.0.1'], true)) {
            $wsHost = request()->getHost();
        }
        $trackingRealtime = [
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => $wsHost,
            'port' => (int) ($reverbOptions['port'] ?? 8080),
            'scheme' => (string) ($reverbOptions['scheme'] ?? 'http'),
        ];
    @endphp
    <script src="{{ asset('assets/vendor/pusher.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/echo.iife.js') }}"></script>
    <script>
        window.FSM_TRACKING_REALTIME = @json($trackingRealtime);
    </script>
    <script>
        const TOKEN = @json($token);
        const API_URL = '/api/v1/public/tracking/' + encodeURIComponent(TOKEN);
        const STATUS_META = {
            on_the_way: {
                big: 'Teknisi dalam perjalanan 🚗',
                small: 'Menuju lokasi pemasangan Anda',
                color: '#7c3aed',
            },
            arrived: {
                big: 'Teknisi sudah tiba di lokasi 📍',
                small: 'Teknisi sedang mempersiapkan peralatan',
                color: '#0369a1',
            },
            installation: {
                big: 'Pemasangan sedang berlangsung 🔧',
                small: 'Teknisi sedang mengerjakan pemasangan Anda',
                color: '#4f46e5',
            },
            finished: {
                big: 'Pemasangan selesai ✅',
                small: 'Terima kasih telah mempercayakan kami! Semoga memuaskan.',
                color: '#059669',
            },
            cancelled: {
                big: 'Pemasangan dibatalkan',
                small: 'Hubungi tim kami bila ada pertanyaan.',
                color: '#64748b',
            },
            failed: {
                big: 'Pemasangan terkendala ⚠️',
                small: 'Tim kami akan segera menghubungi Anda.',
                color: '#991b1b',
            },
        };

        let map = null,
            destMarker = null,
            posMarker = null,
            posAccuracy = null,
            pollTimer = null,
            subscribed = false,
            lastRealtime = 0,
            tripLayer = null,
            destLatLng = null,
            routeLayer = null,
            lastRouteFetch = 0,
            routeDistanceM = null,
            routeDurationS = null,
            lastSpeedMps = null,
            lastKnownLat = null,
            lastKnownLng = null,
            hasCenteredOnTechnician = false;

        const MOTORCYCLE_ICON = L.divIcon({
            className: 'motorcycle-marker-wrap',
            html: '<div class="motorcycle-marker" aria-label="Posisi teknisi"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="5.5" cy="17" r="2.5"></circle><circle cx="18.5" cy="17" r="2.5"></circle><path d="M8 17h3l2-6h3l2.5 6"></path><path d="M10.5 11 9 8h4l1 3"></path><path d="M13 8h3"></path><path d="M15.5 8 17 6"></path><path d="M7.5 17 10 12"></path></svg></div>',
            iconSize: [44, 44],
            iconAnchor: [22, 22],
        });

        function initTrackingRealtime(data) {
            if (subscribed || !data.realtime_channel || typeof Echo === 'undefined') return;
            const cfg = window.FSM_TRACKING_REALTIME || null;
            if (!cfg || !cfg.key) return;
            try {
                window.TrackingEcho = new Echo({
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
                window.TrackingEcho.channel('tracking.' + data.realtime_channel)
                    .listen('.tracking.location.updated', (payload) => {
                        lastRealtime = Date.now();
                        updatePosition(payload);
                    });
                subscribed = true;
            } catch (err) {
                console.error('Tracking realtime gagal:', err);
            }
        }

        function haversineM(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const toRad = d => d * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) ** 2 +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
            return 2 * R * Math.asin(Math.sqrt(a));
        }

        async function fetchRoute(lat, lng) {
            if (!destLatLng || !lat || !lng) return;
            const now = Date.now();
            if (now - lastRouteFetch < 60000) return;
            lastRouteFetch = now;
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 10000);
            try {
                const url = 'https://router.project-osrm.org/route/v1/driving/' +
                    lng + ',' + lat + ';' + destLatLng.lng + ',' + destLatLng.lat +
                    '?overview=full&geometries=geojson&steps=false';
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal,
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (data.code !== 'Ok' || !data.routes || !data.routes.length) throw new Error('no route');
                const route = data.routes[0];
                routeDistanceM = route.distance;
                routeDurationS = route.duration;
                drawRoute(route.geometry.coordinates);
                renderEta();
            } catch (_) {
                if (routeDistanceM === null) {
                    routeDistanceM = haversineM(lat, lng, destLatLng.lat, destLatLng.lng);
                    routeDurationS = routeDistanceM / (30 * 1000 / 3600);
                }
                renderEta();
            } finally {
                clearTimeout(timer);
            }
        }

        function drawRoute(coords) {
            if (!map || !coords || !coords.length) return;
            if (routeLayer) map.removeLayer(routeLayer);
            routeLayer = L.polyline(coords.map(c => [c[1], c[0]]), {
                color: '#2563eb',
                weight: 4,
                opacity: .75,
            }).addTo(map);
        }

        function renderEta() {
            const line = document.getElementById('eta-line');
            const sub = document.getElementById('eta-sub');
            if (!line) return;
            if (routeDurationS === null) {
                line.textContent = lastKnownLat ?
                    'Menghitung perkiraan tiba...' :
                    'Menunggu posisi teknisi...';
                sub.textContent = '';
                return;
            }
            const eta = new Date(Date.now() + routeDurationS * 1000);
            const mins = Math.max(1, Math.round(routeDurationS / 60));
            line.textContent = 'Perkiraan tiba ' +
                eta.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) +
                ' WIB (± ' + mins + ' mnt)';
            const parts = [];
            if (routeDistanceM !== null) {
                parts.push(routeDistanceM >= 1000 ?
                    'Jarak ± ' + (routeDistanceM / 1000).toFixed(1) + ' km' :
                    'Jarak ± ' + Math.round(routeDistanceM) + ' m');
            }
            if (lastSpeedMps && lastSpeedMps > 0) {
                parts.push('Kecepatan ' + Math.round(lastSpeedMps * 3.6) + ' km/jam');
                if (routeDurationS > 0 && routeDistanceM > 0) {
                    const expected = routeDistanceM / routeDurationS;
                    const ratio = lastSpeedMps / expected;
                    const condition = ratio < 0.35 ?
                        'terlihat macet' :
                        (ratio < 0.75 ? 'lalu lintas padat' : 'lalu lintas lancar');
                    parts.push('Kondisi: ' + condition);
                }
            }
            sub.textContent = parts.join(' · ');
        }

        function showLoading() {
            document.getElementById('state-loading').classList.remove('hidden');
            document.getElementById('state-invalid').classList.add('hidden');
            document.getElementById('state-active').classList.add('hidden');
            document.getElementById('live-pill').classList.add('hidden');
        }

        function showInvalid() {
            stopPolling();
            document.getElementById('state-loading').classList.add('hidden');
            document.getElementById('state-invalid').classList.remove('hidden');
            document.getElementById('state-active').classList.add('hidden');
            document.getElementById('live-pill').classList.add('hidden');
        }

        function showActive() {
            document.getElementById('state-loading').classList.add('hidden');
            document.getElementById('state-invalid').classList.add('hidden');
            document.getElementById('state-active').classList.remove('hidden');
            document.getElementById('live-pill').classList.remove('hidden');
        }

        async function load() {
            try {
                const res = await fetch(API_URL, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (res.status === 404) {
                    showInvalid();
                    return;
                }
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                render(data);
            } catch (err) {
                showInvalid();
            }
        }

        function render(data) {
            const meta = STATUS_META[data.status] || STATUS_META.on_the_way;

            // Status banner
            document.getElementById('status-big').textContent = meta.big;
            document.getElementById('status-small').textContent = meta.small;
            document.getElementById('status-card').style.background =
                'linear-gradient(135deg, ' + meta.color + 'f0, ' + meta.color + '99)';

            // Stepper
            const stepDone = {
                finished: ['berangkat', 'tiba', 'pasang', 'selesai'],
                installation: ['berangkat', 'tiba', 'pasang'],
                arrived: ['berangkat', 'tiba'],
                on_the_way: ['berangkat']
            };
            const stepActive = {
                on_the_way: 'berangkat',
                arrived: 'tiba',
                installation: 'pasang',
                finished: 'selesai'
            };

            document.querySelectorAll('#state-active .step').forEach(s => s.classList.remove('active', 'done'));

            const done = stepDone[data.status] || [];
            done.forEach(key => {
                const el = document.getElementById('st-' + key);
                if (el) {
                    el.classList.add('done');
                    el.querySelector('.dot').textContent = '✓';
                }
            });
            const activeKey = stepActive[data.status];
            if (activeKey) {
                const el = document.getElementById('st-' + activeKey);
                if (el) el.classList.add('active');
            }

            // Info
            document.getElementById('wo-number').textContent = data.work_order ? data.work_order.number : '-';
            document.getElementById('wo-date').textContent = data.work_order && data.work_order.scheduled_start_at ?
                new Date(data.work_order.scheduled_start_at).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) :
                '-';
            document.getElementById('wo-address').textContent = data.destination && data.destination.address ?
                data.destination.address : '-';

            // Map & location
            if (data.status === 'on_the_way') {
                showActive();
                initMap(data.destination);
                updatePosition(data.current_location);
                showEtaBar(true);
                renderTripSummary(null);
            } else {
                stopPolling();
                showActive();
                const livePill = document.getElementById('live-pill');
                if (livePill) livePill.classList.add('hidden');
                if (data.destination) initMap(data.destination, true);
                showEtaBar(false);
                drawTripPath(data.trip_points);
                renderTripSummary(data.trip_summary);
                renderArrivedNote(data);
            }

            initTrackingRealtime(data);
        }

        function showEtaBar(show) {
            const bar = document.getElementById('eta-bar');
            if (bar) bar.style.display = show ? 'flex' : 'none';
        }

        function drawTripPath(points) {
            if (!map || !points || points.length < 2) return;
            if (tripLayer) map.removeLayer(tripLayer);
            tripLayer = L.polyline(points.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]), {
                color: '#64748b',
                weight: 3,
                opacity: .7,
                dashArray: '4 6',
            }).addTo(map);
            map.fitBounds(tripLayer.getBounds().pad(0.25), { maxZoom: 16 });
        }

        function renderTripSummary(summary) {
            const card = document.getElementById('trip-summary-card');
            if (!card) return;
            if (!summary) {
                card.style.display = 'none';
                return;
            }
            card.style.display = '';
            const fmt = iso => iso ?
                new Date(iso).toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: 'Asia/Jakarta',
                }) :
                '-';
            document.getElementById('ts-start').textContent = fmt(summary.started_at);
            document.getElementById('ts-arrive').textContent = fmt(summary.arrived_at);
            document.getElementById('ts-finish').textContent = fmt(summary.finished_at);
            const dist = summary.distance_m >= 1000 ?
                (summary.distance_m / 1000).toFixed(1) + ' km' :
                Math.round(summary.distance_m) + ' m';
            const mins = Math.round(summary.duration_s / 60);
            const dur = mins >= 60 ?
                Math.floor(mins / 60) + ' jam ' + (mins % 60) + ' mnt' :
                mins + ' mnt';
            document.getElementById('ts-distance').textContent = dist + ' · ' + dur;
        }

        function renderArrivedNote(data) {
            const note = document.getElementById('map-note');
            if (!note) return;
            if (data.status === 'on_the_way') {
                return;
            }
            if (data.status === 'arrived' || data.status === 'installation') {
                const arrived = data.trip_summary && data.trip_summary.arrived_at;
                const t = arrived
                    ? new Date(arrived).toLocaleTimeString('id-ID', {
                        hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta'
                    })
                    : null;
                note.innerHTML = t
                    ? '<span style="color:#059669;font-weight:700;">✅ Teknisi sudah tiba pada ' + t + ' WIB.</span>'
                    : '<span style="color:var(--muted)">✅ Teknisi sudah tiba di lokasi.</span>';
                return;
            }
            if (data.status === 'finished') {
                const finished = data.trip_summary && data.trip_summary.finished_at;
                const t = finished
                    ? new Date(finished).toLocaleTimeString('id-ID', {
                        hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta'
                    })
                    : null;
                note.innerHTML = t
                    ? '<span style="color:#059669;font-weight:700;">✅ Pemasangan selesai pada ' + t + ' WIB.</span>'
                    : '<span style="color:var(--muted)">✅ Pemasangan selesai.</span>';
            }
        }

        function initMap(dest, finalize) {
            if (typeof L === 'undefined') return;
            const el = document.getElementById('track-map');
            if (!el || (map && map.getContainer() === el)) return;
            if (map) map.remove();
            const hasDest = dest && dest.latitude && dest.longitude;
            const center = hasDest ? [parseFloat(dest.latitude), parseFloat(dest.longitude)] : [-2.5489, 118.0149];
            map = L.map('track-map').setView(center, hasDest ? 14 : 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);
            if (hasDest) {
                destMarker = L.marker([parseFloat(dest.latitude), parseFloat(dest.longitude)]).addTo(map);
                destLatLng = {
                    lat: parseFloat(dest.latitude),
                    lng: parseFloat(dest.longitude),
                };
            }
            if (finalize) {
                document.getElementById('map-note').innerHTML =
                    '<span style="color:var(--muted)">📍 Posisi teknisi tidak lagi dikirimkan.</span>';
            }
        }

        function updatePosition(location) {
            const note = document.getElementById('map-note');
            if (!location || !location.latitude || !location.longitude) {
                note.innerHTML =
                    '<span class="pulse" style="color:var(--red-500)"></span> <span>Menunggu sinyal lokasi teknisi…</span>';
                return;
            }
            const lat = parseFloat(location.latitude),
                lng = parseFloat(location.longitude);
            if (!map) initMap(null);
            if (!map) return;
            if (posMarker) {
                posMarker.setLatLng([lat, lng]);
            } else {
                posMarker = L.marker([lat, lng], { icon: MOTORCYCLE_ICON, zIndexOffset: 1000 })
                    .addTo(map)
                    .bindTooltip('Posisi teknisi', { direction: 'top', offset: [0, -22] });
            }
            if (location.accuracy_meters && posAccuracy) {
                posAccuracy.setLatLng([lat, lng]).setRadius(parseFloat(location.accuracy_meters));
            }
            if (location.accuracy_meters && !posAccuracy) {
                posAccuracy = L.circle([lat, lng], {
                    radius: parseFloat(location.accuracy_meters),
                    color: '#c8102e',
                    fillColor: '#c8102e',
                    fillOpacity: .08,
                    weight: 1,
                }).addTo(map);
            }
            // Mulai dari posisi teknisi dengan zoom detail, lalu ikuti setiap titik baru.
            // `panTo` mempertahankan zoom pengguna dan tidak kembali zoom-out ke tujuan.
            if (!hasCenteredOnTechnician) {
                map.setView([lat, lng], 18, { animate: false });
                hasCenteredOnTechnician = true;
            } else {
                map.panTo([lat, lng], { animate: true, duration: 0.45 });
            }
            const stamp = location.recorded_at || location.received_at;
            const timeStr = stamp ?
                new Date(stamp).toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: 'Asia/Jakarta',
                }) + ' WIB' :
                '-';
            note.innerHTML = `<span class="pulse" style="color:var(--red-500)"></span>
            <span>Posisi diperbarui otomatis &nbsp;<strong>${timeStr}</strong></span>`;

            lastSpeedMps = location.speed_mps || null;
            lastKnownLat = lat;
            lastKnownLng = lng;
            if (destLatLng) {
                fetchRoute(lat, lng);
                renderEta();
            }
        }

        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        showLoading();
        load();
        pollTimer = setInterval(() => {
            if (Date.now() - lastRealtime > 20000) load();
            if (destLatLng && lastKnownLat) fetchRoute(lastKnownLat, lastKnownLng);
        }, 8000);
    </script>
</body>

</html>
