@extends('layouts.guest')
@section('title', 'Super Admin')

@section('content')
<div class="auth-deco" aria-hidden="true"><i></i><i></i></div>
<div class="center-screen">
  <div class="auth-card glass">
    <div class="auth-brand">
      <div class="mark god-mark-lg"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <h1>Kontrol Global</h1>
      <p>Super Admin Platform — Ruang GTK</p>
    </div>

    <form method="POST" action="{{ route('super.attempt') }}" class="stack">
      @csrf
      <div class="field">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" class="input" value="{{ old('username') }}" required autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" class="input" required>
      </div>
      @error('username') <div class="error-text">{{ $message }}</div> @enderror

      <button type="submit" class="btn btn-god" style="width:100%">Masuk Panel Global</button>
    </form>

    <p style="margin-top:16px;text-align:center;font-size:13px">
      <a href="{{ route('landing') }}">← Kembali ke halaman utama</a>
    </p>
  </div>
</div>
@endsection
