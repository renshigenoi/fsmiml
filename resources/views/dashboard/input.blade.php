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

    /* ---- Time picker popup ---- */
    .time-picker-wrap { position: relative; }
    .time-picker-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1.5px solid var(--line,#e2e8f4);
        border-radius: 9px;
        background: var(--surface-2,#f6f9ff);
        color: var(--ink,#0d1b35);
        padding: 10px 12px;
        font-size: 14px;
        font-family: inherit;
        cursor: pointer;
        text-align: left;
        margin-bottom: 0;
    }
    .time-picker-btn:hover, .time-picker-btn:focus { border-color: var(--red-500,#c8102e); outline: none; }
    .tp-ico { font-size: 15px; opacity: .55; }
    .tp-display { font-weight: 600; }
    .time-picker-popup {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        left: auto;
        z-index: 60;
        width: 330px;
        max-width: calc(100vw - 32px);
        background: #fff;
        border: 1px solid var(--line,#e2e8f4);
        border-radius: 14px;
        box-shadow: 0 14px 40px rgba(11,32,68,.18);
        padding: 14px;
    }
    .time-picker-popup.open { display: block; }
    .tp-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .tp-value {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -1px;
        color: var(--navy-700,#112b5c);
        font-variant-numeric: tabular-nums;
    }
    .tp-presets { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    .tp-preset {
        border: 1px solid var(--line,#e2e8f4);
        background: var(--surface-2,#f6f9ff);
        border-radius: 8px;
        padding: 5px 9px;
        font-size: 12px;
        font-weight: 700;
        color: var(--ink-2,#2c3e65);
        cursor: pointer;
        font-variant-numeric: tabular-nums;
    }
    .tp-preset:hover { border-color: var(--red-500,#c8102e); color: var(--red-500,#c8102e); }
    .tp-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    .tp-col-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 800;
        color: var(--muted,#64748b);
        margin-bottom: 6px;
    }
    .tp-options {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
        max-height: 190px;
        overflow-y: auto;
        padding-right: 2px;
    }
    .tp-opt {
        border: 1px solid var(--line,#e2e8f4);
        background: #fff;
        border-radius: 8px;
        padding: 7px 0;
        font-size: 13px;
        font-weight: 700;
        color: var(--ink-2,#2c3e65);
        cursor: pointer;
        font-variant-numeric: tabular-nums;
    }
    .tp-opt:hover { border-color: var(--red-500,#c8102e); color: var(--red-500,#c8102e); }
    .tp-opt.active { background: var(--navy-700,#112b5c); border-color: var(--navy-700,#112b5c); color: #fff; }
    .tp-actions { display: flex; gap: 8px; }
    .tp-actions button {
        flex: 1;
        border: 0;
        border-radius: 9px;
        padding: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
    }
    .tp-now { background: var(--surface-2,#f6f9ff); color: var(--ink-2,#2c3e65); }
    .tp-clear { background: var(--surface-2,#f6f9ff); color: var(--red-500,#c8102e); }
    .tp-done { background: linear-gradient(135deg,#c8102e,#a30d24); color: #fff; }

    /* ---- Date picker popup ---- */
    .date-picker-popup { width: 300px; left: 0; right: auto; }
    .dp-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .dp-nav {
        border: 1px solid var(--line,#e2e8f4);
        background: var(--surface-2,#f6f9ff);
        border-radius: 8px;
        width: 30px;
        height: 30px;
        cursor: pointer;
        font-size: 16px;
        color: var(--ink-2,#2c3e65);
    }
    .dp-nav:hover { border-color: var(--red-500,#c8102e); color: var(--red-500,#c8102e); }
    .dp-title { font-weight: 800; font-size: 14px; color: var(--navy-700,#112b5c); }
    .dp-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 6px; }
    .dp-weekdays span {
        text-align: center;
        font-size: 10.5px;
        font-weight: 800;
        color: var(--muted,#64748b);
        text-transform: uppercase;
    }
    .dp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 12px; }
    .dp-day {
        border: 1px solid transparent;
        background: transparent;
        border-radius: 8px;
        padding: 7px 0;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-2,#2c3e65);
        cursor: pointer;
        font-variant-numeric: tabular-nums;
        font-family: inherit;
    }
    .dp-day:hover { background: var(--surface-2,#f6f9ff); border-color: var(--line,#e2e8f4); }
    .dp-day.out { color: #c3cbe0; }
    .dp-day.today { border-color: var(--red-500,#c8102e); color: var(--red-500,#c8102e); }
    .dp-day.selected { background: var(--navy-700,#112b5c); border-color: var(--navy-700,#112b5c); color: #fff; }

    /* ---- Toast notification ---- */
    .toast-v {
        position: fixed;
        top: 20px;
        left: 50%;
        z-index: 9999;
        width: calc(100% - 40px);
        max-width: 440px;
        background: #fff;
        border-left: 4px solid var(--red-500,#c8102e);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(11,32,68,.20);
        display: flex;
        gap: 12px;
        padding: 16px 20px;
        transform: translate(-50%, -140%);
        opacity: 0;
        transition: transform .35s cubic-bezier(.22,.61,.36,1), opacity .25s;
        pointer-events: none;
    }
    .toast-v.show { transform: translate(-50%, 0); opacity: 1; pointer-events: auto; }
    .toast-v-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg,#c8102e,#a50d26);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .toast-v-body { flex: 1; min-width: 0; }
    .toast-v-title { font-weight: 800; font-size: 13.5px; color: var(--ink,#0d1b35); margin-bottom: 3px; }
    .toast-v-msg { font-size: 12.5px; color: var(--muted,#64748b); line-height: 1.45; }
    .toast-v-close {
        background: none; border: 0;
        color: var(--muted,#64748b);
        font-size: 18px; cursor: pointer;
        padding: 0; line-height: 1;
        align-self: flex-start;
    }
    .toast-v-close:hover { color: var(--red-500,#c8102e); }
    .field-error { border-color: var(--red-500,#c8102e) !important; background: #fff5f7 !important; }
    .field-error:focus { box-shadow: 0 0 0 3px rgba(200,16,46,.12) !important; }

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

{{-- Toast notification --}}
<div id="toast-v" class="toast-v">
    <div class="toast-v-icon">⚠️</div>
    <div class="toast-v-body">
        <div class="toast-v-title" id="toast-v-title"></div>
        <div class="toast-v-msg" id="toast-v-msg"></div>
    </div>
    <button type="button" class="toast-v-close" onclick="hideToast()">✕</button>
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
                        <div class="sp-field"><div class="sp-label">Customer</div><div class="sp-val" id="s-customer">—</div></div>
                        <div class="sp-field"><div class="sp-label">Kendaraan</div><div class="sp-val" id="s-car">—</div></div>
                        <div class="sp-field"><div class="sp-label">Tanggal Pasang (Sumber)</div><div class="sp-val" id="s-date">—</div></div>
                        <div class="sp-field"><div class="sp-label">Alamat</div><div class="sp-val" id="s-address">—</div></div>
                    </div>
                </div>

                <div id="sales-detail-wrap" class="sales-detail-wrap" style="display:none;margin-top:14px;">
                    <div class="sales-detail-head" style="font-weight:800;font-size:13px;color:var(--navy-700,#112b5c);margin-bottom:8px;">
                        🪟 Detail Item Pemasangan
                    </div>
                    <div id="sales-detail-body" style="border:1px solid var(--line,#e2e8f4);border-radius:10px;overflow:hidden;"></div>
                </div>
            </div>
        </div>

        {{-- Step 2: Location --}}
        <div class="sec-card">
            <div class="sec-card-head">
                <div class="sec-head-ico" style="background:linear-gradient(135deg,#059669,#047857);">📍</div>
                <div>
                    <h3>Langkah 2 — Lokasi Pemasangan</h3>
                    <p>Cari di peta atau klik langsung titik lokasi</p>
                </div>
            </div>
            <div class="sec-card-body">
                <div class="field-2col" style="margin-bottom:14px;grid-template-columns:1fr auto;">
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

        {{-- Step 3: Kontak Customer --}}
        <div class="sec-card">
            <div class="sec-card-head">
                <div class="sec-head-ico" style="background:linear-gradient(135deg,#0284c7,#0369a1);">✉️</div>
                <div>
                    <h3>Langkah 3 — Kontak Customer</h3>
                    <p>No. WA wajib untuk notifikasi live tracking, email opsional</p>
                </div>
            </div>
            <div class="sec-card-body">
                <div class="field-2col">
                    <div>
                        <label for="customer-phone">No. WhatsApp <span style="color:var(--red-500,#c8102e);">*</span></label>
                        <input type="tel" name="customer_phone" id="customer-phone"
                               placeholder="08123456789"
                               autocomplete="off"
                               inputmode="numeric" maxlength="15">
                        <div class="map-hint" style="margin-top:4px;">
                            Diperlukan untuk notifikasi live tracking ke customer.
                        </div>
                    </div>
                    <div>
                        <label for="customer-email">Email</label>
                        <input type="email" name="customer_email" id="customer-email"
                               placeholder="nama@email.com" autocomplete="off"
                               pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Format email tidak valid, contoh: nama@email.com">
                    </div>
                </div>
                <div class="map-hint" style="margin-top:10px;">
                    Email opsional — kosongkan jika tidak diperlukan.
                </div>
            </div>
        </div>

    </div>{{-- /left --}}

    {{-- === RIGHT === --}}
    <div>

        {{-- Step 4: Jadwal & Teknisi --}}
        <div class="sec-card" style="position:sticky;top:80px;">
            <div class="sec-card-head">
                <div class="sec-head-ico" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">👷</div>
                <div>
                    <h3>Langkah 4 — Jadwal &amp; Assign Teknisi</h3>
                    <p>Pilih tanggal & jam pemasangan, lalu teknisi yang bertugas</p>
                </div>
            </div>
            <div class="sec-card-body" style="padding-bottom:0;">
                <div class="field-2col">
                    <div>
                        <label for="scheduled-start-at">Tanggal Pemasangan <span style="color:var(--red-500);">*</span></label>
                        <div class="time-picker-wrap">
                            <button type="button" id="date-picker-btn" class="time-picker-btn">
                                <span class="tp-ico">📅</span>
                                <span class="tp-display" id="date-picker-display">Pilih tanggal</span>
                            </button>
                            <input type="hidden" name="scheduled_start_at" id="scheduled-start-at" required>
                            <div id="date-picker-popup" class="time-picker-popup date-picker-popup">
                                <div class="dp-head">
                                    <button type="button" class="dp-nav" id="dp-prev">‹</button>
                                    <div class="dp-title" id="dp-title"></div>
                                    <button type="button" class="dp-nav" id="dp-next">›</button>
                                </div>
                                <div class="dp-weekdays">
                                    <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span>
                                    <span>Jum</span><span>Sab</span><span>Min</span>
                                </div>
                                <div class="dp-grid" id="dp-grid"></div>
                                <div class="tp-actions">
                                    <button type="button" class="tp-now" id="dp-today">Hari Ini</button>
                                    <button type="button" class="tp-clear" id="dp-clear">Hapus</button>
                                    <button type="button" class="tp-done" id="dp-done">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="time-picker-btn">Jam Pemasangan</label>
                        <div class="time-picker-wrap">
                            <button type="button" id="time-picker-btn" class="time-picker-btn">
                                <span class="tp-ico">🕐</span>
                                <span class="tp-display" id="time-picker-display">Pilih jam</span>
                            </button>
                            <input type="hidden" name="scheduled_start_time" id="scheduled-start-time">
                            <div id="time-picker-popup" class="time-picker-popup">
                                <div class="tp-head">
                                    <div class="tp-value" id="tp-value">--:--</div>
                                    <div class="tp-presets">
                                        <button type="button" class="tp-preset" data-h="08" data-m="00">08:00</button>
                                        <button type="button" class="tp-preset" data-h="09" data-m="00">09:00</button>
                                        <button type="button" class="tp-preset" data-h="10" data-m="00">10:00</button>
                                        <button type="button" class="tp-preset" data-h="13" data-m="00">13:00</button>
                                    </div>
                                </div>
                                <div class="tp-cols">
                                    <div>
                                        <div class="tp-col-title">Jam</div>
                                        <div class="tp-options" id="tp-hours"></div>
                                    </div>
                                    <div>
                                        <div class="tp-col-title">Menit</div>
                                        <div class="tp-options" id="tp-minutes"></div>
                                    </div>
                                </div>
                                <div class="tp-actions">
                                    <button type="button" class="tp-now" id="tp-now">Sekarang</button>
                                    <button type="button" class="tp-clear" id="tp-clear">Hapus</button>
                                    <button type="button" class="tp-done" id="tp-done">OK</button>
                                </div>
                            </div>
                        </div>
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
    function fmtDate(value) {
        if (!value) return '-';
        const s = String(value).slice(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return String(value);
        const p = s.split('-');
        const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function fmtNum(value) {
        if (value === null || value === undefined || value === '') return '';
        const n = parseFloat(value);
        return Number.isNaN(n) ? String(value) : String(n);
    }

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
            document.getElementById('s-date').textContent = fmtDate(data.installation_date);
            document.getElementById('customer-phone').value =
                normalizePhoneInput(data.cell_phone || data.home_phone || data.office_phone);
            document.getElementById('customer-email').value = data.customer_email || '';
            document.getElementById('legacy-sales-serial').value = data.serial || '';
            const dateInput = document.getElementById('scheduled-start-at');
            if (!dateInput.value && data.installation_date) {
                setScheduledDate(data.installation_date.slice(0, 10));
            }
            preview.classList.add('visible');
            document.getElementById('spk-search').closest('.spk-search-wrap').style.display = 'none';
            if (fullAddress.trim()) autoGeocode(fullAddress);
            loadSalesDetail(data.serial, data.sales_type);
        },
        clear() {
            document.getElementById('spk-selected').classList.remove('visible');
            document.getElementById('legacy-sales-serial').value = '';
            document.getElementById('spk-search').value = '';
            document.getElementById('spk-search').closest('.spk-search-wrap').style.display = '';
            document.getElementById('sales-detail-wrap').style.display = 'none';
            document.getElementById('sales-detail-body').innerHTML = '';
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
                    <div class="ss-sub">
                        <span>👤 ${row.customer_name || '-'}</span>
                        <span>📅 ${fmtDate(row.installation_date)}</span>
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

    function escHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function loadSalesDetail(serial, salesType) {
        const wrap = document.getElementById('sales-detail-wrap');
        const body = document.getElementById('sales-detail-body');
        if (!serial) {
            wrap.style.display = 'none';
            body.innerHTML = '';
            return;
        }
        wrap.style.display = 'block';
        body.innerHTML = '<div class="ss-loading" style="padding:14px;text-align:center;">⏳ Memuat detail item…</div>';
        try {
            const res = await fetch('/dashboard/api/sales/' + encodeURIComponent(serial) + '/details');
            const payload = await res.json();
            const rows = payload.data || [];
            if (!rows.length) {
                body.innerHTML = '<div class="ss-empty" style="padding:14px;text-align:center;color:var(--muted,#64748b);font-size:13px;">Tidak ada detail item untuk SPK ini.</div>';
                return;
            }
            const isSize = String(salesType) === '3';
            const th = (label, align) =>
                `<th style="padding:9px 10px;text-align:${align || 'left'};color:var(--muted,#64748b);font-size:11px;text-transform:uppercase;letter-spacing:.03em;background:var(--surface-2,#f6f9ff);">${label}</th>`;
            const td = (value, align, bold) =>
                `<td style="padding:9px 10px;text-align:${align || 'left'};${bold ? 'font-weight:700;' : ''}">${escHtml(value) || '—'}</td>`;

            let html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            html += '<thead><tr>';
            html += th('No');
            html += th('Inventory');
            html += isSize ? th('Lebar (cm)', 'right') + th('Panjang (cm)', 'right')
                           : th('Posisi Kaca') + th('Detail Posisi');
            html += th('Qty', 'right');
            html += '</tr></thead><tbody>';

            rows.forEach((d, i) => {
                html += '<tr style="border-bottom:1px solid var(--line,#e2e8f4);">';
                html += td(i + 1, 'left', false);
                html += td(d.inventory_name || '-', 'left', true);
                if (isSize) {
                    html += td(fmtNum(d.width), 'right', false);
                    html += td(fmtNum(d.length), 'right', false);
                } else {
                    html += td(d.window_position || '-', 'left', false);
                    html += td(d.window_position_detail || '-', 'left', false);
                }
                html += td(fmtNum(d.qty), 'right', true);
                html += '</tr>';
            });

            html += '</tbody></table>';
            body.innerHTML = html;
        } catch (e) {
            body.innerHTML = '<div class="ss-empty" style="padding:14px;text-align:center;color:var(--red-500,#c8102e);font-size:13px;">⚠️ Gagal memuat detail item.</div>';
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
            if (!results.length) { if (!silent) showToast('Lokasi tidak ditemukan', 'Coba kata kunci atau alamat yang lain.', 'location-search'); return; }
            const r = results[0];
            map.flyTo([parseFloat(r.lat), parseFloat(r.lon)], zoom);
            placePin(parseFloat(r.lat), parseFloat(r.lon), true);
        } catch (err) {
            if (!silent) showToast('Gagal mencari lokasi', err.message, 'location-search');
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
       NO. WHATSAPP — DIGITS ONLY + FORMAT 08XX
    ===================================================== */
    const phoneInput = document.getElementById('customer-phone');

    function normalizePhoneInput(value) {
        let digits = String(value || '').replace(/\D/g, '');
        if (digits.length > 15) digits = digits.slice(0, 15);
        if (digits.startsWith('62') && digits.length > 2) digits = '0' + digits.slice(2);
        return digits;
    }

    phoneInput.addEventListener('input', () => {
        const normalized = normalizePhoneInput(phoneInput.value);
        if (phoneInput.value !== normalized) phoneInput.value = normalized;
    });

    /* =====================================================
       TIME PICKER POPUP (JAM PEMASANGAN)
    ===================================================== */
    const timePickerBtn = document.getElementById('time-picker-btn');
    const timePickerPopup = document.getElementById('time-picker-popup');
    const timeDisplay = document.getElementById('time-picker-display');
    const timeInput = document.getElementById('scheduled-start-time');
    const tpValue = document.getElementById('tp-value');
    let tpHour = null, tpMinute = null;

    function buildTimeOptions() {
        const hours = document.getElementById('tp-hours');
        const minutes = document.getElementById('tp-minutes');
        hours.innerHTML = '';
        minutes.innerHTML = '';
        for (let h = 0; h < 24; h++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'tp-opt';
            b.textContent = String(h).padStart(2, '0');
            b.dataset.h = h;
            b.addEventListener('click', () => { tpHour = h; renderTime(); });
            hours.appendChild(b);
        }
        for (let m = 0; m < 60; m += 5) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'tp-opt';
            b.textContent = String(m).padStart(2, '0');
            b.dataset.m = m;
            b.addEventListener('click', () => { tpMinute = m; renderTime(); });
            minutes.appendChild(b);
        }
    }

    function renderTime() {
        const val = (tpHour !== null && tpMinute !== null)
            ? String(tpHour).padStart(2, '0') + ':' + String(tpMinute).padStart(2, '0')
            : null;
        tpValue.textContent = val || '--:--';
        timeDisplay.textContent = val || 'Pilih jam';
        timeInput.value = val || '';
        document.querySelectorAll('#tp-hours .tp-opt').forEach(b =>
            b.classList.toggle('active', b.dataset.h == tpHour));
        document.querySelectorAll('#tp-minutes .tp-opt').forEach(b =>
            b.classList.toggle('active', b.dataset.m == tpMinute));
    }

    timePickerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        datePickerPopup.classList.remove('open');
        timePickerPopup.classList.toggle('open');
    });

    document.getElementById('tp-now').addEventListener('click', () => {
        const d = new Date();
        tpHour = d.getHours();
        tpMinute = d.getMinutes();
        renderTime();
    });

    document.getElementById('tp-clear').addEventListener('click', () => {
        tpHour = null;
        tpMinute = null;
        renderTime();
    });

    document.getElementById('tp-done').addEventListener('click', () => {
        timePickerPopup.classList.remove('open');
    });

    document.querySelectorAll('.tp-preset').forEach(b => b.addEventListener('click', () => {
        tpHour = +b.dataset.h;
        tpMinute = +b.dataset.m;
        renderTime();
    }));

    document.addEventListener('click', (e) => {
        if (!timePickerBtn.contains(e.target) && !timePickerPopup.contains(e.target)) {
            timePickerPopup.classList.remove('open');
        }
        if (!datePickerBtn.contains(e.target) && !datePickerPopup.contains(e.target)) {
            datePickerPopup.classList.remove('open');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            timePickerPopup.classList.remove('open');
            datePickerPopup.classList.remove('open');
        }
    });

    buildTimeOptions();

    /* =====================================================
       DATE PICKER POPUP (TANGGAL PEMASANGAN)
    ===================================================== */
    const datePickerBtn = document.getElementById('date-picker-btn');
    const datePickerPopup = document.getElementById('date-picker-popup');
    const dateDisplay = document.getElementById('date-picker-display');
    const dateInput = document.getElementById('scheduled-start-at');
    let dpView = new Date();
    let dpSelected = dateInput.value || null;

    function setScheduledDate(value) {
        dateInput.value = value || '';
        if (value) {
            const d = new Date(value + 'T00:00:00');
            dateDisplay.textContent = d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            const parts = value.split('-');
            dpView = new Date(+parts[0], +parts[1] - 1, 1);
        } else {
            dateDisplay.textContent = 'Pilih tanggal';
        }
        dpSelected = value || null;
        if (datePickerPopup.classList.contains('open')) renderDatePicker();
    }

    function renderDatePicker() {
        document.getElementById('dp-title').textContent = dpView.toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric'
        });
        const grid = document.getElementById('dp-grid');
        grid.innerHTML = '';
        const y = dpView.getFullYear(),
            m = dpView.getMonth();
        const startDow = (new Date(y, m, 1).getDay() + 6) % 7;
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const prevDays = new Date(y, m, 0).getDate();
        const now = new Date();
        const todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0');

        for (let i = 0; i < startDow; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'dp-day out';
            b.textContent = prevDays - startDow + 1 + i;
            b.disabled = true;
            grid.appendChild(b);
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const ds = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'dp-day';
            if (ds === todayStr) b.classList.add('today');
            if (ds === dpSelected) b.classList.add('selected');
            b.textContent = d;
            b.addEventListener('click', () => setScheduledDate(ds));
            grid.appendChild(b);
        }
    }

    datePickerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = !datePickerPopup.classList.contains('open');
        timePickerPopup.classList.remove('open');
        datePickerPopup.classList.toggle('open', open);
        if (open) renderDatePicker();
    });

    document.getElementById('dp-prev').addEventListener('click', () => {
        dpView = new Date(dpView.getFullYear(), dpView.getMonth() - 1, 1);
        renderDatePicker();
    });

    document.getElementById('dp-next').addEventListener('click', () => {
        dpView = new Date(dpView.getFullYear(), dpView.getMonth() + 1, 1);
        renderDatePicker();
    });

    document.getElementById('dp-today').addEventListener('click', () => {
        const t = new Date();
        const ds = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' +
            String(t.getDate()).padStart(2, '0');
        setScheduledDate(ds);
    });

    document.getElementById('dp-clear').addEventListener('click', () => setScheduledDate(null));

    document.getElementById('dp-done').addEventListener('click', () => {
        datePickerPopup.classList.remove('open');
    });

    /* =====================================================
       TOAST & VALIDATION
    ===================================================== */
    let toastTimer = null;
    function showToast(title, msg, fieldId) {
        document.getElementById('toast-v-title').textContent = title;
        document.getElementById('toast-v-msg').textContent = msg;
        const toast = document.getElementById('toast-v');
        // Hapus highlight dari field sebelumnya
        document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
        // Highlight field baru & focus
        if (fieldId) {
            const f = document.getElementById(fieldId);
            if (f) { f.classList.add('field-error'); f.focus(); f.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        }
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(hideToast, 4000);
    }
    window.hideToast = function() {
        document.getElementById('toast-v').classList.remove('show');
        clearTimeout(toastTimer);
    };

    document.getElementById('wo-form').addEventListener('submit', e => {
        const serial = document.getElementById('legacy-sales-serial').value;
        const dateVal = document.getElementById('scheduled-start-at').value;
        const checks = document.querySelectorAll('.tech-check:checked');
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        const wa = document.getElementById('customer-phone').value.trim();
        if (!serial) {
            e.preventDefault();
            showToast('Belum pilih SPK', 'Silakan cari dan pilih nomor SPK terlebih dahulu.', 'spk-search');
            return;
        }
        if (!dateVal) {
            e.preventDefault();
            showToast('Tanggal wajib diisi', 'Pilih tanggal pemasangan terlebih dahulu.', 'date-picker-btn');
            return;
        }
        if (!lat || !lng) {
            e.preventDefault();
            showToast('Lokasi belum di-pin', 'Klik pada peta atau cari lokasi untuk menandai titik pemasangan.', 'location-search');
            return;
        }
        if (!wa) {
            e.preventDefault();
            showToast('No. WA wajib diisi', 'Diperlukan untuk notifikasi live tracking ke customer.', 'customer-phone');
            return;
        }
        if (!checks.length) {
            e.preventDefault();
            showToast('Teknisi belum dipilih', 'Pilih minimal 1 teknisi untuk assignment.', 'tech-filter');
        }
    });

    loadTechnicians();
</script>
@endpush
@endsection
