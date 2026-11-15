@extends('layouts.app')
@section('title', 'Tersimpan')
@section('content')
<h2>Lowongan tersimpan</h2>
<div class="grid">
  @forelse($posts as $post)
    @include('jobs._card', ['post' => $post, 'cv' => $cv, 'isSeeker' => true, 'savedIds' => $savedIds, 'appliedIds' => $appliedIds])
  @empty
    <div class="card">Belum ada yang disimpan. Tekan ★ Simpan pada lowongan yang menarik.</div>
  @endforelse
</div>
@endsection
