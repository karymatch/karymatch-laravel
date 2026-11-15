@extends('layouts.app')
@section('title', 'CV terkirim')
@section('content')
<a href="{{ route('applications.index') }}" class="noprint">← Riwayat lamaran</a>
<h2 style="margin-top:8px">CV terkirim</h2>
<div class="mu noprint" style="margin-bottom:10px">Salinan CV saat melamar «{{ $app->jobPost->title }}» di {{ $app->jobPost->company->name }},
  {{ $app->created_at->locale('id')->translatedFormat('j M Y, H.i') }}. Perubahan CV setelahnya tidak memengaruhi salinan ini.</div>
@include('seeker._cv_preview', ['data' => $cv, 'name' => $cv['name'] ?? auth()->user()->name, 'email' => $cv['email'] ?? auth()->user()->email])
<div class="noprint"><button type="button" class="btn" onclick="window.print()">Unduh (cetak / simpan PDF)</button></div>
@endsection
