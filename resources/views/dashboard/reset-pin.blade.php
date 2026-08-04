@extends('layouts.app')

@section('title', 'Reset PIN Teknisi')

@section('content')
<style>
    .rp-card {
        max-width: 560px;
    }
</style>

<div class="wo-hero">
    <div class="wo-hero-text">
        <h2>🔑 Reset PIN Teknisi</h2>
        <p>Atur ulang PIN aplikasi mobile untuk teknisi yang lupa PIN.</p>
    </div>
</div>

<div class="card rp-card">
    @if (session('success'))
        <div class="flash flash-success">✅ {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="flash flash-error">⚠️ {{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash flash-error">
            <span>⚠️</span>
            <ul style="margin:0;padding-left:16px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dashboard.reset-pin') }}">
        @csrf
        <label for="email">Akun (email)</label>
        <select name="email" id="email" required style="margin-bottom:14px;">
            <option value="">— Pilih akun —</option>
            @foreach ($users as $user)
                <option value="{{ $user->email }}" @selected(old('email') === $user->email)>
                    {{ $user->email }} — {{ $user->name }}
                </option>
            @endforeach
        </select>

        <label for="pin">PIN Baru (6 digit)</label>
        <input type="password" name="pin" id="pin" inputmode="numeric" maxlength="6" required
               pattern="[0-9]{6}" title="6 digit angka" style="margin-bottom:14px;">

        <label for="pin_confirmation">Ulangi PIN Baru</label>
        <input type="password" name="pin_confirmation" id="pin_confirmation" inputmode="numeric" maxlength="6" required
               pattern="[0-9]{6}" title="6 digit angka" style="margin-bottom:18px;">

        <button class="btn" type="submit"
            style="background:var(--red-grad,#c8102e);color:#fff;">💾 Simpan PIN Baru</button>
    </form>

    <p style="font-size:12.5px;color:var(--muted);margin-top:14px;">
        PIN tersimpan ter-hash di server. Teknisi cukup memasukkan PIN baru dari sini saat login berikutnya.
    </p>
</div>
@endsection
