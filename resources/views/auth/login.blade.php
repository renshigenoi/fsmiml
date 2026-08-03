@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div style="max-width:400px;margin:80px auto;">
        <div class="card">
            <h2>Login Koordinator</h2>
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <label style="display:flex;align-items:center;gap:6px;font-weight:400;color:var(--text);">
                    <input type="checkbox" name="remember" style="width:auto;"> Ingat saya
                </label>
                <button class="btn" type="submit" style="width:100%;">Masuk</button>
            </form>
        </div>
    </div>
@endsection
