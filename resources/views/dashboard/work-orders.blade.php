@extends('layouts.app')

@section('title', 'Work Orders')

@section('content')
<style>
    /* ---- Page header (Work Order) ---- */
    .wo-hero {
        background: linear-gradient(135deg, var(--navy-900, #061429), var(--navy-700, #112b5c));
        border-radius: 18px;
        padding: 22px 28px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(11, 32, 68, .22);
    }
    .wo-hero::before {
        content: '';
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200, 16, 46, .15) 0%, transparent 70%);
        top: -100px;
        right: 20px;
        pointer-events: none;
    }
    .wo-hero-text {
        position: relative;
        z-index: 1;
    }
    .wo-hero-text h2 {
        margin: 0 0 5px;
        font-size: 20px;
        font-weight: 900;
        letter-spacing: -.3px;
    }
    .wo-hero-text p {
        margin: 0;
        color: rgba(255, 255, 255, .60);
        font-size: 13.5px;
    }
    .wo-hero-stat {
        position: relative;
        z-index: 1;
        text-align: center;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 14px;
        padding: 14px 24px;
        flex-shrink: 0;
    }
    .wo-hero-stat .whs-num {
        font-size: 36px;
        font-weight: 900;
        letter-spacing: -2px;
        line-height: 1;
    }
    .wo-hero-stat .whs-lbl {
        font-size: 11px;
        color: rgba(255, 255, 255, .55);
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 700;
        margin-top: 4px;
    }

    /* Judul kartu digantikan banner halaman */
    .card-title-row h2 { display: none; }
    .card-title-row { justify-content: flex-end; }

    /* Filter bar enhanced */
    .filter-panel {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .filter-panel select {
        padding: 9px 14px;
        border: 1.5px solid var(--line,#e2e8f4);
        border-radius: 9px;
        font-size: 13.5px;
        font-family: inherit;
        background: var(--surface-2,#f6f9ff);
        color: var(--ink,#0d1b35);
        outline: none;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        margin: 0;
        width: auto;
    }
    .filter-panel select:focus { border-color: var(--red-500,#c8102e); box-shadow: 0 0 0 3px rgba(200,16,46,.10); }
    .filter-panel input[type="search"] {
        padding: 9px 14px;
        border: 1.5px solid var(--line,#e2e8f4);
        border-radius: 9px;
        font-size: 13.5px;
        font-family: inherit;
        background: var(--surface-2,#f6f9ff);
        color: var(--ink,#0d1b35);
        outline: none;
        margin: 0;
        width: 240px;
    }
    .filter-panel input[type="search"]:focus { border-color: var(--red-500,#c8102e); box-shadow: 0 0 0 3px rgba(200,16,46,.10); }
    .filter-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        background: var(--navy-100,#dce9fc);
        color: var(--navy-700,#112b5c);
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
    }

    /* Row hover highlight */
    tbody tr { cursor: pointer; }
    tbody tr:hover td { background: var(--surface-2,#f6f9ff); }

    /* Number col */
    .wo-number {
        font-weight: 800;
        font-size: 14px;
        color: var(--navy-700,#112b5c);
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        letter-spacing: -.3px;
    }

    /* Customer cell */
    .cust-cell { display: flex; flex-direction: column; gap: 1px; }
    .cust-name { font-weight: 600; font-size: 14px; color: var(--ink,#0d1b35); }
    .cust-phone { font-size: 12px; color: var(--muted,#64748b); }

    /* Date cell */
    .date-cell { font-size: 13.5px; color: var(--ink-2,#2c3e65); }

    /* Technician chip */
    .tech-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--navy-100,#dce9fc);
        border-radius: 999px;
        padding: 3px 10px 3px 5px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--navy-700,#112b5c);
        white-space: nowrap;
    }
    .tech-chip-avatar {
        width: 22px; height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg,var(--navy-700,#112b5c),var(--navy-500,#2451a0));
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* Pagination */
    .pagination-wrap { margin-top: 16px; }
    .pagination-wrap nav { display: flex; gap: 4px; flex-wrap: wrap; }
    .pagination-wrap nav a,
    .pagination-wrap nav span {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--line,#e2e8f4);
        background: #fff;
        color: var(--ink,#0d1b35);
        text-decoration: none;
        transition: background .15s, color .15s;
    }
    .pagination-wrap nav a:hover { background: var(--navy-100,#dce9fc); color: var(--navy-700,#112b5c); }
    .pagination-wrap nav span[aria-current="page"] span {
        background: var(--red-500,#c8102e);
        color: #fff;
        border-color: transparent;
        border-radius: 6px;
        padding: 4px 10px;
    }
</style>

{{-- ===== Hero Banner ===== --}}
<div class="wo-hero">
    <div class="wo-hero-text">
        <h2>📋 Work Order</h2>
        <p>Pantau dan kelola seluruh pekerjaan pemasangan lapangan.</p>
    </div>
    <div class="wo-hero-stat">
        <div class="whs-num" id="wo-hero-count">{{ $workOrders->total() }}</div>
        <div class="whs-lbl">Total Work Order</div>
    </div>
</div>

<div id="wo-list">
    @include('dashboard.work-orders-table')
</div>

@push('scripts')
<script>
    (function () {
        const list = document.getElementById('wo-list');
        if (!list) return;

        let busy = false;

        function load(url) {
            if (busy) return;
            busy = true;
            const u = new URL(url, window.location.href);
            u.searchParams.set('partial', '1');
            list.style.opacity = '.55';
            list.style.pointerEvents = 'none';
            fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.text();
                })
                .then(html => {
                    list.innerHTML = html;
                    list.style.opacity = '1';
                    list.style.pointerEvents = '';
                    const countEl = list.querySelector('.filter-count');
                    const heroCount = document.getElementById('wo-hero-count');
                    if (countEl && heroCount) heroCount.textContent = countEl.dataset.total || '0';
                    history.replaceState(null, '', url);
                    const top = list.getBoundingClientRect().top + window.scrollY - 70;
                    window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
                })
                .catch(() => {
                    list.style.opacity = '1';
                    list.style.pointerEvents = '';
                })
                .finally(() => { busy = false; });
        }

        list.addEventListener('submit', function (e) {
            const form = e.target.closest('#filter-form');
            if (!form) return;
            e.preventDefault();
            load(form.action + '?' + new URLSearchParams(new FormData(form)).toString());
        });

        list.addEventListener('click', function (e) {
            const a = e.target.closest('a');
            if (!a || a.target === '_blank') return;
            const href = a.getAttribute('href') || '';
            if (href.includes('page=')) {
                e.preventDefault();
                load(href);
            }
        });
    })();
</script>
@endpush
@endsection
