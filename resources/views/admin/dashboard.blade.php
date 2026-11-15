@extends('layouts.app')
@section('title', 'Admin')
@section('content')
<h1 style="color:var(--dark)">Admin · Verifikasi perusahaan</h1>
@forelse($companies as $c)
  <div class="card">
    <b>{{ $c->name }}</b> · {{ $c->city }} · {{ $c->user->email }}
    <span class="tag {{ $c->verification_status === 'disetujui' ? 'ok' : ($c->verification_status === 'ditolak' ? 'bd' : '') }}">{{ $c->verification_status }}</span>
    <div class="acts">
      @if($c->verification_status !== 'disetujui')
        <form method="post" action="{{ route('admin.companies.approve', $c) }}">@csrf<button class="btn s p">Setujui</button></form>
      @endif
      <form method="post" action="{{ route('admin.companies.reject', $c) }}" style="display:flex;gap:6px">
        @csrf
        <input name="reason" placeholder="Alasan penolakan" required style="width:220px">
        <button class="btn s d">Tolak</button>
      </form>
    </div>
  </div>
@empty
  <div class="card">Belum ada perusahaan terdaftar.</div>
@endforelse
@endsection
