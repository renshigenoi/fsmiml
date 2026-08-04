@extends('layouts.app')

@section('title', 'Master Data Teknisi')

@section('content')
    <style>
        /* ---- Page header ---- */
        .tech-hero {
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

        .tech-hero::before {
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

        .tech-hero-text {
            position: relative;
            z-index: 1;
        }

        .tech-hero-text h2 {
            margin: 0 0 5px;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -.3px;
        }

        .tech-hero-text p {
            margin: 0;
            color: rgba(255, 255, 255, .60);
            font-size: 13.5px;
        }

        .tech-hero-stat {
            position: relative;
            z-index: 1;
            text-align: center;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 14px;
            padding: 14px 24px;
            flex-shrink: 0;
        }

        .tech-hero-stat .ths-num {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: -2px;
            line-height: 1;
        }

        .tech-hero-stat .ths-lbl {
            font-size: 11px;
            color: rgba(255, 255, 255, .55);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            margin-top: 4px;
        }

        /* ---- Toolbar ---- */
        .tech-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .tech-toolbar .search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .tech-toolbar .search-ico {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            opacity: .4;
            pointer-events: none;
        }

        .tech-toolbar input[type="text"] {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--line, #e2e8f4);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            margin: 0;
            background: var(--surface-2, #f6f9ff);
            color: var(--ink, #0d1b35);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .tech-toolbar input:focus {
            border-color: var(--red-500, #c8102e);
            box-shadow: 0 0 0 3px rgba(200, 16, 46, .10);
            background: #fff;
        }

        /* ---- Grid layout ---- */
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        /* ---- Technician card ---- */
        .tech-card {
            background: #fff;
            border: 1px solid var(--line, #e2e8f4);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(11, 32, 68, .06);
            transition: transform .18s, box-shadow .18s;
        }

        .tech-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(11, 32, 68, .12);
        }

        .tech-card-top {
            padding: 20px 20px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .tech-card-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--navy-700, #112b5c), var(--navy-500, #2451a0));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 900;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(11, 32, 68, .22);
            position: relative;
        }

        .tech-card-avatar::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 2px solid var(--navy-100, #dce9fc);
        }

        .tech-card-info {
            flex: 1;
            min-width: 0;
        }

        .tech-card-name {
            font-weight: 800;
            font-size: 15px;
            color: var(--ink, #0d1b35);
            letter-spacing: -.2px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tech-card-id {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--navy-100, #dce9fc);
            color: var(--navy-700, #112b5c);
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 11.5px;
            font-weight: 800;
        }

        /* ---- Detail rows ---- */
        .tech-card-details {
            border-top: 1px solid var(--surface-2, #f6f9ff);
            padding: 12px 20px;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .tc-detail-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--ink-2, #2c3e65);
        }

        .tc-detail-row .tc-ico {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: var(--surface-2, #f6f9ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .tc-detail-row .tc-val {
            flex: 1;
            font-weight: 500;
        }

        .tc-detail-row .tc-val.muted-val {
            color: var(--muted, #64748b);
            font-weight: 400;
        }

        /* ---- Empty / no results ---- */
        .tech-empty {
            grid-column: 1 / -1;
            padding: 60px 24px;
            text-align: center;
            color: var(--muted, #64748b);
        }

        .tech-empty .te-icon {
            font-size: 52px;
            display: block;
            margin-bottom: 14px;
        }

        .tech-empty h3 {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: var(--ink, #0d1b35);
        }

        .tech-empty p {
            margin: 0;
            font-size: 14px;
        }

        /* ---- Result count ---- */
        .result-count {
            font-size: 13px;
            color: var(--muted, #64748b);
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .result-count strong {
            color: var(--ink, #0d1b35);
            font-weight: 800;
        }
    </style>

    {{-- Hero --}}
    <div class="tech-hero">
        <div class="tech-hero-text">
            <h2>👷 Master Data Teknisi</h2>
            <p>Daftar teknisi lapangan Indo Motor Lestari yang terdaftar dalam sistem.</p>
        </div>
        <div class="tech-hero-stat">
            <div class="ths-num">{{ count($technicians) }}</div>
            <div class="ths-lbl">Total Teknisi</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('dashboard.technicians') }}" class="tech-toolbar" id="search-form">
        <div class="search-input-wrap">
            <span class="search-ico">🔍</span>
            <input type="text" name="search" id="search-input" value="{{ $search }}"
                placeholder="Cari nama, ID, atau no. HP teknisi..." autocomplete="off">
        </div>
        <button class="btn" type="submit">Cari</button>
        @if ($search)
            <a href="{{ route('dashboard.technicians') }}" class="btn btn-secondary">✕ Reset</a>
        @endif
    </form>

    {{-- Result info --}}
    @if ($search)
        <div class="result-count">
            🔍 Menampilkan <strong>{{ count($technicians) }}</strong> hasil untuk "<em>{{ $search }}</em>"
        </div>
    @endif

    {{-- Grid --}}
    <div class="tech-grid">
        @forelse ($technicians as $technician)
            @php
                $initial = mb_strtoupper(mb_substr($technician->full_name ?? 'T', 0, 1));
                $phone = $technician->cell_phone ?: $technician->home_phone ?: null;
                $address = collect([$technician->address ?? null, $technician->city ?? null])
                    ->filter()
                    ->implode(', ');
                $colors = [
                    '135deg,#112b5c,#2451a0',
                    '135deg,#7c3aed,#5b21b6',
                    '135deg,#059669,#047857',
                    '135deg,#c8102e,#8b0c1e',
                    '135deg,#0284c7,#0369a1',
                ];
                $color = $colors[$loop->index % count($colors)];
            @endphp
            <div class="tech-card">
                <div class="tech-card-top">
                    <div class="tech-card-avatar" style="background: linear-gradient({{ $color }});">
                        {{ $initial }}
                    </div>
                    <div class="tech-card-info">
                        <div class="tech-card-name" title="{{ $technician->full_name ?? '-' }}">
                            {{ $technician->full_name ?? '-' }}
                        </div>
                        @if ($technician->user_id)
                            <span class="tech-card-id">🪪 {{ $technician->user_id }}</span>
                        @endif
                    </div>
                </div>
                <div class="tech-card-details">
                    <div class="tc-detail-row">
                        <div class="tc-ico">📱</div>
                        <div class="tc-val {{ $phone ? '' : 'muted-val' }}">
                            {{ $phone ?? 'Tidak ada nomor HP' }}
                        </div>
                    </div>
                    <div class="tc-detail-row">
                        <div class="tc-ico">✉️</div>
                        <div class="tc-val {{ $technician->email ? '' : 'muted-val' }}">
                            {{ $technician->email ?? 'Email tidak tersedia' }}
                        </div>
                    </div>
                    <div class="tc-detail-row">
                        <div class="tc-ico">📍</div>
                        <div class="tc-val {{ $address ? '' : 'muted-val' }}" style="line-height:1.4;">
                            {{ $address ?: 'Alamat tidak tersedia' }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="tech-empty">
                <span class="te-icon">😕</span>
                <h3>Tidak Ada Teknisi Ditemukan</h3>
                <p>
                    @if ($search)
                        Tidak ada teknisi yang cocok dengan pencarian "<em>{{ $search }}</em>".
                    @else
                        Belum ada data teknisi terdaftar dalam sistem.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @push('scripts')
        <script>
            // Live search with debounce
            let searchTimer;
            document.getElementById('search-input').addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    document.getElementById('search-form').submit();
                }, 500);
            });
        </script>
    @endpush
@endsection
