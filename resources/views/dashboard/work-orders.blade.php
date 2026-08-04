@extends('layouts.app')

@section('title', 'Work Orders')

@section('content')
<style>
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

<div class="card">
    <div class="card-title-row">
        <h2 style="font-size:17px;font-weight:900;letter-spacing:-.3px;">📋 Daftar Work Order</h2>
        <a class="btn btn-sm" href="{{ route('dashboard.input') }}">➕ Input SPK Baru</a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('dashboard.work-orders') }}" class="filter-panel" id="filter-form">
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="all" @selected($selectedStatus === null)>🔍 Semua Status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($selectedStatus ?? null) === $status->value)>
                    {{ \App\Support\StatusMap::label($status->value) }}
                </option>
            @endforeach
        </select>
        <div class="filter-count">
            📊 {{ $workOrders->total() }} work order
        </div>
    </form>

    @if ($workOrders->isEmpty())
        <div class="empty">
            <div style="font-size:44px;margin-bottom:12px;">📭</div>
            Tidak ada work order dengan filter ini.
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nomor SPK</th>
                    <th>Customer</th>
                    <th>Tanggal Pasang</th>
                    <th>Status</th>
                    <th>Teknisi</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($workOrders as $workOrder)
                    <tr onclick="window.location='{{ route('dashboard.work-orders.show', $workOrder) }}'">
                        <td>
                            <span class="wo-number">{{ $workOrder->number }}</span>
                        </td>
                        <td>
                            <div class="cust-cell">
                                <span class="cust-name">{{ $workOrder->customer?->name ?? '-' }}</span>
                                @if($workOrder->customer?->phone)
                                    <span class="cust-phone">📱 {{ $workOrder->customer->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="date-cell">
                            {{ $workOrder->scheduled_start_at?->format('d M Y') ?? '-' }}<br>
                            <span style="font-size:12px;color:var(--muted);">{{ $workOrder->scheduled_start_at?->format('H:i') ?? '' }}</span>
                        </td>
                        <td>
                            <span class="badge b-{{ \App\Support\StatusMap::color($workOrder->status->value) }}">
                                {{ \App\Support\StatusMap::label($workOrder->status->value) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $techs = $workOrder->assignments->map(fn($a) => $a->technician?->user?->name)->filter();
                            @endphp
                            @if ($techs->isEmpty())
                                <span class="muted" style="font-size:13px;">—</span>
                            @else
                                @foreach ($techs as $t)
                                    <span class="tech-chip">
                                        <span class="tech-chip-avatar">{{ mb_strtoupper(mb_substr($t, 0, 1)) }}</span>
                                        {{ $t }}
                                    </span>
                                @endforeach
                            @endif
                        </td>
                        <td onclick="event.stopPropagation();" style="white-space:nowrap;">
                            <a href="{{ route('dashboard.work-orders.show', $workOrder) }}" class="btn btn-sm btn-navy">Detail →</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $workOrders->links() }}
        </div>
    @endif
</div>
@endsection
