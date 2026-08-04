@extends('layouts.app')

@section('title', 'Work Order '.$workOrder->number)

@section('content')
<style>
    /* ---- Layout ---- */
    .wo-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) { .wo-layout { grid-template-columns: 1fr; } }

    /* ---- Page header ---- */
    .wo-page-header {
        background: linear-gradient(135deg, var(--navy-900,#061429), var(--navy-700,#112b5c));
        border-radius: 18px;
        padding: 22px 26px;
        color: #fff;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(11,32,68,.22);
    }
    .wo-page-header::before {
        content: '';
        position: absolute;
        width: 260px; height: 260px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200,16,46,.14) 0%, transparent 70%);
        top: -100px; right: 20px;
        pointer-events: none;
    }
    .wo-hdr-left { position: relative; z-index: 1; }
    .wo-hdr-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: rgba(255,255,255,.60);
        text-decoration: none;
        font-size: 12.5px;
        font-weight: 600;
        margin-bottom: 10px;
        transition: color .15s;
    }
    .wo-hdr-back:hover { color: #fff; }
    .wo-hdr-number { font-size: 24px; font-weight: 900; letter-spacing: -.5px; }
    .wo-hdr-sub { color: rgba(255,255,255,.60); font-size: 13px; margin-top: 5px; }
    .wo-hdr-right { position: relative; z-index: 1; text-align: right; }
    .wo-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 999px;
        font-size: 13.5px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .wo-hdr-actions { display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; }

    /* ---- Info card ---- */
    .info-card {
        background: #fff;
        border: 1px solid var(--line,#e2e8f4);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 1px 4px rgba(11,32,68,.06);
    }
    .info-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        background: var(--surface-2,#f6f9ff);
        border-bottom: 1px solid var(--line,#e2e8f4);
    }
    .info-card-head .ic-ico {
        width: 36px; height: 36px;
        border-radius: 9px;
        background: linear-gradient(135deg,var(--navy-700,#112b5c),var(--navy-600,#1a3a7a));
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        box-shadow: 0 3px 8px rgba(11,32,68,.18);
        flex-shrink: 0;
    }
    .info-card-head h3 { margin: 0; font-size: 14px; font-weight: 800; color: var(--ink,#0d1b35); }
    .info-card-body { padding: 18px 20px; }

    /* ---- KV rows ---- */
    .kv-list { display: flex; flex-direction: column; gap: 0; }
    .kv-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 11px 0;
        border-bottom: 1px solid var(--surface-2,#f6f9ff);
        font-size: 14px;
    }
    .kv-row:last-child { border-bottom: 0; padding-bottom: 0; }
    .kv-row .kv-k { color: var(--muted,#64748b); font-size: 13px; flex-shrink: 0; min-width: 120px; }
    .kv-row .kv-v { font-weight: 600; color: var(--ink-2,#2c3e65); text-align: right; }

    /* ---- Timeline ---- */
    .timeline { display: flex; flex-direction: column; gap: 0; }
    .tl-item {
        display: flex;
        gap: 14px;
        padding-bottom: 18px;
        position: relative;
    }
    .tl-item:last-child { padding-bottom: 0; }
    .tl-item::after {
        content: '';
        position: absolute;
        left: 15px; top: 30px;
        bottom: 0;
        width: 2px;
        background: var(--line,#e2e8f4);
    }
    .tl-item:last-child::after { display: none; }
    .tl-dot {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        border: 2px solid transparent;
        box-shadow: 0 2px 8px rgba(11,32,68,.12);
    }
    .tl-content { flex: 1; padding-top: 4px; }
    .tl-status { font-size: 13.5px; font-weight: 800; color: var(--ink,#0d1b35); }
    .tl-from { font-size: 12px; color: var(--muted,#64748b); margin-top: 1px; }
    .tl-date { font-size: 11.5px; color: var(--muted,#64748b); margin-top: 3px; }
    .tl-reason { font-size: 12px; color: var(--ink-2,#2c3e65); margin-top: 4px; background: var(--surface-2,#f6f9ff); border-radius: 7px; padding: 6px 10px; }

    /* ---- Technician card ---- */
    .tech-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 0;
        border-bottom: 1px solid var(--surface-2,#f6f9ff);
    }
    .tech-row:last-child { border-bottom: 0; padding-bottom: 0; }
    .tech-avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg,var(--navy-700,#112b5c),var(--navy-500,#2451a0));
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 900;
        flex-shrink: 0;
        box-shadow: 0 3px 10px rgba(11,32,68,.20);
    }
    .tech-info { flex: 1; min-width: 0; }
    .tech-name { font-weight: 700; font-size: 14px; color: var(--ink,#0d1b35); }
    .tech-times { font-size: 12px; color: var(--muted,#64748b); margin-top: 2px; }

    /* ---- Map ---- */
    #wo-map { height: 220px; border-radius: 10px; border: 1px solid var(--line,#e2e8f4); }
    .map-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 8px 14px;
        background: var(--navy-100,#dce9fc);
        color: var(--navy-700,#112b5c);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: background .15s;
    }
    .map-link:hover { background: var(--navy-300,#6a9ae8); color: #fff; }

    /* ---- Items table ---- */
    .item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--surface-2,#f6f9ff);
        font-size: 14px;
        gap: 12px;
    }
    .item-row:last-child { border-bottom: 0; }
    .item-name { font-weight: 600; color: var(--ink,#0d1b35); }
    .item-qty { background: var(--navy-100,#dce9fc); color: var(--navy-700,#112b5c); border-radius: 999px; padding: 3px 10px; font-size: 12.5px; font-weight: 800; }
</style>

{{-- ===== Page Header ===== --}}
<div class="wo-page-header">
    <div class="wo-hdr-left">
        <a href="{{ route('dashboard.work-orders') }}" class="wo-hdr-back">← Kembali ke Daftar</a>
        <div class="wo-hdr-number">{{ $workOrder->number }}</div>
        <div class="wo-hdr-sub">
            Work Order · Dibuat {{ $workOrder->created_at->format('d M Y, H:i') }}
        </div>
    </div>
    <div class="wo-hdr-right">
        <div>
            <span class="badge b-{{ \App\Support\StatusMap::color($workOrder->status->value) }} wo-status-badge">
                {{ \App\Support\StatusMap::label($workOrder->status->value) }}
            </span>
        </div>
        <div class="wo-hdr-actions">
            @if ($workOrder->serviceLocation?->latitude)
                <a href="https://www.google.com/maps?q={{ $workOrder->serviceLocation->latitude }},{{ $workOrder->serviceLocation->longitude }}"
                   target="_blank" rel="noopener"
                   class="btn btn-sm"
                   style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;box-shadow:none;">
                    🗺️ Google Maps
                </a>
            @endif
        </div>
    </div>
</div>

{{-- ===== Main Grid ===== --}}
<div class="wo-layout">

    {{-- === LEFT COLUMN === --}}
    <div>

        {{-- Customer Info --}}
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico">👤</div>
                <h3>Informasi Customer</h3>
            </div>
            <div class="info-card-body">
                <div class="kv-list">
                    <div class="kv-row">
                        <span class="kv-k">Nama Customer</span>
                        <span class="kv-v" style="font-size:15px;">{{ $workOrder->customer?->name ?? '-' }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">No. Telepon</span>
                        <span class="kv-v">
                            @if ($workOrder->customer?->phone)
                                <a href="tel:{{ $workOrder->customer->phone }}" style="color:var(--navy-700,#112b5c);text-decoration:none;">📱 {{ $workOrder->customer->phone }}</a>
                            @else —
                            @endif
                        </span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">Tanggal Pasang</span>
                        <span class="kv-v">{{ $workOrder->scheduled_start_at?->format('d M Y, H:i') ?? '-' }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">Catatan</span>
                        <span class="kv-v" style="text-align:right;font-weight:400;color:var(--ink-2);">{{ $workOrder->notes ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Technician Assignments --}}
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico">👷</div>
                <h3>Tim Teknisi & Assignment</h3>
            </div>
            <div class="info-card-body">
                @if ($workOrder->assignments->isEmpty())
                    <div class="empty" style="padding:20px 0;">Belum ada teknisi yang ditugaskan.</div>
                @else
                    @foreach ($workOrder->assignments as $assignment)
                        <div class="tech-row">
                            <div class="tech-avatar">
                                {{ mb_strtoupper(mb_substr($assignment->technician?->user?->name ?? 'T', 0, 1)) }}
                            </div>
                            <div class="tech-info">
                                <div class="tech-name">{{ $assignment->technician?->user?->name ?? '-' }}</div>
                                <div class="tech-times">
                                    Ditugaskan: {{ $assignment->assigned_at?->format('d M Y, H:i') ?? '-' }}
                                    @if ($assignment->responded_at)
                                        · Respon: {{ $assignment->responded_at->format('d M Y, H:i') }}
                                    @endif
                                </div>
                            </div>
                            <div>
                                @php
                                    $st = $assignment->status->value;
                                    $cls = match($st) {
                                        'accepted'   => 'b-green',
                                        'superseded' => 'b-gray',
                                        default      => 'b-amber',
                                    };
                                @endphp
                                <span class="badge {{ $cls }}">{{ ucfirst($st) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Work Items --}}
        @if ($workOrder->items->isNotEmpty())
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico">📦</div>
                <h3>Item Pekerjaan</h3>
            </div>
            <div class="info-card-body">
                @foreach ($workOrder->items as $item)
                    <div class="item-row">
                        <span class="item-name">{{ $item->product_name }}</span>
                        <span class="item-qty">× {{ $item->quantity }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Status History Timeline --}}
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">🕐</div>
                <h3>Riwayat Status</h3>
            </div>
            <div class="info-card-body">
                @if ($workOrder->statusHistories->isEmpty())
                    <div class="empty" style="padding:16px 0;">Belum ada riwayat perubahan status.</div>
                @else
                    <div class="timeline">
                        @foreach ($workOrder->statusHistories->sortByDesc('occurred_at') as $history)
                            @php
                                $clr = match(true) {
                                    str_contains($history->to_status->value, 'finish') => '#059669',
                                    str_contains($history->to_status->value, 'cancel') => '#64748b',
                                    str_contains($history->to_status->value, 'fail')   => '#dc2626',
                                    str_contains($history->to_status->value, 'way')    => '#7c3aed',
                                    default => '#0284c7',
                                };
                            @endphp
                            <div class="tl-item">
                                <div class="tl-dot" style="background:{{ $clr }}22;border-color:{{ $clr }};">
                                    <span style="font-size:13px;">
                                        @if(str_contains($history->to_status->value,'finish')) ✅
                                        @elseif(str_contains($history->to_status->value,'cancel')) ⛔
                                        @elseif(str_contains($history->to_status->value,'fail')) ⚠️
                                        @elseif(str_contains($history->to_status->value,'way')) 🚗
                                        @else 🔵
                                        @endif
                                    </span>
                                </div>
                                <div class="tl-content">
                                    <div class="tl-status">{{ \App\Support\StatusMap::label($history->to_status->value) }}</div>
                                    @if ($history->from_status !== null)
                                        <div class="tl-from">dari: {{ \App\Support\StatusMap::label($history->from_status->value) }}</div>
                                    @endif
                                    <div class="tl-date">{{ $history->occurred_at?->format('d M Y, H:i') }}</div>
                                    @if ($history->reason)
                                        <div class="tl-reason">💬 {{ $history->reason }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- /left --}}

    {{-- === RIGHT COLUMN === --}}
    <div>

        {{-- Location card --}}
        @if ($workOrder->serviceLocation)
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico" style="background:linear-gradient(135deg,#059669,#047857);">📍</div>
                <h3>Lokasi Pemasangan</h3>
            </div>
            <div class="info-card-body">
                <div class="kv-list" style="margin-bottom:14px;">
                    <div class="kv-row">
                        <span class="kv-k">Alamat</span>
                        <span class="kv-v" style="text-align:left;font-weight:500;font-size:13px;">{{ $workOrder->serviceLocation->address }}</span>
                    </div>
                    @if ($workOrder->serviceLocation->city)
                    <div class="kv-row">
                        <span class="kv-k">Kota</span>
                        <span class="kv-v">{{ $workOrder->serviceLocation->city }}</span>
                    </div>
                    @endif
                    @if ($workOrder->serviceLocation->latitude)
                    <div class="kv-row">
                        <span class="kv-k">Koordinat</span>
                        <span class="kv-v" style="font-size:12px;font-family:monospace;">
                            {{ $workOrder->serviceLocation->latitude }},<br>
                            {{ $workOrder->serviceLocation->longitude }}
                        </span>
                    </div>
                    @endif
                </div>

                @if ($workOrder->serviceLocation->latitude)
                    <div id="wo-map"></div>
                    <div class="map-note" id="wo-map-note" style="margin-top:10px;font-size:12.5px;color:var(--muted,#64748b);">
                        @if ($workOrder->status->value === 'on_the_way')
                            Menunggu sinyal lokasi teknisi...
                        @else
                            Posisi teknisi tidak aktif saat ini.
                        @endif
                    </div>
                    <a href="https://www.google.com/maps?q={{ $workOrder->serviceLocation->latitude }},{{ $workOrder->serviceLocation->longitude }}"
                       target="_blank" rel="noopener" class="map-link">
                        🗺️ Buka di Google Maps ↗
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Quick summary --}}
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico" style="background:linear-gradient(135deg,var(--red-500,#c8102e),var(--red-700,#8b0c1e));">📊</div>
                <h3>Ringkasan WO</h3>
            </div>
            <div class="info-card-body">
                <div class="kv-list">
                    <div class="kv-row">
                        <span class="kv-k">Jumlah Teknisi</span>
                        <span class="kv-v">{{ $workOrder->assignments->count() }} orang</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">Jumlah Item</span>
                        <span class="kv-v">{{ $workOrder->items->count() }} item</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">Riwayat Status</span>
                        <span class="kv-v">{{ $workOrder->statusHistories->count() }} perubahan</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">Dibuat</span>
                        <span class="kv-v">{{ $workOrder->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-k">Diperbarui</span>
                        <span class="kv-v">{{ $workOrder->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /right --}}
</div>

@push('scripts')
@if ($workOrder->serviceLocation?->latitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
        const lat = {{ $workOrder->serviceLocation->latitude }};
        const lng = {{ $workOrder->serviceLocation->longitude }};
        const initial = @json($currentLocation ?? null);
        let map = null,
            posMarker = null,
            posAccuracy = null;

        function initMap() {
            if (map || typeof L === 'undefined') return;
            const el = document.getElementById('wo-map');
            if (!el) return;
            map = L.map('wo-map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            L.marker([lat, lng]).addTo(map)
                .bindPopup('{{ addslashes($workOrder->serviceLocation->address) }}')
                .openPopup();
        }

        function updatePos(loc) {
            if (!loc || !loc.latitude || !loc.longitude) return;
            if (!map) initMap();
            if (!map) return;
            const pLat = parseFloat(loc.latitude),
                pLng = parseFloat(loc.longitude);
            if (posMarker) {
                posMarker.setLatLng([pLat, pLng]);
            } else {
                posMarker = L.circleMarker([pLat, pLng], {
                    radius: 9,
                    color: '#c8102e',
                    fillColor: '#c8102e',
                    fillOpacity: .45
                }).addTo(map);
            }
            if (loc.accuracy_meters) {
                if (posAccuracy) posAccuracy.setLatLng([pLat, pLng]).setRadius(parseFloat(loc.accuracy_meters));
                else posAccuracy = L.circle([pLat, pLng], {
                    radius: parseFloat(loc.accuracy_meters),
                    color: '#c8102e',
                    fillColor: '#c8102e',
                    fillOpacity: .08,
                    weight: 1
                }).addTo(map);
            }
            const bounds = L.latLngBounds([
                [pLat, pLng]
            ]);
            bounds.extend([lat, lng]);
            map.fitBounds(bounds.pad(0.35), { maxZoom: 16 });
            const stamp = loc.recorded_at || loc.received_at;
            const note = document.getElementById('wo-map-note');
            if (note) {
                note.innerHTML = '<span style="color:#059669;font-weight:700;">● Teknisi dalam perjalanan</span> · update ' +
                    (stamp ? new Date(stamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : '...');
            }
        }

        initMap();
        if (initial && initial.latitude) updatePos(initial);

        if (window.Echo) {
            window.Echo.private('work-order.{{ $workOrder->getKey() }}')
                .listen('.tracking.location.updated', (payload) => updatePos(payload));
        }
    })();
</script>
@endif
@endpush
@endsection
