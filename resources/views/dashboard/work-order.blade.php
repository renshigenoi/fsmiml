@extends('layouts.app')

@section('title', 'Work Order '.$workOrder->number)

@section('content')
    <p><a href="{{ route('dashboard.work-orders') }}">&larr; Kembali ke daftar work order</a></p>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2 style="margin:0;">Work Order {{ $workOrder->number }}</h2>
            <span class="badge b-{{ \App\Support\StatusMap::color($workOrder->status->value) }}">
                {{ \App\Support\StatusMap::label($workOrder->status->value) }}
            </span>
        </div>

        <div class="meta-grid mt">
            <div><span class="muted">Customer</span><br><strong>{{ $workOrder->customer?->name }}</strong></div>
            <div><span class="muted">Telepon</span><br>{{ $workOrder->customer?->phone ?? '-' }}</div>
            <div><span class="muted">Alamat Pemasangan</span><br>{{ $workOrder->serviceLocation?->address ?? '-' }}</div>
            <div><span class="muted">Tanggal Pasang</span><br>{{ $workOrder->scheduled_start_at?->format('d M Y H:i') ?? '-' }}</div>
            <div><span class="muted">Catatan</span><br>{{ $workOrder->notes ?? '-' }}</div>
        </div>
    </div>

    @if ($workOrder->serviceLocation)
        <div class="card">
            <h2>Lokasi Pemasangan</h2>
            <div class="meta-grid">
                <div><span class="muted">Alamat</span><br>{{ $workOrder->serviceLocation->address }}</div>
                @if ($workOrder->serviceLocation->city)
                    <div><span class="muted">Kota</span><br>{{ $workOrder->serviceLocation->city }}</div>
                @endif
                @if ($workOrder->serviceLocation->latitude)
                    <div>
                        <span class="muted">Koordinat</span><br>
                        {{ $workOrder->serviceLocation->latitude }}, {{ $workOrder->serviceLocation->longitude }}
                    </div>
                    <div>
                        <span class="muted">Buka di Peta</span><br>
                        <a href="https://www.google.com/maps?q={{ $workOrder->serviceLocation->latitude }},{{ $workOrder->serviceLocation->longitude }}" target="_blank" rel="noopener">Google Maps &nearr;</a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <h2>Tim Teknisi &amp; Assignment</h2>
        @if ($workOrder->assignments->isEmpty())
            <p class="muted">Belum ada teknisi yang ditugaskan.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Teknisi</th>
                        <th>Status Assignment</th>
                        <th>Ditugaskan</th>
                        <th>Respons</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($workOrder->assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->technician?->user?->name ?? '-' }}</td>
                            <td><span class="badge b-{{ $assignment->status->value === 'accepted' ? 'green' : ($assignment->status->value === 'superseded' ? 'gray' : 'amber') }}">{{ ucfirst($assignment->status->value) }}</span></td>
                            <td>{{ $assignment->assigned_at?->format('d M Y H:i') }}</td>
                            <td>{{ $assignment->responded_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($workOrder->items->isNotEmpty())
        <div class="card">
            <h2>Item Pekerjaan</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr><th>Produk</th><th>Qty</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($workOrder->items as $item)
                        <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <h2>Riwayat Status</h2>
        <ul style="line-height:1.9;font-size:14px;">
            @foreach ($workOrder->statusHistories as $history)
                <li>
                    <strong>{{ $history->to_status->value }}</strong>
                    @if ($history->from_status !== null)
                        (dari {{ $history->from_status->value }})
                    @endif
                    — {{ $history->occurred_at?->format('d M Y H:i') }}
                    @if ($history->reason)
                        <span class="muted">— {{ $history->reason }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endsection
