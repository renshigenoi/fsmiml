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

    /* Edit Work Order Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(6, 20, 41, .55);
        z-index: 200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: #fff;
        border-radius: 16px;
        max-width: 520px;
        width: 100%;
        padding: 24px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(6, 20, 41, .30);
    }
    .modal-box h3 { margin: 0 0 4px; font-size: 18px; font-weight: 900; color: var(--ink,#0d1b35); }
    .modal-box label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--muted,#64748b);
        margin: 14px 0 6px;
    }
    .modal-actions-row { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }

    /* Edit modal: date/time pickers */
    .ew-picker-btn {
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
    }
    .ew-picker-btn:hover { border-color: var(--red-500,#c8102e); }
    .ew-popup {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 300;
        background: #fff;
        border: 1px solid var(--line,#e2e8f4);
        border-radius: 12px;
        box-shadow: 0 14px 40px rgba(11,32,68,.18);
        padding: 12px;
        width: 280px;
    }
    .ew-dp-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .ew-dp-nav {
        border: 1px solid var(--line,#e2e8f4);
        background: var(--surface-2,#f6f9ff);
        border-radius: 8px;
        width: 30px;
        height: 30px;
        cursor: pointer;
    }
    .ew-dp-title { font-weight: 800; font-size: 13px; color: var(--navy-700,#112b5c); }
    .ew-dp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 10px; }
    .ew-dp-grid > span { text-align: center; font-size: 10.5px; font-weight: 800; color: var(--muted,#64748b); }
    .ew-dp-day {
        border: 1px solid transparent;
        background: transparent;
        border-radius: 8px;
        padding: 6px 0;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-2,#2c3e65);
        cursor: pointer;
    }
    .ew-dp-day:hover { background: var(--surface-2,#f6f9ff); }
    .ew-dp-day.today { border-color: var(--red-500,#c8102e); color: var(--red-500,#c8102e); }
    .ew-dp-day.selected { background: var(--navy-700,#112b5c); border-color: var(--navy-700,#112b5c); color: #fff; }
    .ew-pop-actions { display: flex; gap: 8px; justify-content: flex-end; }
    .ew-tp-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
    .ew-tp-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 800;
        color: var(--muted,#64748b);
        margin-bottom: 6px;
    }
    .ew-tp-options { display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; max-height: 160px; overflow-y: auto; }
    .ew-tp-opt {
        border: 1px solid var(--line,#e2e8f4);
        background: #fff;
        border-radius: 8px;
        padding: 6px 0;
        font-size: 13px;
        font-weight: 700;
        color: var(--ink-2,#2c3e65);
        cursor: pointer;
    }
    .ew-tp-opt:hover { border-color: var(--red-500,#c8102e); color: var(--red-500,#c8102e); }
    .ew-tp-opt.active { background: var(--navy-700,#112b5c); border-color: var(--navy-700,#112b5c); color: #fff; }
    .ew-tech-list {
        max-height: 180px;
        overflow-y: auto;
        border: 1px solid var(--line,#e2e8f4);
        border-radius: 10px;
        padding: 8px;
    }
    .ew-tech-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 6px;
        border-radius: 8px;
        cursor: pointer;
    }
    .ew-tech-item:hover { background: var(--surface-2,#f6f9ff); }
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
            <button type="button" class="btn btn-sm" id="btn-edit-wo"
                style="background:var(--red-500,#c8102e);color:#fff;">✏️ Edit</button>
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
                    <div class="item-row" style="flex-wrap:wrap;row-gap:4px;">
                        <span class="item-name">{{ $item->product_name }}</span>
                        <span class="item-qty">× {{ $item->quantity }}</span>
                        @if ($item->window_film_desc)
                            <span class="item-qty" style="width:100%;color:var(--muted,#64748b);">🪟 {{ $item->window_film_desc }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Sales Detail (legacy SHOW_SalesDetail) --}}
        @if (!empty($salesDetails))
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-ico" style="background:linear-gradient(135deg,#0891b2,#155e75);">🪟</div>
                <h3>Detail Item Pemasangan</h3>
            </div>
            <div class="info-card-body" style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                    <thead>
                        <tr style="background:var(--surface-2,#f6f9ff);">
                            <th style="padding:9px 10px;text-align:left;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">No</th>
                            <th style="padding:9px 10px;text-align:left;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">Inventory</th>
                            @if ((string) $salesType === '3')
                                <th style="padding:9px 10px;text-align:right;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">Lebar (cm)</th>
                                <th style="padding:9px 10px;text-align:right;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">Panjang (cm)</th>
                            @else
                                <th style="padding:9px 10px;text-align:left;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">Posisi Kaca</th>
                                <th style="padding:9px 10px;text-align:left;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">Detail Posisi</th>
                            @endif
                            <th style="padding:9px 10px;text-align:right;color:var(--muted,#64748b);font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salesDetails as $idx => $detail)
                            <tr style="border-bottom:1px solid var(--line,#e2e8f4);">
                                <td style="padding:9px 10px;color:var(--muted,#64748b);">{{ $idx + 1 }}</td>
                                <td style="padding:9px 10px;font-weight:700;color:var(--ink-2,#2c3e65);">{{ $detail['inventory_name'] ?? '—' }}</td>
                                @if ((string) $salesType === '3')
                                    <td style="padding:9px 10px;text-align:right;">{{ $detail['width'] ?? '—' }}</td>
                                    <td style="padding:9px 10px;text-align:right;">{{ $detail['length'] ?? '—' }}</td>
                                @else
                                    <td style="padding:9px 10px;">{{ $detail['window_position'] ?? '—' }}</td>
                                    <td style="padding:9px 10px;">{{ $detail['window_position_detail'] ?? '—' }}</td>
                                @endif
                                <td style="padding:9px 10px;text-align:right;font-weight:700;">{{ $detail['qty'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

{{-- ===== Edit Work Order Modal ===== --}}
<div id="edit-wo-modal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Edit Work Order</h3>
        <p style="margin:0 0 6px;font-size:13px;color:var(--muted,#64748b);">
            Nomor SPK tidak dapat diubah.
        </p>
        <form method="POST" action="{{ route('dashboard.work-orders.update', $workOrder) }}">
            @csrf
            <label>Nomor SPK</label>
            <input type="text" value="{{ $workOrder->number }}" disabled>

            <label>Item Pekerjaan</label>
            <div class="ew-tech-list" style="max-height:none;margin-bottom:2px;">
                @forelse ($workOrder->items as $item)
                    <div style="padding:8px 6px;display:flex;justify-content:space-between;gap:8px;border-bottom:1px solid var(--line,#e2e8f4);">
                        <div>
                            <div style="font-weight:700;font-size:14px;">{{ $item->product_name }}</div>
                            @if ($item->window_film_desc)
                                <div style="font-size:12.5px;color:var(--muted,#64748b);margin-top:2px;">🪟 Jenis Film: {{ $item->window_film_desc }}</div>
                            @endif
                        </div>
                        <div style="flex-shrink:0;font-weight:800;color:var(--navy-700,#112b5c);">× {{ $item->quantity }}</div>
                    </div>
                @empty
                    <div style="padding:10px;color:var(--muted,#64748b);">Belum ada item pekerjaan.</div>
                @endforelse
            </div>

            <label>Tanggal Pemasangan</label>
            <div class="ew-pick" style="position:relative;">
                <button type="button" id="ew-date-btn" class="ew-picker-btn">
                    📅 <span id="ew-date-label">Pilih tanggal</span>
                </button>
                <input type="hidden" name="scheduled_start_at" id="ew-scheduled-start-at"
                    value="{{ $workOrder->scheduled_start_at?->format('Y-m-d') }}">
                <div id="ew-date-popup" class="ew-popup" style="display:none;">
                    <div class="ew-dp-head">
                        <button type="button" class="ew-dp-nav" id="ew-dp-prev">‹</button>
                        <span class="ew-dp-title" id="ew-dp-title"></span>
                        <button type="button" class="ew-dp-nav" id="ew-dp-next">›</button>
                    </div>
                    <div class="ew-dp-grid" id="ew-dp-grid"></div>
                    <div class="ew-pop-actions">
                        <button type="button" class="btn btn-sm" id="ew-dp-today">Hari Ini</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="ew-dp-close">OK</button>
                    </div>
                </div>
            </div>

            <label>Jam Pemasangan</label>
            <div class="ew-pick" style="position:relative;">
                <button type="button" id="ew-time-btn" class="ew-picker-btn">
                    🕐 <span id="ew-time-label">Pilih jam</span>
                </button>
                <input type="hidden" name="scheduled_start_time" id="ew-scheduled-start-time"
                    value="{{ $workOrder->scheduled_start_at?->format('H:i') }}">
                <div id="ew-time-popup" class="ew-popup" style="display:none;">
                    <div class="ew-tp-cols">
                        <div>
                            <div class="ew-tp-title">Jam</div>
                            <div class="ew-tp-options" id="ew-tp-hours"></div>
                        </div>
                        <div>
                            <div class="ew-tp-title">Menit</div>
                            <div class="ew-tp-options" id="ew-tp-minutes"></div>
                        </div>
                    </div>
                    <div class="ew-pop-actions">
                        <button type="button" class="btn btn-sm" id="ew-tp-now">Sekarang</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="ew-tp-close">OK</button>
                    </div>
                </div>
            </div>

            <label>Catatan</label>
            <textarea name="notes" rows="2" placeholder="Opsional...">{{ $workOrder->notes }}</textarea>

            <label>Lokasi Pemasangan (klik peta / cari)</label>
            <div id="ew-location-map" style="height:220px;border:1.5px solid var(--line,#e2e8f4);border-radius:10px;z-index:0;"></div>
            <div style="display:flex;gap:8px;margin-top:8px;">
                <input type="text" id="ew-location-search" placeholder="Cari lokasi / alamat..." style="flex:1;margin-bottom:0;">
                <button type="button" class="btn btn-sm" id="ew-location-search-btn">Cari</button>
            </div>
            <input type="hidden" name="latitude" id="ew-latitude" value="{{ $workOrder->serviceLocation?->latitude }}">
            <input type="hidden" name="longitude" id="ew-longitude" value="{{ $workOrder->serviceLocation?->longitude }}">
            <label style="margin-top:12px;">Alamat Lengkap</label>
            <input type="text" name="location_address" id="ew-location-address"
                value="{{ $workOrder->serviceLocation?->address }}">

            <label>Teknisi</label>
            <div id="ew-tech-list" class="ew-tech-list">Memuat daftar teknisi…</div>

            <label>No. WhatsApp Customer</label>
            <input type="tel" name="customer_phone" value="{{ $workOrder->customer?->phone }}">

            <label>Email Customer</label>
            <input type="email" name="customer_email" value="{{ $workOrder->customer?->email }}">

            <div class="modal-actions-row">
                <button type="button" class="btn btn-secondary" id="btn-cancel-edit">Batal</button>
                <button type="submit" class="btn" style="background:var(--red-500,#c8102e);color:#fff;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('btn-edit-wo').addEventListener('click', function () {
            document.getElementById('edit-wo-modal').style.display = 'flex';
            setTimeout(function () {
                if (typeof ewMap !== 'undefined') {
                    if (ewMap) ewMap.invalidateSize();
                    else ewInitMap();
                }
            }, 80);
        });
        document.getElementById('btn-cancel-edit').addEventListener('click', function () {
            document.getElementById('edit-wo-modal').style.display = 'none';
        });
        document.getElementById('edit-wo-modal').addEventListener('click', function (e) {
            if (e.target.id === 'edit-wo-modal') {
                document.getElementById('edit-wo-modal').style.display = 'none';
            }
        });

        // ===== Edit modal: date & time pickers =====
        const ewDateInput = document.getElementById('ew-scheduled-start-at');
        const ewTimeInput = document.getElementById('ew-scheduled-start-time');
        const ewDateLabel = document.getElementById('ew-date-label');
        const ewTimeLabel = document.getElementById('ew-time-label');
        const ewDatePopup = document.getElementById('ew-date-popup');
        const ewTimePopup = document.getElementById('ew-time-popup');
        const ewDpGrid = document.getElementById('ew-dp-grid');
        let ewDpView = ewDateInput.value ? new Date(ewDateInput.value + 'T00:00:00') : new Date();
        let ewDpSelected = ewDateInput.value || null;
        let ewTpHour = null;
        let ewTpMinute = null;

        function ewFmtLabel() {
            ewDateLabel.textContent = ewDpSelected
                ? new Date(ewDpSelected + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                : 'Pilih tanggal';
            ewTimeLabel.textContent = (ewTpHour !== null && ewTpMinute !== null)
                ? String(ewTpHour).padStart(2, '0') + ':' + String(ewTpMinute).padStart(2, '0')
                : 'Pilih jam';
        }
        (function () {
            if (ewDateInput.value) {
                ewDateLabel.textContent = new Date(ewDateInput.value + 'T00:00:00')
                    .toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            }
            const tv = ewTimeInput.value || '';
            if (tv) {
                const p = tv.split(':');
                ewTpHour = +p[0];
                ewTpMinute = +p[1];
                ewTimeLabel.textContent = tv;
            }
        })();

        function ewRenderDate() {
            document.getElementById('ew-dp-title').textContent =
                ewDpView.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            const y = ewDpView.getFullYear();
            const m = ewDpView.getMonth();
            const startDow = (new Date(y, m, 1).getDay() + 6) % 7;
            const days = new Date(y, m + 1, 0).getDate();
            const now = new Date();
            const todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            let html = '<span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>';
            for (let i = 0; i < startDow; i++) html += '<span></span>';
            for (let d = 1; d <= days; d++) {
                const ds = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                html += '<button type="button" class="ew-dp-day' + (ds === todayStr ? ' today' : '') + (ds === ewDpSelected ? ' selected' : '') + '" data-d="' + ds + '">' + d + '</button>';
            }
            ewDpGrid.innerHTML = html;
        }
        ewDpGrid.addEventListener('click', function (e) {
            const b = e.target.closest('.ew-dp-day');
            if (!b) return;
            ewDpSelected = b.dataset.d;
            ewDateInput.value = ewDpSelected;
            ewFmtLabel();
            ewRenderDate();
        });
        document.getElementById('ew-dp-prev').addEventListener('click', function () {
            ewDpView = new Date(ewDpView.getFullYear(), ewDpView.getMonth() - 1, 1);
            ewRenderDate();
        });
        document.getElementById('ew-dp-next').addEventListener('click', function () {
            ewDpView = new Date(ewDpView.getFullYear(), ewDpView.getMonth() + 1, 1);
            ewRenderDate();
        });
        document.getElementById('ew-dp-today').addEventListener('click', function () {
            const t = new Date();
            ewDpSelected = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
            ewDateInput.value = ewDpSelected;
            ewFmtLabel();
            ewRenderDate();
        });
        document.getElementById('ew-dp-close').addEventListener('click', function () {
            ewDatePopup.style.display = 'none';
        });
        document.getElementById('ew-date-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            ewTimePopup.style.display = 'none';
            ewDatePopup.style.display = ewDatePopup.style.display === 'none' ? 'block' : 'none';
            if (ewDatePopup.style.display === 'block') ewRenderDate();
        });

        function ewRenderTime() {
            const hours = document.getElementById('ew-tp-hours');
            const minutes = document.getElementById('ew-tp-minutes');
            let hh = '';
            let mm = '';
            for (let h = 0; h < 24; h++) {
                hh += '<button type="button" class="ew-tp-opt' + (h === ewTpHour ? ' active' : '') + '" data-h="' + h + '">' + String(h).padStart(2, '0') + '</button>';
            }
            for (let m = 0; m < 60; m += 5) {
                mm += '<button type="button" class="ew-tp-opt' + (m === ewTpMinute ? ' active' : '') + '" data-m="' + m + '">' + String(m).padStart(2, '0') + '</button>';
            }
            hours.innerHTML = hh;
            minutes.innerHTML = mm;
        }
        document.getElementById('ew-tp-hours').addEventListener('click', function (e) {
            const b = e.target.closest('.ew-tp-opt');
            if (!b) return;
            ewTpHour = +b.dataset.h;
            ewFmtLabel();
            ewRenderTime();
        });
        document.getElementById('ew-tp-minutes').addEventListener('click', function (e) {
            const b = e.target.closest('.ew-tp-opt');
            if (!b) return;
            ewTpMinute = +b.dataset.m;
            ewFmtLabel();
            ewRenderTime();
        });
        document.getElementById('ew-tp-now').addEventListener('click', function () {
            const t = new Date();
            ewTpHour = t.getHours();
            ewTpMinute = t.getMinutes();
            ewFmtLabel();
            ewRenderTime();
        });
        document.getElementById('ew-tp-close').addEventListener('click', function () {
            ewTimePopup.style.display = 'none';
        });
        document.getElementById('ew-time-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            ewDatePopup.style.display = 'none';
            ewTimePopup.style.display = ewTimePopup.style.display === 'none' ? 'block' : 'none';
            if (ewTimePopup.style.display === 'block') ewRenderTime();
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.ew-pick')) {
                ewDatePopup.style.display = 'none';
                ewTimePopup.style.display = 'none';
            }
        });

        // ===== Edit modal: map & pin =====
        const ewLatInput = document.getElementById('ew-latitude');
        const ewLngInput = document.getElementById('ew-longitude');
        const ewAddrInput = document.getElementById('ew-location-address');
        let ewMap = null;
        let ewPin = null;

        function ewInitMap() {
            const el = document.getElementById('ew-location-map');
            if (ewMap || !el || typeof L === 'undefined') return;
            const hasCoord = ewLatInput.value && ewLngInput.value;
            const lat = hasCoord ? parseFloat(ewLatInput.value) : -2.5489;
            const lng = hasCoord ? parseFloat(ewLngInput.value) : 118.0149;
            ewMap = L.map('ew-location-map').setView([lat, lng], hasCoord ? 14 : 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(ewMap);
            if (hasCoord) ewSetPin(lat, lng);
            ewMap.on('click', function (e) {
                ewSetPin(e.latlng.lat, e.latlng.lng);
            });
        }
        function ewSetPin(lat, lng) {
            ewLatInput.value = lat.toFixed(7);
            ewLngInput.value = lng.toFixed(7);
            if (ewPin) {
                ewPin.setLatLng([lat, lng]);
            } else {
                ewPin = L.marker([lat, lng], { draggable: true }).addTo(ewMap);
            }
            ewPin.on('dragend', function () {
                const p = ewPin.getLatLng();
                ewLatInput.value = p.lat.toFixed(7);
                ewLngInput.value = p.lng.toFixed(7);
                ewReverse(p.lat, p.lng);
            });
            ewReverse(lat, lng);
        }
        async function ewReverse(lat, lng) {
            try {
                const res = await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&zoom=16');
                const data = await res.json();
                if (data && data.display_name) ewAddrInput.value = data.display_name;
            } catch (_) {}
        }
        async function ewSearch(query) {
            if (!query.trim()) return;
            try {
                const res = await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&q=' + encodeURIComponent(query) + '&limit=1&countrycodes=id');
                const data = await res.json();
                if (!data.length) {
                    alert('Lokasi tidak ditemukan.');
                    return;
                }
                const r = data[0];
                ewMap.flyTo([parseFloat(r.lat), parseFloat(r.lon)], 16);
                ewSetPin(parseFloat(r.lat), parseFloat(r.lon));
            } catch (_) {}
        }
        document.getElementById('ew-location-search-btn').addEventListener('click', function () {
            ewSearch(document.getElementById('ew-location-search').value);
        });
        document.getElementById('ew-location-search').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ewSearch(this.value);
            }
        });

        // ===== Edit modal: technicians =====
        const ewCurrentSerials = @json($workOrder->assignments->pluck('technician.external_serial')->filter()->values());
        const ewAcceptedSerials = @json(
            $workOrder->assignments
                ->filter(fn ($a) => $a->status === \App\Modules\Assignment\Enums\AssignmentStatus::Accepted)
                ->pluck('technician.external_serial')
                ->filter()
                ->values()
        );
        (async function () {
            try {
                const res = await fetch('/dashboard/api/technicians');
                const payload = await res.json();
                const list = document.getElementById('ew-tech-list');
                if (!payload.data || !payload.data.length) {
                    list.innerHTML = '<div style="padding:10px;color:var(--muted,#64748b);">Tidak ada teknisi terdaftar.</div>';
                    return;
                }
                list.innerHTML = '';
                payload.data.forEach(function (t) {
                    const checked = ewCurrentSerials.indexOf(String(t.serial)) !== -1;
                    const locked = ewAcceptedSerials.indexOf(String(t.serial)) !== -1;
                    const label = document.createElement('label');
                    label.className = 'ew-tech-item';
                    label.innerHTML = (locked
                        ? '<input type="hidden" name="technician_legacy_serials[]" value="' + t.serial + '">'
                        : '') +
                        '<input type="checkbox" name="technician_legacy_serials[]" value="' + t.serial + '"' +
                        (checked ? ' checked' : '') + (locked ? ' disabled' : '') + '> <span>' +
                        (t.full_name || '-') + '</span>' +
                        (locked ? ' <em style="font-size:11px;color:var(--muted,#64748b);font-style:normal;">sudah menerima</em>' : '');
                    list.appendChild(label);
                });
            } catch (e) {
                document.getElementById('ew-tech-list').innerHTML =
                    '<div style="padding:10px;color:var(--red-500,#c8102e);">Gagal memuat daftar teknisi.</div>';
            }
        })();
    </script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if ($workOrder->serviceLocation?->latitude)
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
