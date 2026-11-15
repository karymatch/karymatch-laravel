@extends('layouts.app')
@section('title', 'KaryMatch · Platform Lowongan Kerja Terverifikasi')
@section('content')
@if(!auth()->check())
<div class="hm">
  <section class="hm-hero">
    <img alt="Suasana gedung perusahaan" src="{{ asset('img/hero.jpg') }}" onerror="this.remove()">
    <div class="hm-top">
      <div class="hm-tab">
        <a class="logo" href="{{ route('home') }}"><img class="bi" alt="" src="{{ asset('img/logo-icon.png') }}" style="width:30px;height:30px;margin-right:6px">Kary<span>Match</span></a>
        <nav class="hm-nav" aria-label="Navigasi halaman">
          <a href="#hm-top">Home</a>
          <a href="#hm-tentang">Tentang Kami</a>
          <a href="#hm-cara">Cara Kerja</a>
          <a href="#hm-kontak">Kontak</a>
        </nav>
      </div>
      <a class="hm-adm" href="{{ route('login') }}">Masuk</a>
    </div>
    <div class="hm-body" id="hm-top">
      <div class="hm-eb">Platform Lowongan Kerja Terverifikasi</div>
      <h1>Temukan pekerjaan impianmu di perusahaan terverifikasi.</h1>
      <div class="en">Find your next job at verified companies.</div>
      <p class="ds">KaryMatch mempertemukan pencari kerja dengan perusahaan terverifikasi. Lihat kecocokan skill, pantau lamaran, dan atur jadwal interview dengan aman.</p>
      <a class="btn p" href="{{ route('login') }}">Masuk / Daftar</a>
    </div>
  </section>
  <section id="hm-tentang" class="hm-sec">
    <h2>Tentang Kami</h2>
    <p class="hm-lead">KaryMatch adalah platform lowongan kerja yang mempertemukan pencari kerja dengan perusahaan yang sudah terverifikasi. Kami ingin proses melamar kerja lebih jelas, lebih aman, dan tidak membuang waktu: skill yang cocok langsung terlihat, status lamaran bisa dipantau, dan jadwal interview diatur rapi di satu tempat.</p>
    <div class="hm-feat">
      <div class="card"><div class="hm-ic">♢</div><b>Perusahaan terverifikasi</b><div class="mu">Setiap perusahaan diperiksa admin sebelum boleh memasang lowongan, untuk mencegah penipuan.</div></div>
      <div class="card"><div class="hm-ic">◎</div><b>Skor kecocokan skill</b><div class="mu">Lihat seberapa cocok CV kamu dengan tiap lowongan sebelum melamar.</div></div>
      <div class="card"><div class="hm-ic">▦</div><b>Interview terjadwal</b><div class="mu">Konfirmasi jadwal, dapat pengingat, dan file kalender langsung dari aplikasi.</div></div>
    </div>
  </section>
  <section id="hm-cara" class="hm-sec">
    <h2>Cara Kerja</h2>
    <p class="hm-lead">Empat langkah dari daftar sampai interview.</p>
    <div class="hm-feat">
      <div class="card"><div class="hm-ic">1</div><b>Daftar &amp; buat CV</b><div class="mu">Masuk dengan email dan kode OTP, lalu isi skill dan pengalamanmu.</div></div>
      <div class="card"><div class="hm-ic">2</div><b>Cari &amp; lamar</b><div class="mu">Telusuri lowongan, lalu lamar dengan satu klik.</div></div>
      <div class="card"><div class="hm-ic">3</div><b>Pantau status</b><div class="mu">Dikirim, ditinjau, interview, hingga penawaran: semua tahap terlihat.</div></div>
      <div class="card"><div class="hm-ic">4</div><b>Interview</b><div class="mu">Terima undangan, konfirmasi, dan datang sesuai jadwal.</div></div>
    </div>
  </section>
  <section id="hm-kontak" class="hm-sec">
    <h2>Kontak</h2>
    <p class="hm-lead">Ada pertanyaan atau menemukan lowongan mencurigakan? Hubungi kami.</p>
    <div class="hm-kt">
      <div class="card"><b>✉ Email</b><div>support@karymatch.id</div></div>
      <div class="card"><b>💬 WhatsApp</b><div>0800-1234-5678</div></div>
      <div class="card"><b>📍 Alamat</b><div>Jl. Malioboro, Yogyakarta</div></div>
    </div>
    <p class="mu" style="margin-top:12px">KaryMatch tidak pernah meminta uang dari pelamar. Laporkan lowongan yang meminta biaya, transfer, atau OTP.</p>
    <a class="btn hm-up" href="#hm-top">↑ Kembali ke atas</a>
  </section>
</div>
@else

<h1 class="hl">Cari kerja yang cocok, dan aman.</h1>
<p class="sub">Semua perusahaan di sini sudah diverifikasi admin sebelum boleh memasang iklan.</p>

<form method="get" class="card">
  <div class="srch">
    <label>Posisi, perusahaan, atau skill<input name="q" value="{{ $q }}" placeholder="mis. Web Developer"></label>
    <label>Kota<input name="kota" value="{{ $city }}" placeholder="mis. Bekasi"></label>
    <label>Tipe
      <select name="tipe">
        <option value="">Semua</option>
        @foreach(\App\Http\Controllers\HomeController::TYPES as $t)<option @selected($type === $t)>{{ $t }}</option>@endforeach
      </select>
    </label>
    <div class="fl"><button class="btn p">Cari</button><a class="btn" href="{{ route('home') }}">Reset</a></div>
  </div>
  @if($isSeeker)
    <label class="chk" style="margin-top:12px"><input type="checkbox" name="cocok" value="1" @checked($match)> Hanya yang cocok dengan skill saya (skor ≥ 40%)</label>
    @if($match && ! ($cv && $cv->skills))<div class="mu">Isi skill di <a href="{{ route('cv.edit') }}">CV &amp; profil</a> dulu supaya skor kecocokan bisa dihitung.</div>@endif
  @endif
</form>

<div class="grid">
  @forelse($posts as $post)
    @include('jobs._card', ['post' => $post, 'cv' => $cv, 'isSeeker' => $isSeeker, 'savedIds' => $savedIds, 'appliedIds' => $appliedIds])
  @empty
    <div class="card">Tidak ada lowongan yang cocok dengan pencarianmu.</div>
  @endforelse
</div>
<div style="margin:16px 0">{{ $posts->links() }}</div>
@endif
@endsection
