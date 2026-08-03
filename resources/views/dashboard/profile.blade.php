@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <p><a href="{{ route('dashboard') }}">&larr; Kembali ke dashboard</a></p>

    <div class="card" style="max-width:520px;">
        <h2>Profil Koordinator</h2>

        <form method="POST" action="{{ url('/dashboard/profile') }}">
            @csrf

            <label for="name">Nama</label>
            <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required>

            <label for="email">Email</label>
            <input type="email" id="email" value="{{ auth()->user()->email }}" readonly
                   style="background:#f1f5f9;color:var(--muted);">
            <p class="muted" style="margin-top:-8px;">Email tidak dapat diubah.</p>

            <label for="phone">No. HP / WhatsApp</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}" maxlength="30"
                   placeholder="08xxxxxxxxxx">

            <hr style="border:0;border-top:1px solid var(--border);margin:16px 0;">
            <h3 style="font-size:14px;margin:0 0 12px;">Ganti Password</h3>

            <label for="current_password">Password saat ini</label>
            <input type="password" name="current_password" id="current_password" autocomplete="current-password">

            <label for="password">Password baru</label>
            <input type="password" name="password" id="password" minlength="8" autocomplete="new-password">

            <label for="password_confirmation">Ulangi password baru</label>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password">

            <button class="btn" type="submit">Simpan Profil</button>
        </form>
    </div>
@endsection
