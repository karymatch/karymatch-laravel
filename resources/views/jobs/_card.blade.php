@php
  $badge   = $post->company->planBadge();
  $isSeeker = $isSeeker ?? false;
  $cv      = $cv ?? null;
  $score   = ($isSeeker && $post->skills) ? $post->matchScore($cv) : null;
  $isSaved = in_array($post->id, $savedIds ?? [], true);
  $isApplied = in_array($post->id, $appliedIds ?? [], true);
  $labels  = [];
  if ($post->posted_at->gt(now()->subDays(3))) $labels[] = 'Baru';
  if ($post->daysLeft() <= 3) $labels[] = 'Segera ditutup';
@endphp
<div class="card jc job {{ $badge }}">
  <div class="fl">
    @include('jobs._logo', ['name' => $post->company->name])
    <div style="flex:1;min-width:0">
      <h3 style="margin:0"><a class="ttl" href="{{ route('jobs.show', $post) }}">{{ $post->title }}</a>
        @if($badge)<span class="badge {{ $badge }}">{{ $badge === 'pro' ? '★ PRO' : '✔ BISNIS' }}</span>@endif
      </h3>
      <div class="ok">✔ {{ $post->company->name }}</div>
      <div class="mu">{{ $post->city }} · {{ $post->type }} · {{ $post->salaryLabel() }}</div>
    </div>
    @if($score !== null)<div class="ring" style="--p:{{ $score }}" title="Kecocokan skill"><b>{{ $score }}%</b></div>@endif
  </div>
  @if($labels || $post->skills)
    <div class="tg" style="margin-top:8px">
      @foreach($labels as $l)<i class="n">{{ $l }}</i>@endforeach
      @foreach(($post->skills ?? []) as $s)<i>{{ $s }}</i>@endforeach
    </div>
  @endif
  <div class="mu" style="margin-top:8px">Tayang {{ $post->posted_at->locale('id')->translatedFormat('j M Y') }} – {{ $post->ends_at->locale('id')->translatedFormat('j M Y') }} · sisa {{ $post->daysLeft() }} hari</div>
  <div class="fl" style="margin-top:10px">
    <a class="btn p" href="{{ route('jobs.show', $post) }}">Detail</a>
    @if($isApplied)<a class="btn" href="{{ route('applications.index') }}">Sudah dilamar</a>@endif
    @if($isSeeker)
      <form method="post" action="{{ route('saved.toggle', $post) }}">@csrf
        <button class="chip {{ $isSaved ? 'on' : '' }}">★ {{ $isSaved ? 'Tersimpan' : 'Simpan' }}</button>
      </form>
    @endif
  </div>
</div>
