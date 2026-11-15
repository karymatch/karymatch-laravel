@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="card" style="max-width:420px;margin:30px auto">
  <h2>Masuk</h2>
  <form method="post" action="{{ route('login.post') }}">
    @csrf
    <label>Email</label><input type="email" name="email" value="{{ old('email') }}" required autofocus>
    <label>Sandi</label><input type="password" name="password" required>
    <label style="font-weight:400"><input type="checkbox" name="remember" value="1" style="width:auto"> Tetap masuk di perangkat ini</label>
    <div class="acts"><button class="btn p">Masuk</button><a class="btn" href="{{ route('register') }}">Daftar</a></div>
  </form>
  <p class="mu">Admin, pekerja, dan perusahaan masuk lewat halaman yang sama. Sistem mengarahkan sesuai peran akun.</p>
</div>
@endsection
