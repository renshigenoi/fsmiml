<div class="card">
    <div class="card-title-row">
        <h2 style="font-size:17px;font-weight:900;letter-spacing:-.3px;">📋 Daftar Work Order</h2>
        <a class="btn btn-sm" href="{{ route('dashboard.input') }}">➕ Input SPK Baru</a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('dashboard.work-orders') }}" class="filter-panel" id="filter-form">
        <input type="search" name="q" value="{{ $search }}" placeholder="🔍 Cari SPK / customer..."
            onchange="this.form.submit()">
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="all" @selected($selectedStatus === 'all')>🔍 Semua Status</option>
            <option value="processing" @selected($selectedStatus === 'processing')>
                Proses Pemasangan (Tiba / Pasang)
            </option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($selectedStatus ?? null) === $status->value)>
                    {{ \App\Support\StatusMap::label($status->value) }}
                </option>
            @endforeach
        </select>
        <select name="range" id="range" onchange="this.form.submit()">
            <option value="3" @selected($selectedRange === 3)>📅 3 Hari Terakhir</option>
            <option value="7" @selected($selectedRange === 7)>7 Hari Terakhir</option>
            <option value="14" @selected($selectedRange === 14)>14 Hari Terakhir</option>
            <option value="all" @selected($selectedRange === null)>Semua Transaksi</option>
        </select>
        <select name="per_page" id="per_page" onchange="this.form.submit()">
            <option value="10" @selected($selectedPerPage === 10)>10 / halaman</option>
            <option value="25" @selected($selectedPerPage === 25)>25 / halaman</option>
            <option value="50" @selected($selectedPerPage === 50)>50 / halaman</option>
            <option value="all" @selected($selectedPerPage === 'all')>Semua</option>
        </select>
        <div class="filter-count" data-total="{{ $workOrders->total() }}">
            📊 {{ $workOrders->total() }} work order
        </div>
    </form>

    @if ($workOrders->isEmpty())
        <div class="empty">
            <div style="font-size:44px;margin-bottom:12px;">📭</div>
            Tidak ada work order dengan filter ini.
            <div style="margin-top:14px;">
                <a href="{{ route('dashboard.work-orders') }}" class="btn btn-sm">Reset Filter</a>
            </div>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th></th>
                    <th>Nomor SPK</th>
                    <th>Customer</th>
                    <th>Tanggal Pasang</th>
                    <th>Status</th>
                    <th>Teknisi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($workOrders as $workOrder)
                    <tr onclick="window.location='{{ route('dashboard.work-orders.show', $workOrder) }}'">
                        <td onclick="event.stopPropagation();" style="white-space:nowrap;">
                            <a href="{{ route('dashboard.work-orders.show', $workOrder) }}" class="btn btn-sm btn-navy">Detail →</a>
                            @if (isset($trackingLinks[$workOrder->getKey()]))
                                <a href="{{ $trackingLinks[$workOrder->getKey()] }}" target="_blank" rel="noopener"
                                    class="btn btn-sm" style="background:var(--red-500,#c8102e);color:#fff;">Tracking →</a>
                            @endif
                        </td>
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
