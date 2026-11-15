@extends('layouts.app')
@section('title', 'Dasbor perusahaan')
@section('content')
<h1 style="color:var(--dark)">{{ $company->name }}</h1>

@if(! $company->isApproved())
  <div class="pal {{ $company->verification_status === 'ditolak' ? 'danger' : 'warn' }}">
    @if($company->verification_status === 'ditolak')
      Verifikasi ditolak: {{ $company->rejection_reason }}
    @else
      Perusahaan menunggu verifikasi admin. Iklan baru bisa dipasang setelah disetujui.
    @endif
  </div>
@endif

@foreach($alerts as $a)
  <div class="pal {{ $a['level'] }}"><span>{{ $a['level'] === 'danger' ? '⛔' : '⚠️' }}</span><span style="flex:1">{{ $a['text'] }}</span></div>
@endforeach

<div class="card">
  <b>Paket {{ $plan->name }}</b>
  @if($company->planIsActive())
    <span class="mu"> · {{ $company->plan_started_at->locale('id')->translatedFormat('j M Y') }} s.d. {{ $company->plan_ends_at->locale('id')->translatedFormat('j M Y') }} · sisa {{ $company->daysLeft() }} hari</span>
  @else
    <span class="mu"> · selamanya (tanpa batas waktu)</span>
  @endif
  <table style="margin-top:8px">
    <tr><td>Iklan aktif</td><td>{{ $live }} / {{ $plan->unlimitedAds() ? 'tanpa batas' : $plan->max_active_ads }}</td></tr>
    <tr><td>Masa tayang tiap iklan</td><td>{{ $plan->ad_duration_days }} hari</td></tr>
    <tr><td>Pertanyaan screening per iklan</td><td>maks {{ $plan->max_screening_questions }}</td></tr>
    <tr><td>Undangan per hari</td><td>{{ $plan->max_daily_invites }}</td></tr>
  </table>
</div>

<div class="acts" style="margin:14px 0"><a class="btn p" href="{{ route('company.jobs.create') }}">+ Pasang iklan baru</a></div>

@forelse($posts as $post)
  @php($expired = $post->isExpired())
  <div class="card">
    <b>{{ $post->title }}</b> · {{ $post->city }}
    @if($post->is_closed)<span class="tag bd">ditutup</span>
    @elseif($expired)<span class="tag bd">berakhir · tersimpan</span>
    @else<span class="tag ok">aktif</span>@endif
    <div class="mu">Tayang {{ $post->posted_at->locale('id')->translatedFormat('j M Y') }} – {{ $post->ends_at->locale('id')->translatedFormat('j M Y') }}@unless($expired) · sisa {{ $post->daysLeft() }} hari @endunless</div>
    <div class="acts">
      @if($expired)
        <span class="tag bd">🔒 Arsip</span>
      @else
        <a class="btn s" href="{{ route('company.jobs.edit', $post) }}">Edit</a>
      @endif
      <form method="post" action="{{ route('company.jobs.renew', $post) }}">@csrf<button class="btn s">Up ulang · {{ $plan->ad_duration_days }} hari</button></form>
      <form method="post" action="{{ route('company.jobs.duplicate', $post) }}">@csrf<button class="btn s">Duplikat</button></form>
      @unless($post->is_closed || $expired)
        <form method="post" action="{{ route('company.jobs.close', $post) }}">@csrf<button class="btn s d">Tutup</button></form>
      @endunless
    </div>
  </div>
@empty
  <div class="card">Belum ada iklan.</div>
@endforelse
@endsection
