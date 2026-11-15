@extends('layouts.app')
@section('title', 'Daftar')
@section('content')
<div class="card" style="max-width:480px;margin:30px auto">
  <h2>Daftar</h2>
  <form method="post" action="{{ route('register.post') }}">
    @csrf
    <label>Daftar sebagai</label>
    <select name="role" id="role">
      <option value="pekerja" @selected(old('role') === 'pekerja')>Pencari kerja</option>
      <option value="perusahaan" @selected(old('role') === 'perusahaan')>Perusahaan</option>
    </select>
    <label>Nama</label><input name="name" value="{{ old('name') }}" required>
    <label>Email</label><input type="email" name="email" value="{{ old('email') }}" required>
    <div id="co" style="display:none">
      <label>Nama perusahaan</label><input name="company_name" value="{{ old('company_name') }}">
      <label>Kota</label><input name="city" value="{{ old('city') }}">
    </div>
    <div class="row">
      <div><label>Sandi (min. 8 karakter)</label><input type="password" name="password" required></div>
      <div><label>Ulangi sandi</label><input type="password" name="password_confirmation" required></div>
    </div>
    <div class="acts"><button class="btn p">Buat akun</button><a class="btn" href="{{ route('login') }}">Sudah punya akun</a></div>
  </form>
</div>
@push('scripts')
<script>
  const role = document.getElementById('role'), co = document.getElementById('co');
  const sync = () => co.style.display = role.value === 'perusahaan' ? 'block' : 'none';
  role.addEventListener('change', sync); sync();
</script>
@endpush
@endsection
