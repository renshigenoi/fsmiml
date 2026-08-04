@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    /* ---- Hero welcome banner ---- */
    .dash-hero {
        background: linear-gradient(135deg, var(--navy-900,#061429) 0%, var(--navy-700,#112b5c) 60%, var(--navy-600,#1a3a7a) 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(11,32,68,.22);
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200,16,46,.18) 0%, transparent 70%);
        top: -120px; right: 0;
        pointer-events: none;
    }
    .dash-hero::after {
        content: '';
        position: absolute;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(58,107,200,.14) 0%, transparent 70%);
        bottom: -60px; left: 40px;
        pointer-events: none;
    }
    .dash-hero-text { position: relative; z-index: 1; }
    .dash-hero-text h2 { margin: 0 0 6px; font-size: 22px; font-weight: 900; letter-spacing: -.4px; }
    .dash-hero-text p  { margin: 0; color: rgba(255,255,255,.65); font-size: 14px; }
    .dash-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(200,16,46,.22);
        border: 1px solid rgba(200,16,46,.35);
        border-radius: 999px;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 700;
        color: #ffb3bc;
        margin-top: 12px;
    }
    .live-dot { width: 7px; height: 7px; border-radius: 50%; background: #ff6b7a; animation: dot-pulse 1.4s ease-in-out infinite; }
    @keyframes dot-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.8)} }

    /* ---- Stat grid ---- */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
        margin-bottom: 28px;
    }
    @media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px)  { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

    .stat-card {
        background: #fff;
        border: 1px solid var(--line,#e2e8f4);
        border-radius: 16px;
        padding: 18px 18px 14px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(11,32,68,.06);
        cursor: default;
        transition: transform .18s, box-shadow .18s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(11,32,68,.10); }
    .stat-card .sc-icon { font-size: 24px; display: block; margin-bottom: 12px; }
    .stat-card .sc-num { font-size: 34px; font-weight: 900; letter-spacing: -1.5px; line-height: 1; margin-bottom: 5px; }
    .stat-card .sc-lbl { font-size: 12px; color: var(--muted,#64748b); font-weight: 500; line-height: 1.3; }
    .stat-card .sc-bar { position: absolute; bottom: 0; left: 0; height: 3px; width: 100%; }

    .stat-card.amber .sc-num { color: #d97706; }
    .stat-card.amber .sc-bar { background: linear-gradient(90deg,#f59e0b,#fcd34d); }
    .stat-card.violet .sc-num { color: #7c3aed; }
    .stat-card.violet .sc-bar { background: linear-gradient(90deg,#8b5cf6,#c4b5fd); }
    .stat-card.sky .sc-num { color: #0284c7; }
    .stat-card.sky .sc-bar { background: linear-gradient(90deg,#0ea5e9,#7dd3fc); }
    .stat-card.green .sc-num { color: #059669; }
    .stat-card.green .sc-bar { background: linear-gradient(90deg,#10b981,#6ee7b7); }
    .stat-card.red .sc-num { color: var(--red-500,#c8102e); }
    .stat-card.red .sc-bar { background: linear-gradient(90deg,#e01836,#fca5b0); }

    /* ---- Section headers ---- */
    .section-hdr {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .section-hdr h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--ink,#0d1b35);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-hdr h3::after {
        content: '';
        display: inline-block;
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--red-500,#c8102e);
    }

    /* ---- Paginator override ---- */
    .pagination { display: flex; gap: 4px; margin-top: 14px; flex-wrap: wrap; }
    .pagination .page-item .page-link,
    nav[role="navigation"] a,
    nav[role="navigation"] span {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--line,#e2e8f4);
        background: #fff;
        color: var(--ink,#0d1b35);
        text-decoration: none;
        transition: background .15s, color .15s;
    }
    nav[role="navigation"] a:hover { background: var(--navy-100,#dce9fc); color: var(--navy-700,#112b5c); }
    nav[role="navigation"] span[aria-current] { background: var(--red-500,#c8102e); color: #fff; border-color: transparent; }

    /* ---- Quick actions ---- */
    .quick-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .qa-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 20px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: transform .15s, box-shadow .15s;
        border: 0;
        cursor: pointer;
        font-family: inherit;
    }
    .qa-btn:hover { transform: translateY(-1px); }
    .qa-btn.primary {
        background: linear-gradient(135deg,#e01836,#8b0c1e);
        color: #fff;
        box-shadow: 0 4px 14px rgba(200,16,46,.30);
    }
    .qa-btn.secondary {
        background: var(--surface-2,#f6f9ff);
        color: var(--ink-2,#2c3e65);
        border: 1.5px solid var(--line,#e2e8f4);
    }
    .qa-btn.secondary:hover { background: var(--navy-100,#dce9fc); border-color: var(--navy-300,#6a9ae8); }
</style>

{{-- ===== Hero Banner ===== --}}
<div class="dash-hero">
    <div class="dash-hero-text">
        <h2>Selamat datang, {{ auth()->user()->name }}! 👋</h2>
        <p>Pantau progres pekerjaan lapangan Indo Motor Lestari hari ini.</p>
        <div class="dash-hero-badge">
            <div class="live-dot"></div>
            Data diperbarui secara real-time
        </div>
    </div>
</div>

{{-- ===== Stats ===== --}}
<div class="stat-grid">
    <div class="stat-card amber">
        <span class="sc-icon">⏳</span>
        <div class="sc-num">{{ $statusCounts['waiting_acceptance'] ?? 0 }}</div>
        <div class="sc-lbl">Menunggu<br>Konfirmasi</div>
        <div class="sc-bar"></div>
    </div>
    <div class="stat-card violet">
        <span class="sc-icon">🚗</span>
        <div class="sc-num">{{ $statusCounts['on_the_way'] ?? 0 }}</div>
        <div class="sc-lbl">Dalam<br>Perjalanan</div>
        <div class="sc-bar"></div>
    </div>
    <div class="stat-card sky">
        <span class="sc-icon">🛠️</span>
        <div class="sc-num">{{ ($statusCounts['installation'] ?? 0) + ($statusCounts['arrived'] ?? 0) }}</div>
        <div class="sc-lbl">Proses<br>Pemasangan</div>
        <div class="sc-bar"></div>
    </div>
    <div class="stat-card green">
        <span class="sc-icon">✅</span>
        <div class="sc-num">{{ $statusCounts['finished'] ?? 0 }}</div>
        <div class="sc-lbl">Selesai<br>Hari Ini</div>
        <div class="sc-bar"></div>
    </div>
    <div class="stat-card red">
        <span class="sc-icon">👷</span>
        <div class="sc-num">{{ $technicianCount }}</div>
        <div class="sc-lbl">Total<br>Teknisi</div>
        <div class="sc-bar"></div>
    </div>
</div>

{{-- ===== Quick Actions ===== --}}
<div class="quick-actions">
    <a href="{{ route('dashboard.input') }}" class="qa-btn primary">➕ Input SPK Baru</a>
    <a href="{{ route('dashboard.work-orders') }}" class="qa-btn secondary">📋 Semua Work Order</a>
    <a href="{{ route('dashboard.technicians') }}" class="qa-btn secondary">👷 Data Teknisi</a>
</div>

{{-- ===== Pending WO ===== --}}
<div class="card">
    <div class="section-hdr">
        <h3>⏳ Menunggu Konfirmasi Teknisi</h3>
        <a class="btn btn-sm" href="{{ route('dashboard.work-orders') }}">Lihat semua →</a>
    </div>
    @if ($pending->isEmpty())
        <div class="empty">
            <div style="font-size:40px;margin-bottom:10px;">🎉</div>
            Tidak ada work order yang menunggu konfirmasi saat ini.
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nomor SPK</th>
                    <th>Customer</th>
                    <th>Tanggal Pasang</th>
                    <th>Teknisi</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($pending as $workOrder)
                    <tr>
                        <td><strong style="color:var(--navy-700,#112b5c);">{{ $workOrder->number }}</strong></td>
                        <td>{{ $workOrder->customer?->name ?? '-' }}</td>
                        <td>{{ $workOrder->scheduled_start_at?->format('d M Y, H:i') ?? '-' }}</td>
                        <td>{{ $workOrder->assignments->pluck('technician.user.name')->filter()->implode(', ') ?: '-' }}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('dashboard.work-orders.show', $workOrder) }}" class="btn btn-sm btn-navy">Detail →</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ===== Technicians ===== --}}
<div class="card">
    <div class="section-hdr">
        <h3>👷 Tim Teknisi</h3>
        <a class="btn btn-sm" href="{{ route('dashboard.technicians') }}">Lihat semua ({{ $technicianCount }}) →</a>
    </div>
    @if ($technicians->isEmpty())
        <div class="empty">
            <div style="font-size:40px;margin-bottom:10px;">👥</div>
            Belum ada data teknisi terdaftar.
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nama Teknisi</th>
                    <th>ID Karyawan</th>
                    <th>No. HP</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($technicians as $technician)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--navy-700,#112b5c),var(--navy-500,#2451a0));color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;">
                                    {{ mb_strtoupper(mb_substr($technician->full_name ?? 'T', 0, 1)) }}
                                </div>
                                <strong>{{ $technician->full_name ?? '-' }}</strong>
                            </div>
                        </td>
                        <td><span class="badge b-navy">{{ $technician->user_id ?? '-' }}</span></td>
                        <td>{{ $technician->cell_phone ?: $technician->home_phone ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        window.onFsmWorkOrderChanged = async function () {
            try {
                const res = await fetch('/dashboard/api/overview', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const nums = document.querySelectorAll('.stat-card .sc-num');
                if (nums[0]) nums[0].textContent = data.waiting_acceptance ?? 0;
                if (nums[1]) nums[1].textContent = data.on_the_way ?? 0;
                if (nums[2]) nums[2].textContent = data.installation ?? 0;
                if (nums[3]) nums[3].textContent = data.finished ?? 0;
            } catch (err) { /* biarkan angka lama; toast realtime tetap muncul */ }
        };
    </script>
@endpush
@endsection
