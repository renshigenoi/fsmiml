@extends('layouts.app')

@section('title', 'Teknisi')

@section('content')
    <div class="card">
        <div class="card-title-row">
            <h2>👷 Daftar Teknisi</h2>
            <span class="muted">{{ count($technicians) }} teknisi (sumber: database sales)</span>
        </div>

        <form method="GET" action="{{ route('dashboard.technicians') }}" class="search-bar">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / ID teknisi...">
            <button class="btn" type="submit">Cari</button>
        </form>

        @if (count($technicians) === 0)
            <p class="empty">Tidak ada teknisi yang cocok.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>ID</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($technicians as $technician)
                        <tr>
                            <td><strong>{{ $technician->full_name ?? '-' }}</strong></td>
                            <td>{{ $technician->user_id ?? '-' }}</td>
                            <td>{{ $technician->cell_phone ?: $technician->home_phone ?: '-' }}</td>
                            <td>{{ collect([$technician->address ?? null, $technician->city ?? null])->filter()->implode(', ') ?: '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
