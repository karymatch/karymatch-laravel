<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light">
<title>@yield('title', 'KaryMatch')</title>
<link rel="icon" type="image/png" href="{{ asset('img/logo-icon.png') }}">
<link rel="stylesheet" href="{{ asset('css/karymatch.css') }}">
<link rel="stylesheet" href="{{ asset('css/compat.css') }}">
</head>
<body>
@php($u = auth()->user())
@auth
<header class="hd">
  <a class="logo" href="{{ route('home') }}"><img class="bi" alt="" src="{{ asset('img/logo-icon.png') }}">Kary<span>Match</span></a>
  <nav class="nav">
    @if(! $u || $u->role === 'pekerja')
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home','jobs.show') ? 'on' : '' }}">Cari lowongan</a>
    @endif
    @if($u && $u->role === 'pekerja')
      <a href="{{ route('saved.index') }}" class="{{ request()->routeIs('saved.*') ? 'on' : '' }}">Tersimpan</a>
      <a href="{{ route('applications.index') }}" class="{{ request()->routeIs('applications.*') ? 'on' : '' }}">Riwayat lamaran</a>
      <a href="{{ route('cv.edit') }}" class="{{ request()->routeIs('cv.*') ? 'on' : '' }}">CV &amp; profil</a>
    @endif
    @if($u && $u->role === 'perusahaan')
      <a href="{{ route('company.dashboard') }}" class="{{ request()->routeIs('company.*') ? 'on' : '' }}">Dasbor perusahaan</a>
    @endif
    @if($u && $u->role === 'admin')
      <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'on' : '' }}">Admin</a>
    @endif
    @if($u)
      @php($unread = $u->unreadNotices())
      <a href="{{ route('notices.index') }}" class="{{ request()->routeIs('notices.*') ? 'on' : '' }}">Notifikasi @if($unread)<em>{{ $unread > 99 ? '99+' : $unread }}</em>@endif</a>
    @endif
  </nav>
  <span class="sp"></span>
  @auth
    <span class="who">{{ $u->name }}</span>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="btn">Keluar</button></form>
  @else
    <a class="btn" href="{{ route('login') }}">Masuk</a>
    <a class="btn p" href="{{ route('register') }}">Daftar</a>
  @endauth
</header>
@endauth

<main class="{{ auth()->guest() ? 'public-main' : '' }}">
  @if(session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
  @if(session('error'))<div class="flash err">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="flash err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
  @yield('content')
</main>
@auth<footer>KaryMatch</footer>@endauth
@stack('scripts')
</body>
</html>
