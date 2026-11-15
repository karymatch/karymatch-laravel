@extends('layouts.app')
@section('title', 'Riwayat lamaran')
@section('content')
<h2>Riwayat lamaran</h2>
<div class="mu" style="margin-bottom:12px">Semua lamaran tersimpan, termasuk yang ditarik atau ditolak, walau lowongan sudah ditutup.</div>

@forelse($apps as $a)
  @php($post = $a->jobPost)
  @php($cls = $a->status === 'diterima' ? 'ok' : (in_array($a->status, ['ditolak','ditarik'], true) ? 'bd' : ''))
  <div class="card">
    <div class="fl" style="flex-wrap:nowrap;align-items:flex-start">
      @include('jobs._logo', ['name' => $post->company->name, 'size' => 42])
      <div style="flex:1;min-width:0">
        <b>{{ $post->title }}</b> · {{ $post->company->name }}
        <div><b class="{{ $cls }}">{{ $a->label() }}</b>
          @unless($post->isLive())<span class="mu"> (lowongan sudah tidak tayang)</span>@endunless</div>
      </div>
    </div>
    <div class="bar" style="margin:10px 0"><i style="width:{{ $a->progress() }}%"></i></div>
    <div class="tl">
      @foreach(($a->events ?? []) as $e)
        <span><b>{{ $a->label($e['s']) }}</b> {{ \Illuminate\Support\Carbon::parse($e['t'])->locale('id')->translatedFormat('j M Y, H.i') }}</span>@unless($loop->last)<span>→</span>@endunless
      @endforeach
    </div>

    @if(! empty($a->screening_answers))
      <details style="margin-top:8px"><summary class="mu" style="cursor:pointer">Jawaban screening</summary>
        @foreach($a->screening_answers as $x)<div class="ans"><div class="mu">{{ $x['q'] }}</div>{{ $x['a'] }}</div>@endforeach
      </details>
    @endif

    <div class="fl" style="margin-top:10px">
      <a class="btn" href="{{ route('applications.cv', $a) }}">CV terkirim</a>
      @if($post->isLive())<a class="btn" href="{{ route('jobs.show', $post) }}">Lihat lowongan</a>@endif
      @if(! $a->isFinal())
        <form method="post" action="{{ route('applications.withdraw', $a) }}" onsubmit="return confirm('Tarik lamaran ini?')">@csrf
          <button class="btn d" @disabled(! $a->canWithdraw()) title="{{ $a->canWithdraw() ? '' : 'Hanya bisa ditarik selama masih Dikirim' }}">Tarik lamaran</button>
        </form>
      @endif
    </div>
  </div>
@empty
  <div class="card">Belum ada lamaran. <a href="{{ route('home') }}">Cari lowongan</a> yang cocok dengan skill kamu.</div>
@endforelse
@endsection
