@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<style>
    .attendance-admin-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; margin-bottom:18px; }
    .attendance-admin-grid .card { padding:20px; }
    .attendance-admin-grid h3, .attendance-section h3 { margin:0 0 14px; color:var(--ink); font-size:16px; }
    .attendance-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
    .attendance-form-grid .full { grid-column:1/-1; }
    .attendance-form-grid input, .attendance-form-grid select { width:100%; margin:0; }
    .attendance-section { padding:20px; margin-bottom:18px; }
    .attendance-section .table-wrap { border:1px solid var(--line); }
    .small-form { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .small-form select,.small-form input { margin:0; padding:7px 8px; font-size:12px; }
    .status-pending { color:#92400e; background:#fef3c7; }.status-approved { color:#065f46; background:#d1fae5; }.status-rejected { color:#9f1239; background:#ffe4e6; }
    @media(max-width:850px){ .attendance-admin-grid{grid-template-columns:1fr}.attendance-form-grid{grid-template-columns:1fr}.attendance-form-grid .full{grid-column:auto} }
</style>

<div class="dash-hero">
    <div class="dash-hero-text"><h2>🕘 Manajemen Absensi</h2><p>Kelola lokasi kerja, aturan karyawan, pengajuan cuti/izin, dan kehadiran hari ini.</p></div>
</div>

<div class="attendance-admin-grid">
    <section class="card">
        <h3>Tambah lokasi kerja</h3>
        <form method="POST" action="{{ route('dashboard.attendance.locations.store') }}" class="attendance-form-grid">@csrf
            <input class="full" name="name" placeholder="Nama lokasi, misalnya Kantor Pusat" required>
            <input class="full" name="address" placeholder="Alamat (opsional)">
            <input name="latitude" type="number" step="0.0000001" placeholder="Latitude" required>
            <input name="longitude" type="number" step="0.0000001" placeholder="Longitude" required>
            <input name="radius_meters" type="number" min="10" value="150" placeholder="Radius (meter)" required>
            <button class="btn" type="submit">Tambah lokasi</button>
        </form>
    </section>
    <section class="card">
        <h3>Master lokasi</h3>
        @forelse($locations as $location)
            <form method="POST" action="{{ route('dashboard.attendance.locations.update', $location) }}" class="small-form" style="padding:9px 0;border-bottom:1px solid var(--line)">@csrf
                <input name="name" value="{{ $location->name }}" required>
                <input name="address" value="{{ $location->address }}" placeholder="Alamat">
                <input name="latitude" type="number" step="0.0000001" value="{{ $location->latitude }}" required>
                <input name="longitude" type="number" step="0.0000001" value="{{ $location->longitude }}" required>
                <input name="radius_meters" type="number" min="10" value="{{ $location->radius_meters }}" style="width:90px" required>
                <select name="is_active"><option value="1" @selected($location->is_active)>Aktif</option><option value="0" @selected(!$location->is_active)>Nonaktif</option></select>
                <button class="btn btn-sm" type="submit">Simpan</button>
            </form>
        @empty <div class="empty">Belum ada lokasi kerja.</div> @endforelse
    </section>
</div>

<section class="card attendance-section">
    <h3>Aturan absensi per karyawan</h3>
    <div class="table-wrap"><table><thead><tr><th>Karyawan</th><th>Lokasi kerja</th><th>Mode absensi</th><th>Radius khusus</th><th></th></tr></thead><tbody>
    @forelse($technicians as $technician)
        <tr><form method="POST" action="{{ route('dashboard.attendance.technicians.update', $technician) }}">@csrf
            <td><strong>{{ $technician->user?->name ?? $technician->employee_code }}</strong><br><small class="muted">{{ $technician->employee_code }}</small></td>
            <td><select name="work_location_id"><option value="">Tidak ditetapkan</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($technician->work_location_id === $location->id)>{{ $location->name }}</option>@endforeach</select></td>
            <td><select name="attendance_mode"><option value="anywhere" @selected($technician->attendance_mode === 'anywhere')>Bebas lokasi</option><option value="required_location" @selected($technician->attendance_mode === 'required_location')>Wajib di lokasi</option><option value="allowed_outside" @selected($technician->attendance_mode === 'allowed_outside')>Luar lokasi diizinkan</option></select></td>
            <td><input name="attendance_radius_override" type="number" min="10" placeholder="Ikuti lokasi" value="{{ $technician->attendance_radius_override }}"></td>
            <td><button class="btn btn-sm" type="submit">Simpan</button></td>
        </form></tr>
    @empty <tr><td colspan="5" class="empty">Belum ada karyawan yang dapat diatur.</td></tr> @endforelse
    </tbody></table></div>
</section>

<section class="card attendance-section">
    <h3>Pengajuan cuti / izin menunggu persetujuan</h3>
    <div class="table-wrap"><table><thead><tr><th>Karyawan</th><th>Jenis & tanggal</th><th>Catatan</th><th>Keputusan</th></tr></thead><tbody>
    @forelse($leaveRequests as $leave)<tr><td>{{ $leave->user?->name ?? '-' }}</td><td><span class="badge status-pending">{{ $leave->type === 'leave' ? 'Cuti' : 'Izin' }}</span><br>{{ $leave->leave_date->format('d M Y') }}{{ $leave->leave_end_date && !$leave->leave_end_date->isSameDay($leave->leave_date) ? ' — '.$leave->leave_end_date->format('d M Y') : '' }}{{ $leave->start_time ? ' · '.$leave->start_time.'–'.$leave->end_time : '' }}</td><td>{{ $leave->note ?: '-' }}</td><td><form class="small-form" method="POST" action="{{ route('dashboard.attendance.leaves.review', $leave) }}">@csrf<input name="review_note" placeholder="Catatan (opsional)"><button class="btn btn-sm" name="status" value="approved">Setujui</button><button class="btn btn-danger btn-sm" name="status" value="rejected">Tolak</button></form></td></tr>
    @empty <tr><td colspan="4" class="empty">Tidak ada pengajuan yang menunggu.</td></tr> @endforelse
    </tbody></table></div>
</section>

<section class="card attendance-section">
    <h3>Absensi hari ini</h3>
    <div class="table-wrap"><table><thead><tr><th>Karyawan</th><th>Datang</th><th>Pulang</th><th>Status lokasi</th></tr></thead><tbody>
    @forelse($records as $record)<tr><td>{{ $record->user?->name ?? '-' }}</td><td>{{ $record->check_in_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-' }}</td><td>{{ $record->check_out_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-' }}</td><td>{{ $record->check_in_location_status === 'valid' ? 'Valid' : ($record->check_in_location_status === 'outside_allowed' ? 'Luar lokasi diizinkan' : '-') }}</td></tr>
    @empty <tr><td colspan="4" class="empty">Belum ada absensi hari ini.</td></tr> @endforelse
    </tbody></table></div>
</section>
@endsection
