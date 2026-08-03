@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $statusLabels = [
        'draft' => 'Draft',
        'waiting_acceptance' => 'Menunggu Konfirmasi',
        'accepted' => 'Diterima',
        'on_the_way' => 'Dalam Perjalanan',
        'arrived' => 'Tiba di Lokasi',
        'installation' => 'Pemasangan',
        'finished' => 'Selesai',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'failed' => 'Gagal',
    ];
    $statusColors = [
        'draft' => 'gray',
        'waiting_acceptance' => 'amber',
        'accepted' => 'blue',
        'on_the_way' => 'indigo',
        'arrived' => 'cyan',
        'installation' => 'violet',
        'finished' => 'green',
        'rejected' => 'rose',
        'cancelled' => 'gray',
        'failed' => 'red',
    ];
@endphp

@section('content')
    <div class="card">
        <h2>Input Pemasangan Baru</h2>

        <label for="spk-search">Cari Nomor SPK</label>
        <input type="text" id="spk-search" placeholder="Ketik nomor SPK / invoice (min. 3 karakter)..." autocomplete="off">
        <ul id="spk-results" class="suggest hidden"></ul>

        <div id="spk-selected" class="hidden">
            <h3 class="mt">Data SPK Terpilih</h3>
            <div class="meta-grid">
                <div><span class="muted">Nomor SPK</span><br><strong id="s-spk"></strong></div>
                <div><span class="muted">Customer</span><br><strong id="s-customer"></strong></div>
                <div><span class="muted">Alamat</span><br><span id="s-address"></span></div>
                <div><span class="muted">Kendaraan</span><br><span id="s-car"></span></div>
                <div><span class="muted">Tanggal Pemasangan (sumber)</span><br><span id="s-date"></span></div>
            </div>
        </div>

        <form id="wo-form" method="POST" action="{{ url('/dashboard/work-orders') }}">
            @csrf
            <input type="hidden" name="legacy_sales_serial" id="legacy-sales-serial">

            <h3 class="mt">Tim Teknisi</h3>
            <label for="tech-filter">Cari teknisi</label>
            <input type="text" id="tech-filter" placeholder="Filter nama / ID teknisi...">
            <div id="tech-list" class="tech-list"><span class="muted">Memuat daftar teknisi...</span></div>

            <div class="meta-grid mt">
                <div>
                    <label for="scheduled-start-at">Tanggal Pemasangan (bisa diubah)</label>
                    <input type="date" name="scheduled_start_at" id="scheduled-start-at">
                </div>
                <div>
                    <label for="notes">Catatan</label>
                    <textarea name="notes" id="notes" rows="1" placeholder="Opsional"></textarea>
                </div>
            </div>

            <button class="btn" type="submit">Simpan &amp; Assign Teknisi</button>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Work Order</h2>
        <form method="GET" action="{{ url('/dashboard') }}" class="filter-bar">
            <label for="status" style="margin:0;">Filter status:</label>
            <select name="status" id="status" onchange="this.form.submit()">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($selectedStatus ?? null) === $status->value)>
                        {{ $statusLabels[$status->value] ?? ucfirst($status->value) }}
                    </option>
                @endforeach
                <option value="all" @selected($selectedStatus === null)>Semua Status</option>
            </select>
            <span class="muted">{{ $workOrders->total() }} work order</span>
        </form>
        @if ($workOrders->isEmpty())
            <p class="muted">Belum ada work order. Mulai dengan mencari SPK di atas.</p>
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
                                <span class="badge b-{{ $statusColors[$workOrder->status->value] ?? 'gray' }}">
                                    {{ $statusLabels[$workOrder->status->value] ?? ucfirst($workOrder->status->value) }}
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

@push('scripts')
<script>
    const selectedSpk = {
        set(data) {
            document.getElementById('spk-selected').classList.remove('hidden');
            document.getElementById('s-spk').textContent = data.spk_no || '-';
            document.getElementById('s-customer').textContent = data.customer_name || '-';
            document.getElementById('s-address').textContent = [data.address, data.city, data.state].filter(Boolean).join(', ') || '-';
            document.getElementById('s-car').textContent = [data.car_brand, data.car_model].filter(Boolean).join(' ') || '-';
            document.getElementById('s-date').textContent = data.installation_date || '-';
            document.getElementById('legacy-sales-serial').value = data.serial || '';
            const dateInput = document.getElementById('scheduled-start-at');
            if (!dateInput.value && data.installation_date) {
                dateInput.value = data.installation_date.slice(0, 10);
            }
        },
        clear() {
            document.getElementById('spk-selected').classList.add('hidden');
            document.getElementById('legacy-sales-serial').value = '';
        },
    };

    const spkInput = document.getElementById('spk-search');
    const spkResults = document.getElementById('spk-results');
    let spkTimer;

    spkInput.addEventListener('input', () => {
        clearTimeout(spkTimer);
        spkTimer = setTimeout(searchSpk, 350);
    });

    async function searchSpk() {
        const q = spkInput.value.trim();
        if (q.length < 3) {
            spkResults.innerHTML = '';
            spkResults.classList.add('hidden');
            return;
        }

        const response = await fetch('/dashboard/api/sales?search=' + encodeURIComponent(q));
        const payload = await response.json();
        spkResults.innerHTML = '';

        if (payload.data.length === 0) {
            spkResults.innerHTML = '<li><small>SPK tidak ditemukan</small></li>';
        }

        payload.data.forEach((row) => {
            const li = document.createElement('li');
            li.innerHTML = '<strong>' + (row.spk_no || '') + '</strong>'
                + '<small>' + (row.customer_name || '') + ' — ' + (row.installation_date || '') + '</small>';
            li.addEventListener('click', () => {
                selectedSpk.set(row);
                spkResults.innerHTML = '';
                spkResults.classList.add('hidden');
                spkInput.value = row.spk_no || '';
            });
            spkResults.appendChild(li);
        });

        spkResults.classList.remove('hidden');
    }

    let technicians = [];

    async function loadTechnicians() {
        const response = await fetch('/dashboard/api/technicians');
        const payload = await response.json();
        technicians = payload.data;
        renderTechnicians('');
    }

    function renderTechnicians(filter) {
        const box = document.getElementById('tech-list');
        const needle = filter.toLowerCase();
        const rows = technicians.filter((t) =>
            !needle || (t.full_name || '').toLowerCase().includes(needle) || (t.user_id || '').toLowerCase().includes(needle)
        );

        if (rows.length === 0) {
            box.innerHTML = '<span class="muted">Tidak ada teknisi yang cocok</span>';
            return;
        }

        box.innerHTML = '';
        rows.forEach((t) => {
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'technician_legacy_serials[]';
            checkbox.value = t.serial;
            checkbox.className = 'tech-check';
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(t.full_name + (t.user_id ? ' (' + t.user_id + ')' : '')));
            box.appendChild(label);
        });
    }

    document.getElementById('tech-filter').addEventListener('input', (e) => renderTechnicians(e.target.value.trim()));

    document.getElementById('wo-form').addEventListener('submit', (e) => {
        const serial = document.getElementById('legacy-sales-serial').value;
        const checks = document.querySelectorAll('.tech-check:checked');
        if (!serial) {
            e.preventDefault();
            alert('Pilih SPK terlebih dahulu.');
            return;
        }
        if (checks.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 teknisi.');
        }
    });

    loadTechnicians();
</script>
@endpush
