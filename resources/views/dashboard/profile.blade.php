@extends('layouts.app')

@section('title', 'Profil Koordinator')

@section('content')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 860px) {
        .profile-grid { grid-template-columns: 1fr; }
    }

    /* Identity card */
    .identity-card {
        background: linear-gradient(160deg, var(--navy-800, #0b2044) 0%, var(--navy-600, #1a3a7a) 100%);
        border-radius: 20px;
        padding: 32px 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(11,32,68,.30);
    }
    .identity-card::before {
        content: '';
        position: absolute;
        width: 220px; height: 220px;
        border-radius: 50%;
        background: rgba(200,16,46,.15);
        top: -80px; right: -60px;
        pointer-events: none;
    }
    .identity-card::after {
        content: '';
        position: absolute;
        width: 150px; height: 150px;
        border-radius: 50%;
        background: rgba(58,107,200,.12);
        bottom: -50px; left: -40px;
        pointer-events: none;
    }

    /* Avatar */
    .avatar-wrap {
        position: relative;
        width: 88px;
        margin: 0 auto 20px;
    }
    .big-avatar {
        width: 88px; height: 88px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e01836, #8b0c1e);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        font-weight: 900;
        box-shadow: 0 6px 24px rgba(200,16,46,.40);
        border: 3px solid rgba(255,255,255,.20);
        position: relative;
        z-index: 1;
    }
    .avatar-ring {
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 2px dashed rgba(255,255,255,.25);
        animation: spin-slow 12s linear infinite;
    }
    @keyframes spin-slow { to { transform: rotate(360deg); } }

    .id-name {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.3px;
        text-align: center;
        margin-bottom: 4px;
    }
    .id-role {
        font-size: 12px;
        color: rgba(255,255,255,.60);
        text-align: center;
        text-transform: uppercase;
        letter-spacing: .10em;
        font-weight: 700;
        margin-bottom: 24px;
    }
    .id-divider {
        height: 1px;
        background: rgba(255,255,255,.12);
        margin: 0 -4px 20px;
    }
    .id-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
        font-size: 13px;
    }
    .id-row:last-child { margin-bottom: 0; }
    .id-row .id-ico {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,.10);
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .id-row .id-info { flex: 1; min-width: 0; }
    .id-row .id-label { color: rgba(255,255,255,.50); font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 2px; }
    .id-row .id-value { color: #fff; font-weight: 600; word-break: break-all; font-size: 13px; }

    /* Right column */
    .right-col { display: flex; flex-direction: column; gap: 20px; }

    /* Form cards */
    .form-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--line, #e2e8f4);
        box-shadow: 0 1px 4px rgba(11,32,68,.06);
        overflow: hidden;
    }
    .form-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--line, #e2e8f4);
        background: var(--surface-2, #f6f9ff);
    }
    .form-card-header .fch-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--navy-700, #112b5c), var(--navy-600, #1a3a7a));
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 3px 10px rgba(11,32,68,.20);
    }
    .form-card-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--ink, #0d1b35);
        letter-spacing: -.2px;
    }
    .form-card-header p {
        margin: 2px 0 0;
        font-size: 12px;
        color: var(--muted, #64748b);
    }
    .form-card-body {
        padding: 22px;
    }

    /* Field group */
    .field-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 20px;
    }
    @media (max-width: 600px) {
        .field-group { grid-template-columns: 1fr; }
    }
    .field-full { grid-column: 1 / -1; }

    .form-card input[type="text"],
    .form-card input[type="email"],
    .form-card input[type="password"] {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid var(--line, #e2e8f4);
        border-radius: 9px;
        font-size: 14px;
        font-family: inherit;
        margin-bottom: 16px;
        background: var(--surface-2, #f6f9ff);
        color: var(--ink, #0d1b35);
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .form-card input:focus {
        border-color: #c8102e;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(200,16,46,.10);
    }
    .form-card input[readonly] {
        background: var(--bg, #f0f4fb);
        color: var(--muted, #64748b);
        cursor: not-allowed;
    }
    .form-card label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: 6px;
        color: var(--muted, #64748b);
    }
    .form-hint {
        font-size: 12px;
        color: var(--muted, #64748b);
        margin-top: -11px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Password strength */
    .pw-strength-bar {
        height: 4px;
        border-radius: 2px;
        margin-top: -11px;
        margin-bottom: 14px;
        background: var(--line, #e2e8f4);
        overflow: hidden;
    }
    .pw-strength-fill {
        height: 100%;
        border-radius: 2px;
        width: 0%;
        transition: width .3s, background .3s;
    }
    .pw-strength-label {
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 14px;
        margin-top: -11px;
        color: var(--muted, #64748b);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Action row */
    .form-action-row {
        display: flex;
        justify-content: flex-end;
        padding-top: 4px;
    }
    .btn-save {
        background: linear-gradient(135deg, #e01836, #8b0c1e);
        color: #fff;
        border: 0;
        padding: 12px 28px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 800;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(200,16,46,.30);
        transition: transform .15s, box-shadow .15s, opacity .15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -.1px;
    }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(200,16,46,.40); }
    .btn-save:active { transform: scale(.975); box-shadow: 0 2px 8px rgba(200,16,46,.20); }
    .btn-save:disabled { opacity: .6; pointer-events: none; }

    /* Danger zone */
    .danger-note {
        background: #fff7f7;
        border: 1px solid #fecdd3;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 13px;
        color: #991b1b;
        margin-bottom: 16px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
</style>

<div class="profile-grid">

    <!-- ===== LEFT: Identity Card ===== -->
    <div>
        <div class="identity-card">
            <!-- Avatar -->
            <div class="avatar-wrap">
                <div class="avatar-ring"></div>
                <div class="big-avatar">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>

            <div class="id-name">{{ auth()->user()->name }}</div>
            <div class="id-role">Koordinator FSM</div>

            <div class="id-divider"></div>

            <!-- Email -->
            <div class="id-row">
                <div class="id-ico">📧</div>
                <div class="id-info">
                    <div class="id-label">Email</div>
                    <div class="id-value">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <!-- Phone -->
            <div class="id-row">
                <div class="id-ico">📱</div>
                <div class="id-info">
                    <div class="id-label">No. HP</div>
                    <div class="id-value">{{ auth()->user()->phone ?: '—' }}</div>
                </div>
            </div>

            <!-- Member since -->
            <div class="id-row">
                <div class="id-ico">📅</div>
                <div class="id-info">
                    <div class="id-label">Bergabung sejak</div>
                    <div class="id-value">{{ auth()->user()->created_at->translatedFormat('d F Y') }}</div>
                </div>
            </div>

            <!-- Last login -->
            <div class="id-row">
                <div class="id-ico">🔐</div>
                <div class="id-info">
                    <div class="id-label">Login terakhir</div>
                    <div class="id-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT: Forms ===== -->
    <div class="right-col">

        {{-- ---- Informasi Akun ---- --}}
        <form method="POST" action="{{ url('/dashboard/profile') }}" id="form-profile">
            @csrf
            <input type="hidden" name="form_section" value="profile">

            <div class="form-card">
                <div class="form-card-header">
                    <div class="fch-icon">👤</div>
                    <div>
                        <h3>Informasi Akun</h3>
                        <p>Perbarui nama dan nomor kontak kamu</p>
                    </div>
                </div>
                <div class="form-card-body">
                    <div class="field-group">
                        <div>
                            <label for="name">Nama Lengkap</label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', auth()->user()->name) }}"
                                   placeholder="Nama koordinator"
                                   required
                                   autocomplete="name">
                        </div>
                        <div>
                            <label for="phone">No. HP / WhatsApp</label>
                            <input type="text"
                                   name="phone"
                                   id="phone"
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   placeholder="08xxxxxxxxxx"
                                   maxlength="30">
                        </div>
                        <div class="field-full">
                            <label for="email">Email</label>
                            <input type="email"
                                   id="email"
                                   value="{{ auth()->user()->email }}"
                                   readonly>
                            <p class="form-hint">🔒 Email terdaftar tidak dapat diubah.</p>
                        </div>
                    </div>
                    <div class="form-action-row">
                        <button class="btn-save" type="submit" id="btn-save-profile">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ---- Ganti Password ---- --}}
        <form method="POST" action="{{ url('/dashboard/profile') }}" id="form-password">
            @csrf
            <input type="hidden" name="form_section" value="password">

            <div class="form-card">
                <div class="form-card-header">
                    <div class="fch-icon" style="background: linear-gradient(135deg, #7c3aed, #5b21b6);">🔑</div>
                    <div>
                        <h3>Keamanan & Password</h3>
                        <p>Ganti password untuk menjaga keamanan akun</p>
                    </div>
                </div>
                <div class="form-card-body">

                    <div class="danger-note">
                        ℹ️ Biarkan kolom password kosong jika tidak ingin mengubah password.
                    </div>

                    <label for="current_password">Password saat ini</label>
                    <input type="password"
                           name="current_password"
                           id="current_password"
                           placeholder="Masukkan password lama"
                           autocomplete="current-password">

                    <div class="field-group">
                        <div>
                            <label for="password">Password baru</label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   placeholder="Min. 8 karakter"
                                   minlength="8"
                                   autocomplete="new-password"
                                   oninput="checkStrength(this.value)">
                            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-fill"></div></div>
                            <div class="pw-strength-label" id="pw-label"></div>
                        </div>
                        <div>
                            <label for="password_confirmation">Ulangi password baru</label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   placeholder="Ketik ulang password baru"
                                   autocomplete="new-password"
                                   oninput="checkMatch()">
                            <div class="pw-strength-label" id="pw-match-label"></div>
                        </div>
                    </div>

                    <div class="form-action-row">
                        <button class="btn-save" type="submit"
                                style="background: linear-gradient(135deg, #7c3aed, #5b21b6); box-shadow: 0 4px 16px rgba(124,58,237,.30);"
                                id="btn-save-password">
                            🔐 Perbarui Password
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div><!-- /right-col -->
</div>

@push('scripts')
<script>
    // ---- Password strength indicator ----
    function checkStrength(val) {
        const fill  = document.getElementById('pw-fill');
        const label = document.getElementById('pw-label');
        if (!val) { fill.style.width = '0%'; label.textContent = ''; return; }

        let score = 0;
        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { pct: '20%', color: '#ef4444', text: '⚠️ Sangat lemah' },
            { pct: '40%', color: '#f97316', text: '⚠️ Lemah' },
            { pct: '60%', color: '#eab308', text: '👍 Cukup' },
            { pct: '80%', color: '#22c55e', text: '✅ Kuat' },
            { pct: '100%', color: '#059669', text: '🛡️ Sangat kuat' },
        ];
        const lvl = levels[Math.min(score - 1, 4)] || levels[0];
        fill.style.width  = lvl.pct;
        fill.style.background = lvl.color;
        label.textContent = lvl.text;
        label.style.color = lvl.color;

        checkMatch();
    }

    // ---- Match check ----
    function checkMatch() {
        const pw    = document.getElementById('password').value;
        const conf  = document.getElementById('password_confirmation').value;
        const lbl   = document.getElementById('pw-match-label');
        if (!conf) { lbl.textContent = ''; return; }
        if (pw === conf) {
            lbl.textContent = '✅ Password cocok';
            lbl.style.color = '#059669';
        } else {
            lbl.textContent = '❌ Password tidak cocok';
            lbl.style.color = '#ef4444';
        }
    }

    // ---- Live preview of name in sidebar-ish way ----
    document.getElementById('name').addEventListener('input', function() {
        const first = this.value.trim().charAt(0).toUpperCase();
        const avatar = document.querySelector('.big-avatar');
        if (avatar && first) avatar.textContent = first;

        const idName = document.querySelector('.id-name');
        if (idName) idName.textContent = this.value || '{{ auth()->user()->name }}';
    });

    // ---- Live preview phone ----
    document.getElementById('phone').addEventListener('input', function() {
        const rows = document.querySelectorAll('.id-value');
        // phone is second id-row → index 1
        if (rows[1]) rows[1].textContent = this.value || '—';
    });
</script>
@endpush
@endsection
