<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b1f4b">
    <title>Live Tracking Pemasangan - Indomotor Lestari</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root {
            --navy: #0b1f4b;
            --navy-grad: linear-gradient(135deg, #0b1f4b 0%, #16347f 100%);
            --red: #d00202;
            --bg: #f4f6fb;
            --card: #ffffff;
            --ink: #101828;
            --muted: #667085;
            --line: #e6eaf2;
            --green: #16a34a;
            --amber: #d97706;
            --violet: #7c3aed;
            --shadow: 0 6px 20px rgba(11, 31, 75, .08);
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, sans-serif;
            background: var(--bg); color: var(--ink); font-size: 15px;
        }
        #app { width: 100%; min-height: 100vh; }
        .header {
            background: var(--navy-grad); color: #fff; padding: 18px 18px 26px;
            border-radius: 0 0 26px 26px; text-align: center;
        }
        .header .logo { background: #fff; border-radius: 12px; padding: 6px 10px; display: inline-block; }
        .header .logo img { height: 28px; display: block; }
        .header h1 { margin: 12px 0 4px; font-size: 19px; }
        .header p { margin: 0; color: #b9c7ea; font-size: 13px; }
        .body { padding: 16px; }

        .status-card {
            border-radius: 16px; padding: 16px; color: #fff; margin-bottom: 14px;
            box-shadow: var(--shadow);
        }
        .status-card .big { font-size: 16px; font-weight: 800; }
        .status-card .small { opacity: .92; font-size: 12.5px; margin-top: 3px; }
        .card {
            background: var(--card); border-radius: 16px; box-shadow: var(--shadow);
            border: 1px solid var(--line); padding: 16px; margin-bottom: 14px;
        }
        .card h4 { margin: 0 0 10px; font-size: 12.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
        .kv { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px dashed #eef1f7; font-size: 14px; }
        .kv:last-child { border-bottom: 0; }
        .kv .k { color: var(--muted); flex-shrink: 0; }
        .kv .v { text-align: right; font-weight: 600; }
        #track-map { height: 230px; border-radius: 12px; border: 1px solid var(--line); z-index: 1; }
        .map-note { font-size: 12px; color: var(--muted); margin-top: 8px; display: flex; align-items: center; gap: 6px; }

        .stepper { display: flex; justify-content: space-between; margin: 6px 2px 2px; }
        .step { flex: 1; text-align: center; position: relative; }
        .step .dot { width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; background: #eef1f7; border: 2px solid #d3dae7; display: flex; align-items: center; justify-content: center; font-size: 11px; color: var(--muted); position: relative; z-index: 1; }
        .step .lbl { font-size: 10px; color: var(--muted); margin-top: 4px; }
        .step.done .dot { background: var(--green); border-color: var(--green); color: #fff; }
        .step.active .dot { background: var(--red); border-color: var(--red); color: #fff; box-shadow: 0 0 0 4px rgba(208,2,2,.15); }
        .step.active .lbl { color: var(--red); font-weight: 700; }
        .step::before { content: ""; position: absolute; top: 12px; right: 50%; width: 100%; height: 2px; background: #e6eaf2; z-index: 0; }
        .step:first-child::before { display: none; }
        .step.done::before, .step.active::before { background: var(--green); }

        .badge { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .b-violet { background: #ede9fe; color: #5b21b6; }
        .b-green { background: #dcfce7; color: #166534; }
        .b-rose { background: #ffe4e6; color: #9f1239; }
        .b-gray { background: #e2e8f0; color: #475569; }

        .live-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--red); display: inline-block; animation: pulse 1.2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }
        .muted { color: var(--muted); }
        .small { font-size: 12.5px; }
        .hidden { display: none; }
        .center-box { padding: 70px 24px; text-align: center; }
        .center-box .big { font-size: 40px; display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
<div id="app">
    <div class="header">
        <div class="logo"><img src="/assets/images/iml-logo.png" alt="IML"></div>
        <h1>Live Tracking Pemasangan</h1>
        <p>Pantau posisi teknisi kami secara real-time</p>
    </div>

    <div class="body">
        <div id="state-loading" class="center-box">
            <span class="big">⏳</span>
            Memuat data tracking...
        </div>

        <div id="state-invalid" class="center-box hidden">
            <span class="big">😕</span>
            <b>Link tracking tidak aktif</b>
            <p class="muted small">Link sudah tidak berlaku atau sesi tracking telah berakhir. Hubungi tim Indomotor Lestari bila butuh bantuan.</p>
        </div>

        <div id="state-active" class="hidden">
            <div id="status-card" class="status-card">
                <div id="status-big" class="big">Teknisi dalam perjalanan 🚗</div>
                <div id="status-small" class="small">Menuju lokasi pemasangan Anda</div>
            </div>

            <div class="card">
                <h4>Progres Pemasangan</h4>
                <div class="stepper">
                    <div class="step done"><div class="dot">✓</div><div class="lbl">Dipesan</div></div>
                    <div id="st-berangkat" class="step"><div class="dot">2</div><div class="lbl">Berangkat</div></div>
                    <div id="st-tiba" class="step"><div class="dot">3</div><div class="lbl">Tiba</div></div>
                    <div id="st-pasang" class="step"><div class="dot">4</div><div class="lbl">Pasang</div></div>
                    <div id="st-selesai" class="step"><div class="dot">5</div><div class="lbl">Selesai</div></div>
                </div>
            </div>

            <div class="card">
                <h4>📍 Lokasi Teknisi</h4>
                <div id="track-map"></div>
                <div class="map-note" id="map-note">
                    <span class="live-dot"></span>
                    Posisi diperbarui otomatis <span id="last-updated">-</span>
                </div>
            </div>

            <div class="card">
                <h4>Informasi Pemasangan</h4>
                <div class="kv"><span class="k">Nomor SPK</span><span class="v" id="wo-number">-</span></div>
                <div class="kv"><span class="k">Jadwal</span><span class="v" id="wo-date">-</span></div>
                <div class="kv"><span class="k">Alamat</span><span class="v" id="wo-address">-</span></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const TOKEN = @json($token);
    const API_URL = '/api/v1/public/tracking/' + encodeURIComponent(TOKEN);
    const STATUS_META = {
        on_the_way: { big: 'Teknisi dalam perjalanan 🚗', small: 'Menuju lokasi pemasangan Anda', color: '#7c3aed' },
        finished:   { big: 'Pemasangan selesai ✅',        small: 'Terima kasih telah mempercayakan kami!', color: '#16a34a' },
        cancelled:  { big: 'Pemasangan dibatalkan',       small: 'Hubungi tim kami bila ada pertanyaan.', color: '#64748b' },
        failed:     { big: 'Pemasangan terkendala',       small: 'Tim kami akan segera menghubungi Anda.', color: '#991b1b' },
    };

    let map = null;
    let destMarker = null;
    let posMarker = null;
    let posAccuracy = null;
    let pollTimer = null;

    function showLoading() { document.getElementById('state-loading').classList.remove('hidden'); document.getElementById('state-invalid').classList.add('hidden'); document.getElementById('state-active').classList.add('hidden'); }
    function showInvalid() { stopPolling(); document.getElementById('state-loading').classList.add('hidden'); document.getElementById('state-invalid').classList.remove('hidden'); document.getElementById('state-active').classList.add('hidden'); }
    function showActive() { document.getElementById('state-loading').classList.add('hidden'); document.getElementById('state-invalid').classList.add('hidden'); document.getElementById('state-active').classList.remove('hidden'); }

    async function load() {
        try {
            const res = await fetch(API_URL, { headers: { 'Accept': 'application/json' } });
            if (res.status === 404) { showInvalid(); return; }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            render(data);
        } catch (err) {
            showInvalid();
        }
    }

    function render(data) {
        const meta = STATUS_META[data.status] || STATUS_META.on_the_way;
        document.getElementById('status-big').textContent = meta.big;
        document.getElementById('status-small').textContent = meta.small;
        document.getElementById('status-card').style.background = 'linear-gradient(135deg, ' + meta.color + ', ' + meta.color + 'cc)';

        const stepMap = { on_the_way: 'berangkat', finished: 'selesai' };
        const activeKey = stepMap[data.status];
        document.querySelectorAll('#state-active .step').forEach(s => s.classList.remove('active', 'done'));
        if (data.status === 'finished') {
            ['st-berangkat', 'st-tiba', 'st-pasang', 'st-selesai'].forEach(id => {
                const el = document.getElementById(id);
                el.classList.add('done');
                el.querySelector('.dot').textContent = '✓';
            });
        } else if (activeKey) {
            const el = document.getElementById('st-' + activeKey);
            el.classList.add('active');
        }

        document.getElementById('wo-number').textContent = data.work_order ? data.work_order.number : '-';
        document.getElementById('wo-date').textContent = data.work_order && data.work_order.scheduled_start_at
            ? new Date(data.work_order.scheduled_start_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            : '-';
        document.getElementById('wo-address').textContent = data.destination && data.destination.address ? data.destination.address : '-';

        if (data.status === 'on_the_way') {
            showActive();
            initMap(data.destination);
            updatePosition(data.current_location);
        } else {
            stopPolling();
            showActive();
            if (data.destination) initMap(data.destination, true);
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
            maxZoom: 19, attribution: '&copy; OpenStreetMap',
        }).addTo(map);
        if (hasDest) {
            destMarker = L.marker([parseFloat(dest.latitude), parseFloat(dest.longitude)]).addTo(map);
        }
        if (finalize) {
            document.getElementById('map-note').innerHTML = 'Posisi teknisi tidak lagi dikirimkan.';
        }
    }

    function updatePosition(location) {
        const note = document.getElementById('map-note');
        if (!location || !location.latitude || !location.longitude) {
            note.innerHTML = '<span class="live-dot"></span> Menunggu sinyal lokasi teknisi...';
            return;
        }
        const lat = parseFloat(location.latitude), lng = parseFloat(location.longitude);
        if (!map) initMap(null);
        if (!map) return;
        if (posMarker) {
            posMarker.setLatLng([lat, lng]);
        } else {
            posMarker = L.circleMarker([lat, lng], { radius: 9, color: '#d00202', fillColor: '#d00202', fillOpacity: .45 }).addTo(map);
        }
        if (location.accuracy_meters && posAccuracy) posAccuracy.setLatLng([lat, lng]).setRadius(parseFloat(location.accuracy_meters));
        if (location.accuracy_meters && !posAccuracy) {
            posAccuracy = L.circle([lat, lng], { radius: parseFloat(location.accuracy_meters), color: '#d00202', fillColor: '#d00202', fillOpacity: .08, weight: 1 }).addTo(map);
        }
        const bounds = L.latLngBounds([[lat, lng]]);
        if (destMarker) bounds.extend(destMarker.getLatLng());
        map.fitBounds(bounds.pad(0.35), { maxZoom: 16 });
        const stamp = location.recorded_at || location.received_at;
        document.getElementById('last-updated').textContent = stamp
            ? new Date(stamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
            : '-';
    }

    function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

    showLoading();
    load();
    pollTimer = setInterval(load, 8000);
</script>
</body>
</html>
