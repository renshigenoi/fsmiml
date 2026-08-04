@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: var(--bg);">

    <div style="width: 100%; max-width: 400px;">

        <!-- Logo & Brand -->
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="display: inline-block; background: #fff; border-radius: 18px; padding: 16px 24px; box-shadow: var(--shadow-lg); margin-bottom: 20px;">
                <img src="{{ asset('assets/images/iml-logo.png') }}" alt="Indo Motor Lestari" style="height: 52px; display: block;" onerror="this.style.display='none'">
            </div>
            <h1 style="margin: 0 0 6px; font-size: 22px; font-weight: 900; color: var(--ink); letter-spacing: -.4px;">FSM Admin</h1>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">Masuk ke dashboard koordinator</p>
        </div>

        <!-- Login Card -->
        <div class="card" style="padding: 28px; border-radius: 20px; box-shadow: var(--shadow-lg);">
            <form method="POST" action="{{ url('/login') }}" id="login-form">
                @csrf

                <label for="email">Email</label>
                <div style="position: relative; margin-bottom: 14px;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; opacity: .45; pointer-events: none;">📧</span>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           placeholder="koordinator@indomotorlestari.co.id"
                           required
                           autofocus
                           style="padding-left: 42px; margin-bottom: 0;">
                </div>

                <label for="password">Password</label>
                <div style="position: relative; margin-bottom: 18px;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; opacity: .45; pointer-events: none;">🔒</span>
                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="••••••••"
                           required
                           style="padding-left: 42px; margin-bottom: 0;">
                </div>

                <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; color: var(--ink-2); text-transform: none; letter-spacing: 0; margin-bottom: 20px; cursor: pointer;" for="remember">
                    <input type="checkbox" name="remember" id="remember" style="width: auto; margin: 0; accent-color: var(--red-500);">
                    Ingat saya
                </label>

                <button class="btn" type="submit" id="btn-login" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px;">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>

        <p style="text-align: center; margin-top: 24px; color: var(--muted); font-size: 12px;">
            Indo Motor Lestari © 2026 · FSM v2
        </p>
    </div>
</div>
@endsection
