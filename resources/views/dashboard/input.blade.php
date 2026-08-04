@extends('layouts.app')

@section('title', 'Input SPK Baru')

@section('content')
<style>
    /* ---- Page header ---- */
    .input-hero {
        background: linear-gradient(135deg, var(--navy-900,#061429), var(--navy-700,#112b5c));
        border-radius: 18px;
        padding: 22px 26px;
        color: #fff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(11,32,68,.20);
    }
    .input-hero::before {
        content: '';
        position: absolute;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200,16,46,.16) 0%, transparent 70%);
        top: -80px; right: 0;
        pointer-events: none;
    }
    .input-hero h2 { margin: 0 0 5px; font-size: 20px; font-weight: 900; letter-spacing: -.3px; position: relative; z-index: 1; }
    .input-hero p  { margin: 0; color: rgba(255,255,255,.60); font-size: 13.5px; position: relative; z-index: 1; }

    /* ---- Layout ---- */
    .input-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) { .input-layout { grid-template-columns: 1fr; } }

    /* ---- Section cards ---- */
    .sec-card {
        background: #fff;
        border: 1px solid var(--line,#e2e8f4);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 1px 4px rgba(11,32,68,.06);
    }
    .sec-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        background: var(--surface-2,#f6f9ff);
        border-bottom: 1px solid var(--line,#e2e8f4);
    }
    .sec-head-ico {
        width: 36px; height: 36px;
        border-radius: 9px;
        background: linear-gradient(135deg,var(--navy-700,#112b5c),var(--navy-600,#1a3a7a));
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        box-shadow: 0 3px 8px rgba(11,32,68,.18);
        flex-shrink: 0;
    }
    .sec-card-head h3 { margin: 0; font-size: 14.5px; font-weight: 800; color: var(--ink,#0d1b35); }
    .sec-card-head p  { margin: 2px 0 0; font-size: 12px; color: var(--muted,#64748b); }
    .sec-card-body { padding: 20px; }

    /* ---- SPK search ---- */
    .spk-search-wrap {
        position: relative;
        margin-bottom: 0;
    }
    .spk-search-wrap input { padding-left: 44px; }
    .spk-search-ico {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
        opacity: .45;
        pointer-events: none;
    }
    .spk-suggest {
        list-style: none;
        margin: 6px 0 0;
        padding: 0;
        border: 1.5px solid var(--line,#e2e8f4);
        border-radius: 12px;
        background: #fff;
        max-height: 280px;
        overflow-y: auto;
        box-shadow: 0 10px 32px rgba(11,32,68,.14);
        display: none;
    }
    .spk-suggest.visible { display: block; }
    .spk-suggest li {
        padding: 12px 16px;
        border-bottom: 1px solid var(--surface-2,#f6f9ff);
        cursor: pointer;
        transition: background .12s;
    }
    .spk-suggest li:last-child { border-bottom: 0; }
    .spk-suggest li:hover { background: var(--navy-100,#dce9fc); }
    .spk-suggest .ss-number { font-weight: 800; font-size: 14px; color: var(--navy-700,#112b5c); }
    .spk-suggest .ss-sub { font-size: 12.5px; color: var(--muted,#64748b); margin-top: 2px; display: flex; gap: 8px; }
    .spk-suggest .ss-empty { padding: 16px; text-align: center; color: var(--muted,#64748b); font-size: 13.5px; cursor: default; }
    .spk-suggest .ss-loading { padding: 14px; text-align: center; color: var(--muted,#64748b); font-size: 13px; }

    /* ---- Selected SPK preview ---- */
    .spk-preview {
        background: linear-gradient(135deg, var(--navy-100,#dce9fc), #eef4ff);
        border: 1.5px solid var(--navy-300,#6a9ae8);
        border-radius: 12px;
        padding: 16px;
        margin-top: 14px;
        display: none;
    }
    .spk-preview.visible { display: block; }
    .sp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .sp-number { font-size: 18px; font-weight: 900; color: var(--navy-700,#112b5c); letter-spacing: -.3px; }
    .sp-clear {
        background: none;
        border: 0;
        color: var(--muted,#64748b);
        cursor: pointer;
        font-size: 13px;
        font-family: inherit;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background .15s, color .15s;
    }
    .sp-clear:hover { background: rgba(200,16,46,.10); color: var(--red-500,#c8102e); }
    .sp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .sp-field { font-size: 13px; }
    .sp-label { color: var(--muted,#64748b); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .sp-val { font-weight: 600; color: var(--ink-2,#2c3e65); margin-top: 2px; }

    /* ---- Technician list ---- */
    .tech-filter-wrap { margin-bottom: 10px; }
    .tech-filter-wrap input { margin-bottom: 0; padding-left: 38px; }
    .tech-search-ico { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 15px; opacity: .4; pointer-events: none; }
    .tech-list-box {
        max-height: 260px;
        overflow-y: auto;
        border: 1.5px solid var(--line,#e2e8f4);
        border-radius: 10px;
        background: var(--surface-2,#f6f9ff);
        scrollbar-width: thin;
    }
    .tech-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--line,#e2e8f4);
        cursor: pointer;
        transition: background .12s;
    }
    .tech-item:last-child { border-bottom: 0; }
    .tech-item:hover { background: var(--navy-100,#dce9fc); }
    .tech-item input[type="checkbox"] { accent-color: var(--red-500,#c8102e); width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
    .tech-item-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg,var(--navy-700,#112b5c),var(--navy-500,#2451a0));
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800;
        flex-shrink: 0;
    }
    .tech-item-info { flex: 1; min-width: 0; }
    .tech-item-name { font-weight: 600; font-size: 13.5px; color: var(--ink,#0d1b35); }
    .tech-item-id { font-size: 11.5px; color: var(--muted,#64748b); }

    /* ---- Field rows ---- */
    .field-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 500px) { .field-2col { grid-template-columns: 1fr; } }

    /* ---- Map ---- */
    #location-map { height: 320px; border-radius: 10px; border: 1.5px solid var(--line,#e2e8f4); z-index: 0; }
    .map-hint { font-size: 12px; color: var(--muted,#64748b); margin-top: 8px; display: flex; align-items: center; gap: 6px; }

    /* ---- Submit ---- */
    .submit-area { padding: 20px; background: var(--surface-2,#f6f9ff); border-top: 1px solid var(--line,#e2e8f4); display: flex; align-items: center; gap: 12px; justify-content: flex-end; }
    .btn-submit {
        background: linear-gradient(135deg, #e01836, #8b0c1e);
        color: #fff;
        border: 0;
        padding: 13px 30px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 900;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 4px 18px rgba(200,16,46,.32);
        transition: transform .15s, box-shadow .15s;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(200,16,46,.42); }
    .btn-submit:active { transform: scale(.975); }

    /* ---- Step badge ---- */
    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: var(--red-500,#c8102e);
        color: #fff;
        font-size: 11px;
        font-weight: 900;
        flex-shrink: 0;
    }
</style>

{{-- Hero --}}
<div class="input-hero">
    <h2>➕ Input SPK Pemasangan Baru</h2>
    <p>Cari nomor SPK, assign teknisi, dan tentukan lokasi pemasangan.</p>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<form id="wo-form" method="POST" action="{{ url('/dashboard/work-orders') }}">
@csrf
<input type="hidden" name="legacy_sales_serial" id="legacy-sales-serial">
<input type="hidden" name="latitude" id="latitude">
<input type="hidden" name="longitude" id="longitude">

<div class="input-layout">

    {{-- === LEFT === --}}
    <div>

        {{-- Step 1: SPK --}}
        <div class="sec-card">
            <div class="sec-card-head">
                <div class="sec-head-ico">🔍</div>
                <div>
                    <h3>Langkah 1 — Pilih Nomor SPK</h3>
                    <p>Cari berdasarkan nomor SPK atau nama customer</p>
                </div>
            </div>
            <div class="sec-card-body">
                <label for="spk-search">Cari SPK / Invoice</label>
                <div class="spk-search-wrap">
                    <span class="spk-search-ico">🔍</span>
                    <input type="text" id="spk-search"
                           placeholder="Ketik nomor SPK atau nama customer (min. 3 karakter)..."
                           autocomplete="off"
                           style="margin-bottom:0;">
                </div>
                <ul id="spk-results" class="spk-suggest"></ul>

                <div id="spk-selected" class="spk-preview">
                    <div class="sp-header">
                        <div class="sp-number" id="s-spk">—</div>
                        <button type="button" class="sp-clear" id="btn-clear-spk">✕ Ganti SPK</button>
                    </div>
                    <div class="sp-grid">
                        <div class="sp-field"><div class="sp-label">Jenis Film</div><div class="sp-val" id="s-film">—</div></div>
                        <div class="sp-field"><div class="sp-label">Customer</div><div class="sp-val" id="s-customer">—</div></div>
                        <div class="sp-field"><div class="sp-label">Kendaraan</div><div class="sp-val" id="s-car">—</div></div>
                        <div class="sp-field"><div class="sp-label">Tanggal Pasang (Sumber)</div><div class="sp-val" id="s-date">—</div></div>
                        <div class="sp-field"><div class="sp-label">Alamat</div><div class="sp-val" id="s-address">—</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Location --}}
        <div class="sec-card">
            <div class="sec-card-head">
                <div class="sec-head-ico" style="background:linear-gradient(135deg,#059669,#047857);">📍</div>
                <div>
                    <h3>Langkah 3 — Lokasi Pemasangan</h3>
                    <p>Cari di peta atau klik langsung titik lokasi</p>
                </div>
            </div>
            <div class="sec-card-body">
                <div class="field-2col" style="margin-bottom:14px;">
                    <div>
                        <label for="location-search">Cari Lokasi / Tempat</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:15px;opacity:.4;pointer-events:none;">🔍</span>
                            <input type="text" id="location-search"
                                   placeholder="Nama jalan, landmark, dsb..."
                                   autocomplete="off"
                                   style="padding-left:38px;margin-bottom:0;">
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-end;gap:8px;">
                        <button type="button" id="location-search-btn" class="btn" style="margin-bottom:0;white-space:nowrap;">
                            🔎 Cari di Peta
                        </button>
                    </div>
                </div>

                <label for="location-address">Alamat Lengkap</label>
                <input type="text" id="location-address" name="location_address"
                       placeholder="Alamat lengkap lokasi pemasangan"
                       autocomplete="off">

                <button type="button" id="location-geocode" class="btn btn-secondary" style="margin-bottom:14px;">
                    📌 Cari Koordinat dari Alamat
                </button>

                <div id="location-map"></div>
                <div class="map-hint">
                    📌 Klik di peta atau geser pin untuk mengatur titik lokasi. Alamat dari SPK otomatis dicari saat SPK dipilih.
                </div>
            </div>
        </div>

        {{-- Step 4: Kontak Customer --}}
        <div class="sec-card">
            <div class="sec-card-head">
                <div class="sec-head-ico" style="background:linear-gradient(135deg,#0284c7,#0369a1);">✉️</div>
                <div>
                    <h3>Langkah 4 — Kontak Customer (Opsional)</h3>
                    <p>Isi jika ingin memperbarui no. WA / email customer</p>
                </div>
            </div>
            <div class="sec-card-body">
                <div class="field-2col">
                    <div>
                        <label for="customer-phone">No. WhatsApp</label>
                        <input type="tel" name="customer_phone" id="customer-phone"
                               placeholder="08xxxxxxxxxx" autocomplete="off">
                    </div>
                    <div>
                        <label for="customer-email">Email</label>
                        <input type="email" name="customer_email" id="customer-email"
                               placeholder="nama@email.com" autocomplete="off"
                               pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Format email tidak valid, contoh: nama@email.com">
                    </div>
                </div>
                <div class="map-hint" style="margin-top:10px;">
                    Kosongkan jika tidak ingin mengubah data customer.
                </div>
            </div>
        </div>

    </div>{{-- /left --}}

    {{-- === RIGHT === --}}
    <div>

        {{-- Step 2: Teknisi --}}
        <div class="sec-card" style="position:sticky;top:80px;">
            <div class="sec-card-head">
                <div class="sec-head-ico" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">👷</div>
                <div>
                    <h3>Langkah 2 — Assign Teknisi</h3>
                    <p>Pilih satu atau lebih teknisi yang bertugas</p>
                </div>
            </div>
            <div class="sec-card-body" style="padding-bottom:0;">
                <div class="field-2col">
                    <div>
                        <label for="scheduled-start-at">Tanggal Pemasangan <span style="color:var(--red-500);">*</span></label>
                        <input type="date" name="scheduled_start_at" id="scheduled-start-at" required>
                    </div>
                    <div>
                        <label for="scheduled-start-time">Jam Pemasangan</label>
                        <input type="time" name="scheduled_start_time" id="scheduled-start-time">
                    </div>
                </div>
                <label for="notes" style="margin-top:14px;">Catatan</label>
                <textarea name="notes" id="notes" rows="1"
                          placeholder="Opsional..."
                          style="resize:vertical;"></textarea>

                <label for="tech-filter">Cari Teknisi</label>
                <div class="tech-filter-wrap" style="position:relative;">
                    <span class="tech-search-ico">👷</span>
                    <input type="text" id="tech-filter"
                           placeholder="Filter nama atau ID teknisi..."
                           style="padding-left:38px;">
                </div>

                <div id="tech-list" class="tech-list-box">
                    <div style="padding:16px;text-align:center;color:var(--muted);">Memuat daftar teknisi…</div>
                </div>

                <div id="tech-selected-summary" style="margin-top:10px;font-size:12.5px;color:var(--muted,#64748b);min-height:18px;"></div>
            </div>

            <div class="submit-area">
                <a href="{{ route('dashboard.work-orders') }}" class="btn btn-secondary" style="box-shadow:none;">
                    Batal
                </a>
                <button class="btn-submit" type="submit" id="btn-submit">
                    💾 Simpan & Assign Teknisi
                </button>
            </div>
        </div>

    </div>{{-- /right --}}

</div>
</form>

@push('scripts')
<script>
    /* =====================================================
       SPK SEARCH
    ===================================================== */
    const selectedSpk = {
        set(data) {
            const preview = document.getElementById('spk-selected');
            document.getElementById('s-spk').textContent = data.spk_no || '-';
            document.getElementById('s-customer').textContent = data.customer_name || '-';
            const fullAddress = [data.address, data.city, data.state].filter(Boolean).join(', ');
            document.getElementById('s-address').textContent = fullAddress || '-';
            document.getElementById('location-address').value = fullAddress;
            document.getElementById('s-car').textContent = [data.car_brand, data.car_model].filter(Boolean).join(' ') || '-';
            document.getElementById('s-date').textContent = data.installation_date || '-';
            document.getElementById('s-film').textContent = data.window_film_desc || '-';
            document.getElementById('customer-phone').value = data.cell_phone || '';
            document.getElementById('customer-email').value = data.customer_email || '';
            document.getElementById('legacy-sales-serial').value = data.serial || '';
            const dateInput = document.getElementById('scheduled-start-at');
            if (!dateInput.value && data.installation_date) {
                dateInput.value = data.installation_date.slice(0, 10);
            }
            preview.classList.add('visible');
            document.getElementById('spk-search').closest('.spk-search-wrap').style.display = 'none';
            if (fullAddress.trim()) autoGeocode(fullAddress);
        },
        clear() {
            document.getElementById('spk-selected').classList.remove('visible');
            document.getElementById('legacy-sales-serial').value = '';
            document.getElementById('spk-search').value = '';
            document.getElementById('spk-search').closest('.spk-search-wrap').style.display = '';
        },
    };

    document.getElementById('btn-clear-spk').addEventListener('click', () => selectedSpk.clear());

    const spkInput   = document.getElementById('spk-search');
    const spkResults = document.getElementById('spk-results');
    let spkTimer;

    spkInput.addEventListener('input', () => {
        clearTimeout(spkTimer);
        const q = spkInput.value.trim();
        if (q.length < 3) {
            spkResults.innerHTML = '';
            spkResults.classList.remove('visible');
            return;
        }
        spkResults.innerHTML = '<li class="ss-loading">⏳ Mencari SPK…</li>';
        spkResults.classList.add('visible');
        spkTimer = setTimeout(() => searchSpk(q), 350);
    });

    async function searchSpk(q) {
        try {
            const res = await fetch('/dashboard/api/sales?search=' + encodeURIComponent(q));
            const payload = await res.json();
            spkResults.innerHTML = '';

            if (!payload.data.length) {
                spkResults.innerHTML = '<li class="ss-empty">😕 SPK tidak ditemukan</li>';
                return;
            }

            payload.data.forEach(row => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div class="ss-number">${row.spk_no || ''}</div>
                    <div class="ss-sub"><span>🪟 ${row.window_film_desc || '-'}</span></div>
                    <div class="ss-sub">
                        <span>👤 ${row.customer_name || '-'}</span>
                        <span>📅 ${row.installation_date || '-'}</span>
                    </div>`;
                li.addEventListener('click', () => {
                    selectedSpk.set(row);
                    spkResults.innerHTML = '';
                    spkResults.classList.remove('visible');
                });
                spkResults.appendChild(li);
            });
        } catch (e) {
            spkResults.innerHTML = '<li class="ss-empty">⚠️ Gagal memuat data</li>';
        }
    }

    /* =====================================================
       LOCATION MAP
    ===================================================== */
    const DEFAULT_MAP = { lat: -2.5489, lng: 118.0149, zoom: 5 };
    let map = null, pin = null, geocodeSeq = 0;

    function initLocationMap() {
        if (map || typeof L === 'undefined') return;
        map = L.map('location-map').setView([DEFAULT_MAP.lat, DEFAULT_MAP.lng], DEFAULT_MAP.zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);
        map.on('click', e => placePin(e.latlng.lat, e.latlng.lng, true));
    }

    function placePin(lat, lng, reverse) {
        document.getElementById('latitude').value  = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
        if (!pin) {
            pin = L.marker([lat, lng], { draggable: true }).addTo(map);
            pin.on('dragend', () => { const p = pin.getLatLng(); placePin(p.lat, p.lng, true); });
        } else {
            pin.setLatLng([lat, lng]);
        }
        if (reverse) reverseGeocode(lat, lng);
    }

    async function nominatimGet(path, params) {
        const url = 'https://nominatim.openstreetmap.org/' + path + '?' + new URLSearchParams(params);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    async function searchAndPin(query, zoom = 16, silent = false) {
        initLocationMap();
        if (!query.trim()) return;
        const seq = ++geocodeSeq;
        try {
            const results = await nominatimGet('search', { format: 'jsonv2', q: query, limit: 1, countrycodes: 'id' });
            if (seq !== geocodeSeq) return;
            if (!results.length) { if (!silent) alert('Lokasi tidak ditemukan. Coba kata kunci lain.'); return; }
            const r = results[0];
            map.flyTo([parseFloat(r.lat), parseFloat(r.lon)], zoom);
            placePin(parseFloat(r.lat), parseFloat(r.lon), true);
        } catch (err) {
            if (!silent) alert('Gagal mencari lokasi: ' + err.message);
        }
    }

    async function reverseGeocode(lat, lng) {
        try {
            const data = await nominatimGet('reverse', { format: 'jsonv2', lat, lon: lng, zoom: 16 });
            const input = document.getElementById('location-address');
            if (data?.display_name) input.value = data.display_name;
        } catch (_) {}
    }

    function autoGeocode(address) {
        if (!address.trim()) return;
        initLocationMap();
        searchAndPin(address, 14, true);
    }

    document.getElementById('location-search-btn').addEventListener('click', () =>
        searchAndPin(document.getElementById('location-search').value));
    document.getElementById('location-search').addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('location-search-btn').click(); }
    });
    document.getElementById('location-geocode').addEventListener('click', () =>
        searchAndPin(document.getElementById('location-address').value));

    // Init map early
    window.addEventListener('load', initLocationMap);

    /* =====================================================
       TECHNICIANS
    ===================================================== */
    let technicians = [];

    async function loadTechnicians() {
        try {
            const res = await fetch('/dashboard/api/technicians');
            const payload = await res.json();
            technicians = payload.data;
            renderTechnicians('');
        } catch (e) {
            document.getElementById('tech-list').innerHTML =
                '<div style="padding:14px;text-align:center;color:var(--muted);">Gagal memuat data teknisi.</div>';
        }
    }

    function renderTechnicians(filter) {
        const box = document.getElementById('tech-list');
        const needle = filter.toLowerCase();
        const rows = technicians.filter(t =>
            !needle ||
            (t.full_name || '').toLowerCase().includes(needle) ||
            (t.user_id || '').toLowerCase().includes(needle)
        );

        if (!rows.length) {
            box.innerHTML = '<div style="padding:14px;text-align:center;color:var(--muted);">Tidak ada teknisi yang cocok.</div>';
            return;
        }

        box.innerHTML = '';
        rows.forEach(t => {
            const div = document.createElement('div');
            div.className = 'tech-item';
            div.innerHTML = `
                <input type="checkbox" name="technician_legacy_serials[]" value="${t.serial}" class="tech-check" id="tc-${t.serial}">
                <div class="tech-item-avatar">${(t.full_name||'T').charAt(0).toUpperCase()}</div>
                <label for="tc-${t.serial}" class="tech-item-info" style="cursor:pointer;">
                    <div class="tech-item-name">${t.full_name || '-'}</div>
                    <div class="tech-item-id">${t.user_id ? 'ID: ' + t.user_id : ''}</div>
                </label>`;
            div.addEventListener('click', e => {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') {
                    const cb = div.querySelector('.tech-check');
                    cb.checked = !cb.checked;
                    updateSummary();
                }
            });
            div.querySelector('.tech-check').addEventListener('change', updateSummary);
            box.appendChild(div);
        });
    }

    function updateSummary() {
        const checked = document.querySelectorAll('.tech-check:checked');
        const el = document.getElementById('tech-selected-summary');
        el.textContent = checked.length
            ? `✅ ${checked.length} teknisi dipilih`
            : '';
    }

    document.getElementById('tech-filter').addEventListener('input', e => {
        renderTechnicians(e.target.value.trim());
    });

    /* =====================================================
       FORM SUBMIT VALIDATION
    ===================================================== */
    document.getElementById('wo-form').addEventListener('submit', e => {
        const serial = document.getElementById('legacy-sales-serial').value;
        const dateVal = document.getElementById('scheduled-start-at').value;
        const checks = document.querySelectorAll('.tech-check:checked');
        if (!serial) {
            e.preventDefault();
            alert('⚠️ Silakan pilih SPK terlebih dahulu.');
            return;
        }
        if (!dateVal) {
            e.preventDefault();
            alert('⚠️ Tanggal pemasangan wajib diisi.');
            return;
        }
        if (!checks.length) {
            e.preventDefault();
            alert('⚠️ Pilih minimal 1 teknisi untuk assignment.');
        }
    });

    loadTechnicians();
</script>
@endpush
@endsection
