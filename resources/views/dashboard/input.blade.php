@extends('layouts.app')

@section('title', 'Input SPK')

@section('content')
    <div class="card">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

            <h3 class="mt">Lokasi Pemasangan</h3>
            <label for="location-search">Cari lokasi / nama tempat</label>
            <div class="search-bar">
                <input type="text" id="location-search" placeholder="Cari alamat, nama jalan, atau landmark..." autocomplete="off">
                <button type="button" id="location-search-btn" class="btn">Cari</button>
            </div>
            <label for="location-address">Alamat (bisa diedit / diketik ulang)</label>
            <input type="text" id="location-address" name="location_address" placeholder="Alamat lengkap lokasi pemasangan" autocomplete="off">
            <button type="button" id="location-geocode" class="btn btn-secondary">Cari di Peta dari Alamat</button>
            <div id="location-map" class="mt" style="height: 340px; border: 1px solid var(--border); border-radius: 10px; z-index: 0;"></div>
            <p class="muted small">Klik di peta atau geser pin untuk mengatur titik lokasi. Alamat dari SPK otomatis dicari di peta saat SPK dipilih.</p>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <button class="btn" type="submit">Simpan &amp; Assign Teknisi</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const selectedSpk = {
        set(data) {
            document.getElementById('spk-selected').classList.remove('hidden');
            document.getElementById('s-spk').textContent = data.spk_no || '-';
            document.getElementById('s-customer').textContent = data.customer_name || '-';
            const fullAddress = [data.address, data.city, data.state].filter(Boolean).join(', ');
            document.getElementById('s-address').textContent = fullAddress || '-';
            document.getElementById('location-address').value = fullAddress;
            document.getElementById('s-car').textContent = [data.car_brand, data.car_model].filter(Boolean).join(' ') || '-';
            document.getElementById('s-date').textContent = data.installation_date || '-';
            document.getElementById('legacy-sales-serial').value = data.serial || '';
            const dateInput = document.getElementById('scheduled-start-at');
            if (!dateInput.value && data.installation_date) {
                dateInput.value = data.installation_date.slice(0, 10);
            }
            autoGeocode(fullAddress);
        },
        clear() {
            document.getElementById('spk-selected').classList.add('hidden');
            document.getElementById('legacy-sales-serial').value = '';
        },
    };

    // ---------- Lokasi Pemasangan (Leaflet + Nominatim/OSM) ----------
    const DEFAULT_MAP = { lat: -2.5489, lng: 118.0149, zoom: 5 };
    let map = null;
    let pin = null;
    let geocodeSeq = 0;

    function initLocationMap() {
        if (map || typeof L === 'undefined') return;
        map = L.map('location-map').setView([DEFAULT_MAP.lat, DEFAULT_MAP.lng], DEFAULT_MAP.zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);
        map.on('click', (e) => placePin(e.latlng.lat, e.latlng.lng, true));
    }

    function placePin(lat, lng, reverse) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
        if (!pin) {
            pin = L.marker([lat, lng], { draggable: true }).addTo(map);
            pin.on('dragend', () => {
                const p = pin.getLatLng();
                placePin(p.lat, p.lng, true);
            });
        } else {
            pin.setLatLng([lat, lng]);
        }
        if (reverse) reverseGeocode(lat, lng);
    }

    async function nominatimGet(path, params) {
        const url = 'https://nominatim.openstreetmap.org/' + path + '?' + new URLSearchParams(params).toString();
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Layanan pencarian lokasi error (' + response.status + ')');
        return response.json();
    }

    async function searchAndPin(query, zoom = 16, silent = false) {
        initLocationMap();
        if (typeof L === 'undefined' || !query.trim()) return;
        const seq = ++geocodeSeq;
        try {
            const results = await nominatimGet('search', {
                format: 'jsonv2',
                q: query,
                limit: 1,
                countrycodes: 'id',
            });
            if (seq !== geocodeSeq) return;
            if (!results.length) {
                if (!silent) alert('Lokasi tidak ditemukan. Coba kata kunci lain atau klik langsung di peta.');
                return;
            }
            const r = results[0];
            map.flyTo([parseFloat(r.lat), parseFloat(r.lon)], zoom);
            placePin(parseFloat(r.lat), parseFloat(r.lon), true);
        } catch (err) {
            if (!silent) alert('Gagal mencari lokasi: ' + err.message);
        }
    }

    async function reverseGeocode(lat, lng) {
        try {
            const data = await nominatimGet('reverse', { format: 'jsonv2', lat: lat, lon: lng, zoom: 16 });
            const input = document.getElementById('location-address');
            if (data && data.display_name && !input.value.trim()) {
                input.value = data.display_name;
            }
        } catch (err) {
            // Abaikan; koordinator tetap bisa mengetik alamat manual.
        }
    }

    function autoGeocode(address) {
        if (!address.trim()) return;
        initLocationMap();
        searchAndPin(address, 14, true);
    }

    document.getElementById('location-search-btn').addEventListener('click', () => {
        searchAndPin(document.getElementById('location-search').value, 16, false);
    });
    document.getElementById('location-search').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('location-search-btn').click();
        }
    });
    document.getElementById('location-geocode').addEventListener('click', () => {
        searchAndPin(document.getElementById('location-address').value, 16, false);
    });

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
