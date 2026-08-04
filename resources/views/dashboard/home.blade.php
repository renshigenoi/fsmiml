@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid">
        <div class="stat">
            <div class="stat-head"><span class="lbl">Menunggu Konfirmasi</span><span class="ico">⏳</span></div>
            <div class="num">{{ $statusCounts['waiting_acceptance'] ?? 0 }}</div>
            <div class="stat-bar"></div>
        </div>
        <div class="stat">
            <div class="stat-head"><span class="lbl">Dalam Perjalanan</span><span class="ico">🚗</span></div>
            <div class="num">{{ $statusCounts['on_the_way'] ?? 0 }}</div>
            <div class="stat-bar"></div>
        </div>
        <div class="stat">
            <div class="stat-head"><span class="lbl">Pemasangan</span><span class="ico">🛠️</span></div>
            <div class="num">{{ ($statusCounts['installation'] ?? 0) + ($statusCounts['arrived'] ?? 0) }}</div>
            <div class="stat-bar"></div>
        </div>
        <div class="stat">
            <div class="stat-head"><span class="lbl">Selesai Hari Ini</span><span class="ico">✅</span></div>
            <div class="num">{{ $statusCounts['finished'] ?? 0 }}</div>
            <div class="stat-bar"></div>
        </div>
        <div class="stat">
            <div class="stat-head"><span class="lbl">Total Teknisi</span><span class="ico">👷</span></div>
            <div class="num">{{ $technicianCount }}</div>
            <div class="stat-bar"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-title-row">
            <h2>⏳ Menunggu Konfirmasi</h2>
            <a class="btn btn-sm" href="{{ route('dashboard.work-orders') }}">Lihat semua →</a>
        </div>
        @if ($pending->isEmpty())
            <p class="empty">Tidak ada work order yang menunggu konfirmasi. 🎉</p>
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
                            <td><strong style="color:var(--navy-700)">{{ $workOrder->number }}</strong></td>
                            <td>{{ $workOrder->customer?->name ?? '-' }}</td>
                            <td>{{ $workOrder->scheduled_start_at?->format('d M Y H:i') }}</td>
                            <td>{{ $workOrder->assignments->pluck('technician.user.name')->filter()->implode(', ') ?: '-' }}</td>
                            <td><a href="{{ route('dashboard.work-orders.show', $workOrder) }}" class="btn btn-sm btn-navy">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title-row">
            <h2>👷 Tim Teknisi</h2>
            <a class="btn btn-sm" href="{{ route('dashboard.technicians') }}">Lihat semua ({{ $technicianCount }}) →</a>
        </div>
        @if ($technicians->isEmpty())
            <p class="empty">Belum ada data teknisi.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Nama Teknisi</th>
                        <th>ID</th>
                        <th>No. HP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($technicians as $technician)
                        <tr>
                            <td><strong>{{ $technician->full_name ?? '-' }}</strong></td>
                            <td><span class="badge b-navy">{{ $technician->user_id ?? '-' }}</span></td>
                            <td>{{ $technician->cell_phone ?: $technician->home_phone ?: '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
