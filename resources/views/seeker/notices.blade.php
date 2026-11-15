@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<h2>Notifikasi</h2>
@forelse($notices as $n)
  <div class="card nt {{ $n->read_at ? '' : 'new' }}">
    {{ $n->message }}
    <div class="mu">{{ $n->created_at->locale('id')->diffForHumans() }}</div>
  </div>
@empty
  <div class="card">Belum ada notifikasi.</div>
@endforelse
@endsection
