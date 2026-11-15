@extends('layouts.app')
@section('title', $post->title)
@section('content')
@php($badge = $post->company->planBadge())
<a href="{{ route('home') }}">← Kembali</a>
<div class="pst" style="margin-top:10px">
  <small>KAMI MEREKRUT @if($badge)<span class="badge {{ $badge }}">{{ $badge === 'pro' ? '★ PRO' : '✔ BISNIS' }}</span>@endif</small>
  <b>{{ $post->title }}</b>
  <span>{{ $post->company->name }} · {{ $post->city }} · {{ $post->type }}</span>
  <span>{{ $post->salaryLabel() }}</span>
  <span>Tayang {{ $post->posted_at->locale('id')->translatedFormat('j M Y') }} – {{ $post->ends_at->locale('id')->translatedFormat('j M Y') }} · sisa {{ $post->daysLeft() }} hari</span>
</div>

<div class="card" style="margin-top:14px">
  <h2>{{ $post->title }}</h2>
  <div class="ok">✔ {{ $post->company->name }}</div>
  <div>{{ $post->city }} · {{ $post->type }} · {{ $post->salaryLabel() }}</div>

  @if($post->skills)
    <h3 style="margin-top:14px">Skill dibutuhkan @if($isSeeker)<span class="mu">(kecocokan {{ $score }}%)</span>@endif</h3>
    @php($have = $cv ? $cv->skillKeys() : [])
    @foreach($post->skills as $s)
      @if($isSeeker)
        <div>@if(in_array(mb_strtolower($s), $have, true))<span class="skok">✔</span>@else<span class="skno">✗</span>@endif {{ $s }}</div>
      @else
        <div>• {{ $s }}</div>
      @endif
    @endforeach
  @endif

  <h3 style="margin-top:14px">Deskripsi</h3>
  <div>{!! nl2br(e($post->description ?: 'Belum ada deskripsi.')) !!}</div>
</div>

{{-- Melamar --}}
@if($isSeeker)
  @if($applied)
    <div class="card"><b>Sudah dilamar</b> · status: <b>{{ $applied->label() }}</b>
      <div class="acts"><a class="btn" href="{{ route('applications.index') }}">Lihat Riwayat lamaran</a></div></div>
  @elseif($cv && ! $cv->readyToApply())
    <div class="pal warn">Lengkapi CV dulu sebelum melamar. Masih kurang: {{ implode(', ', $cv->missingForApply()) }}.
      <a class="btn s p" href="{{ route('cv.edit') }}">Isi CV</a></div>
  @else
    <div class="card">
      <h3>Lamar lowongan ini</h3>
      <div class="mu">CV kamu saat ini dikirim sebagai salinan. Perubahan CV setelahnya tidak mengubah lamaran yang sudah terkirim.</div>
      <form method="post" action="{{ route('applications.store', $post) }}" class="f">
        @csrf
        @foreach($post->questions as $q)
          <label>{{ $loop->iteration }}. {{ $q->question }}
            <textarea name="answers[{{ $q->id }}]" rows="2" maxlength="500" required>{{ old('answers.'.$q->id) }}</textarea></label>
        @endforeach
        <div class="fl">
          <button class="btn p">Lamar sekarang</button>
          <button class="chip {{ $saved ? 'on' : '' }}" formnovalidate formaction="{{ route('saved.toggle', $post) }}">★ {{ $saved ? 'Tersimpan' : 'Simpan' }}</button>
        </div>
      </form>
    </div>
  @endif
@elseif(! auth()->check())
  <div class="card"><b>Mau melamar?</b> <a href="{{ route('login') }}">Masuk</a> atau <a href="{{ route('register') }}">daftar sebagai pencari kerja</a> dulu.</div>
@elseif($post->questions->count())
  <div class="card"><h3>Pertanyaan screening</h3><ol>@foreach($post->questions as $q)<li>{{ $q->question }}</li>@endforeach</ol></div>
@endif

@if($post->lat && $post->lng)
  <div class="card">
    <h3>Lokasi</h3>
    <div id="map" style="height:320px;border-radius:16px"></div>
  </div>
  @push('scripts')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  <script>
    const pos = [{{ (float) $post->lat }}, {{ (float) $post->lng }}];
    const map = L.map('map').setView(pos, 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '© OpenStreetMap'}).addTo(map);
    L.marker(pos).addTo(map).bindPopup(@json($post->company->name));
  </script>
  @endpush
@endif
@endsection
