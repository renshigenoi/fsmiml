@extends('layouts.app')

@section('title', 'Work Orders')

@section('content')
    <div class="card">
        <div class="card-title-row">
            <h2>Daftar Work Order</h2>
            <a class="btn btn-sm" href="{{ route('dashboard.input') }}">➕ Input SPK Baru</a>
        </div>

        <form method="GET" action="{{ route('dashboard.work-orders') }}" class="filter-bar">
            <label for="status" style="margin:0;">Filter status:</label>
            <select name="status" id="status" onchange="this.form.submit()">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($selectedStatus ?? null) === $status->value)>
                        {{ \App\Support\StatusMap::label($status->value) }}
                    </option>
                @endforeach
                <option value="all" @selected($selectedStatus === null)>Semua Status</option>
            </select>
            <span class="muted">{{ $workOrders->total() }} work order</span>
        </form>

        @if ($workOrders->isEmpty())
            <p class="empty">Belum ada work order dengan filter ini.</p>
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
                        <tr>
                            <td><strong>{{ $workOrder->number }}</strong></td>
                            <td>{{ $workOrder->customer?->name }}</td>
                            <td>{{ $workOrder->scheduled_start_at?->format('d M Y H:i') }}</td>
                            <td>
                                <span class="badge b-{{ \App\Support\StatusMap::color($workOrder->status->value) }}">
                                    {{ \App\Support\StatusMap::label($workOrder->status->value) }}
                                </span>
                            </td>
                            <td>
                                {{ $workOrder->assignments->pluck('technician.user.name')->filter()->implode(', ') ?: '-' }}
                            </td>
                            <td><a href="{{ route('dashboard.work-orders.show', $workOrder) }}">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $workOrders->links() }}
        @endif
    </div>
@endsection
