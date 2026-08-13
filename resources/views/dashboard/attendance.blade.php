@extends('layouts.app')

@section('title', 'Manajemen Absensi')

@section('content')
<!-- DataTables CSS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
    /* Hero & Layout Base */
    .att-hero { 
        background: linear-gradient(135deg, var(--navy-900, #061429), var(--navy-700, #112b5c)); 
        border-radius: 18px; 
        padding: 22px 26px; 
        color: #fff; 
        margin-bottom: 24px; 
        box-shadow: 0 8px 32px rgba(11, 32, 68, .20); 
        position: relative; 
        overflow: hidden; 
    }
    .att-hero:before { 
        content: ''; 
        position: absolute; 
        width: 240px; 
        height: 240px; 
        border-radius: 50%; 
        background: radial-gradient(circle, rgba(200, 16, 46, .16), transparent 70%); 
        right: 0; 
        top: -80px; 
    }
    .att-hero h2, .att-hero p { position: relative; z-index: 1; }
    .att-hero h2 { margin: 0 0 5px; font-size: 20px; font-weight: 900; }
    .att-hero p { margin: 0; color: rgba(255, 255, 255, .6); font-size: 13.5px; }

    .att-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    .att-full { grid-column: 1 / -1; }
    
    .sec-card { 
        background: #fff; 
        border: 1px solid var(--line, #e2e8f4); 
        border-radius: 16px; 
        overflow: hidden; 
        box-shadow: 0 1px 4px rgba(11, 32, 68, .06); 
    }
    .sec-card-head { 
        display: flex; 
        gap: 12px; 
        align-items: center; 
        padding: 15px 20px; 
        background: var(--surface-2, #f6f9ff); 
        border-bottom: 1px solid var(--line, #e2e8f4); 
    }
    .sec-ico { 
        width: 36px; height: 36px; 
        border-radius: 9px; 
        background: linear-gradient(135deg, var(--navy-700,#112b5c), var(--navy-600,#1a3a7a)); 
        display: grid; place-items: center; 
        box-shadow: 0 3px 8px rgba(11, 32, 68, .18); 
    }
    .sec-card-head h3 { margin: 0; font-size: 14.5px; color: var(--ink, #0d1b35); font-weight: 800; }
    .sec-card-head p { margin: 2px 0 0; font-size: 12px; color: var(--muted, #64748b); }
    .sec-card-body { padding: 20px; }

    /* Custom Form Elements */
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--muted, #64748b);
    }
    .form-control-custom {
        width: 100%;
        border: 1.5px solid var(--line, #e2e8f4);
        border-radius: 9px;
        background: var(--surface-2, #f6f9ff);
        color: var(--ink, #0d1b35);
        padding: 10px 12px;
        font-size: 13.5px;
        font-family: inherit;
        box-sizing: border-box;
        transition: border-color .15s, background-color .15s;
    }
    .form-control-custom:focus { outline: none; border-color: var(--navy-700, #112b5c); background: #fff; }
    textarea.form-control-custom { resize: vertical; min-height: 68px; }

    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .field-grid .wide { grid-column: 1 / -1; }
    .form-action { display: flex; justify-content: flex-end; margin-top: 18px; }

    /* Master Location Style */
    .location-list { display: grid; gap: 14px; }
    .location-item { 
        padding: 16px; 
        border: 1px solid var(--line, #e2e8f4); 
        border-radius: 12px; 
        background: var(--surface-2, #f6f9ff); 
    }
    .location-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .location-title-box { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .location-item strong { color: var(--navy-700, #112b5c); font-size: 15px; font-weight: 800; }
    
    .map-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: var(--navy-100, #dce9fc);
        color: var(--navy-700, #112b5c);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: background .15s;
    }
    .map-link-btn:hover { background: var(--navy-300, #6a9ae8); color: #fff; }

    .location-edit-grid { display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 8px; }
    .location-edit-row { display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 8px; }
    .location-edit-row input, .location-edit-row select, .location-edit-grid textarea { 
        padding: 8px 10px; 
        margin: 0; 
        font-size: 12px; 
        border: 1px solid var(--line, #e2e8f4); 
        border-radius: 8px;
        background: #fff;
        font-family: inherit;
    }

    /* Badges */
    .review-box { display: grid; gap: 8px; }
    .table-card .sec-card-body { padding: 12px; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-valid { background: #d1fae5; color: #065f46; }
    .status-outside { background: #dbeafe; color: #1e40af; }

    /* =========================================================
       CUSTOM DATATABLE & PAGINATION STYLING
    ========================================================= */
    table.dataTable { border-collapse: collapse !important; width: 100% !important; }
    table.dataTable thead th {
        background: var(--surface-2, #f6f9ff);
        color: var(--navy-700, #112b5c);
        font-size: 12.5px;
        font-weight: 800;
        padding: 12px;
        border-bottom: 2px solid var(--line, #e2e8f4) !important;
    }
    table.dataTable tbody td {
        padding: 10px 12px;
        font-size: 13px;
        vertical-align: middle;
        border-bottom: 1px solid var(--line, #e2e8f4) !important;
    }
    
    .dataTables_wrapper { font-family: inherit; padding: 8px 0; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 8px 12px;
        margin-bottom: 8px;
        font-size: 12.5px;
        color: var(--muted, #64748b);
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1.5px solid var(--line, #e2e8f4);
        border-radius: 8px;
        padding: 6px 12px;
        outline: none;
        background: var(--surface-2, #f6f9ff);
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--navy-700, #112b5c);
        background: #fff;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1.5px solid var(--line, #e2e8f4);
        border-radius: 6px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 12px 16px !important;
        font-size: 12px;
        color: var(--muted, #64748b) !important;
    }
    .dataTables_wrapper .dataTables_paginate { padding: 10px 16px !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        border: 1px solid var(--line, #e2e8f4) !important;
        background: #fff !important;
        color: var(--navy-700, #112b5c) !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        margin-left: 4px !important;
        transition: all .15s !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--surface-2, #f6f9ff) !important;
        color: var(--navy-900, #061429) !important;
        border-color: var(--navy-700, #112b5c) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--navy-700, #112b5c) !important;
        color: #fff !important;
        border-color: var(--navy-700, #112b5c) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        opacity: 0.5;
        background: #f1f5f9 !important;
        color: var(--muted, #64748b) !important;
        cursor: not-allowed !important;
    }

    @media(max-width: 960px) { .att-layout { grid-template-columns: 1fr; } .att-full { grid-column: auto; } }
    @media(max-width: 650px) { 
        .field-grid, .location-edit-row { grid-template-columns: 1fr; } 
        .field-grid .wide { grid-column: auto; } 
        .location-edit-row { gap: 7px; } 
        .sec-card-body { padding: 14px; } 
    }
</style>

<div class="att-hero">
    <h2>🕘 Manajemen Absensi</h2>
    <p>Atur lokasi kerja, kebijakan kehadiran, serta tinjau pengajuan dan absensi karyawan.</p>
</div>

<div class="att-layout">

    {{-- TAMBAH LOKASI KERJA --}}
    <section class="sec-card">
        <div class="sec-card-head">
            <div class="sec-ico">📍</div>
            <div>
                <h3>Tambah Lokasi Kerja</h3>
                <p>Tentukan titik GPS dan radius validasi absensi.</p>
            </div>
        </div>
        <div class="sec-card-body">
            <form method="POST" action="{{ route('dashboard.attendance.locations.store') }}">
                @csrf
                <div class="field-grid">
                    <div class="wide form-group">
                        <label>Nama Lokasi</label>
                        <input type="text" name="name" class="form-control-custom" placeholder="Contoh: Kantor Pusat" required>
                    </div>

                    <div class="wide form-group">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control-custom" rows="2" placeholder="Masukkan alamat lengkap lokasi (opsional)..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Latitude</label>
                        <input name="latitude" type="number" step="0.0000001" class="form-control-custom" placeholder="-6.175392" required>
                    </div>
                    <div class="form-group">
                        <label>Longitude</label>
                        <input name="longitude" type="number" step="0.0000001" class="form-control-custom" placeholder="106.827153" required>
                    </div>
                    <div class="wide form-group">
                        <label>Radius (meter)</label>
                        <input name="radius_meters" type="number" min="10" value="150" class="form-control-custom" required>
                    </div>
                </div>
                <div class="form-action">
                    <button class="btn" type="submit">📍 Tambah Lokasi</button>
                </div>
            </form>
        </div>
    </section>

    {{-- MASTER LOKASI --}}
    <section class="sec-card">
        <div class="sec-card-head">
            <div class="sec-ico">🗺️</div>
            <div>
                <h3>Master Lokasi</h3>
                <p>Ubah data, radius, atau status lokasi yang sudah ada.</p>
            </div>
        </div>
        <div class="sec-card-body">
            <div class="location-list">
                @forelse($locations as $location)
                    <div class="location-item">
                        <div class="location-header">
                            <div class="location-title-box">
                                <strong>{{ $location->name }}</strong>
                                @if($location->latitude && $location->longitude)
                                    <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" 
                                       target="_blank" 
                                       rel="noopener" 
                                       class="map-link-btn">
                                        🗺️ Location ↗
                                    </a>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('dashboard.attendance.locations.update', $location) }}">
                            @csrf
                            <input type="hidden" name="name" value="{{ $location->name }}">
                            
                            <div class="location-edit-grid">
                                <textarea name="address" rows="2" placeholder="Alamat lokasi..." title="Alamat">{{ $location->address }}</textarea>
                                
                                <div class="location-edit-row">
                                    <input name="latitude" type="number" step="0.0000001" value="{{ $location->latitude }}" required title="Latitude" placeholder="Lat">
                                    <input name="longitude" type="number" step="0.0000001" value="{{ $location->longitude }}" required title="Longitude" placeholder="Long">
                                    <input name="radius_meters" type="number" min="10" value="{{ $location->radius_meters }}" required title="Radius (m)" placeholder="Radius">
                                    <select name="is_active">
                                        <option value="1" @selected($location->is_active)>Aktif</option>
                                        <option value="0" @selected(!$location->is_active)>Nonaktif</option>
                                    </select>
                                    <button class="btn btn-sm" type="submit">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="empty">Belum ada lokasi kerja.</div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ATURAN ABSENSI PER KARYAWAN (FIXED DATATABLES HTML) --}}
    <section class="sec-card att-full table-card">
        <div class="sec-card-head">
            <div class="sec-ico">👷</div>
            <div>
                <h3>Aturan Absensi per Karyawan</h3>
                <p>Pilih lokasi kerja, mode validasi, dan radius khusus bila diperlukan.</p>
            </div>
        </div>
        <div class="sec-card-body">
            <table id="table-technicians" class="dataTable">
                <thead>
                    <tr>
                        <th style="width: 25%;">Karyawan</th>
                        <th style="width: 25%;">Lokasi Kerja</th>
                        <th style="width: 25%;">Mode Absensi</th>
                        <th style="width: 15%;">Radius Khusus</th>
                        <th style="width: 10%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($technicians as $technician)
                        <tr>
                            <td>
                                <strong>{{ $technician->user?->name ?? $technician->employee_code }}</strong><br>
                                <small class="muted">{{ $technician->employee_code }}</small>
                            </td>
                            
                            {{-- Form diletakkan di dalam TD tanpa menyatukan kolom dengan colspan --}}
                            <td>
                                <select form="form-tech-{{ $technician->id }}" name="work_location_id" class="form-control-custom" style="padding: 6px;">
                                    <option value="">Tidak ditetapkan</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" @selected($technician->work_location_id === $location->id)>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="form-tech-{{ $technician->id }}" name="attendance_mode" class="form-control-custom" style="padding: 6px;">
                                    <option value="anywhere" @selected($technician->attendance_mode === 'anywhere')>Bebas lokasi</option>
                                    <option value="required_location" @selected($technician->attendance_mode === 'required_location')>Wajib di lokasi</option>
                                    <option value="allowed_outside" @selected($technician->attendance_mode === 'allowed_outside')>Luar lokasi diizinkan</option>
                                </select>
                            </td>
                            <td>
                                <input form="form-tech-{{ $technician->id }}" name="attendance_radius_override" type="number" min="10" class="form-control-custom" style="padding: 6px;" value="{{ $technician->attendance_radius_override }}" placeholder="Ikuti lokasi">
                            </td>
                            <td style="text-align: center;">
                                {{-- Form ID dihubungkan dengan atribut form="" pada input di atas --}}
                                <form id="form-tech-{{ $technician->id }}" method="POST" action="{{ route('dashboard.attendance.technicians.update', $technician) }}">
                                    @csrf
                                    <button class="btn btn-sm" type="submit">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- PENGAJUAN CUTI / IZIN --}}
    <section class="sec-card table-card att-full">
        <div class="sec-card-head">
            <div class="sec-ico">📝</div>
            <div>
                <h3>Pengajuan Cuti / Izin</h3>
                <p>Pengajuan yang menunggu persetujuan admin.</p>
            </div>
        </div>
        <div class="sec-card-body">
            <table id="table-leaves" class="dataTable">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jenis & Waktu</th>
                        <th>Catatan</th>
                        <th>Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveRequests as $leave)
                        <tr>
                            <td><strong>{{ $leave->user?->name ?? '-' }}</strong></td>
                            <td>
                                <span class="badge status-pending">{{ $leave->type === 'leave' ? 'Cuti' : 'Izin' }}</span><br>
                                {{ $leave->leave_date->format('d M Y') }}{{ $leave->leave_end_date && !$leave->leave_end_date->isSameDay($leave->leave_date) ? ' — '.$leave->leave_end_date->format('d M Y') : '' }}{{ $leave->start_time ? ' · '.$leave->start_time.'–'.$leave->end_time : '' }}
                            </td>
                            <td>{{ $leave->note ?: '-' }}</td>
                            <td>
                                <form class="review-box" method="POST" action="{{ route('dashboard.attendance.leaves.review', $leave) }}">
                                    @csrf
                                    <input name="review_note" class="form-control-custom" style="padding: 6px 10px;" placeholder="Catatan keputusan (opsional)">
                                    <div>
                                        <button class="btn btn-sm" name="status" value="approved">Setujui</button>
                                        <button class="btn btn-danger btn-sm" name="status" value="rejected">Tolak</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ABSENSI HARI INI --}}
    <section class="sec-card table-card att-full">
        <div class="sec-card-head">
            <div class="sec-ico">📋</div>
            <div>
                <h3>Absensi Hari Ini</h3>
                <p>Rekap datang, pulang, dan hasil validasi lokasi.</p>
            </div>
        </div>
        <div class="sec-card-body">
            <table id="table-records" class="dataTable">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Datang</th>
                        <th>Pulang</th>
                        <th>Status Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        <tr>
                            <td><strong>{{ $record->user?->name ?? '-' }}</strong></td>
                            <td>{{ $record->check_in_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-' }}</td>
                            <td>{{ $record->check_out_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-' }}</td>
                            <td>
                                @if($record->check_in_location_status === 'valid')
                                    <span class="badge status-valid">Valid</span>
                                @elseif($record->check_in_location_status === 'outside_allowed')
                                    <span class="badge status-outside">Luar lokasi diizinkan</span>
                                @else 
                                    - 
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- DataTables JS & Inisialisasi Script -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const dtLanguage = {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Data kosong",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: {
                previous: "‹",
                next: "›"
            }
        };

        $('#table-technicians').DataTable({
            pageLength: 5,
            language: dtLanguage,
            ordering: false
        });

        $('#table-leaves').DataTable({
            pageLength: 5,
            language: dtLanguage,
            ordering: false
        });

        $('#table-records').DataTable({
            pageLength: 5,
            language: dtLanguage,
            ordering: false
        });
    });
</script>
@endsection