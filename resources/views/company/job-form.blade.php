@extends('layouts.app')
@section('title', $post ? 'Edit iklan' : 'Iklan baru')
@section('content')
<div class="card">
  <h2>{{ $post ? 'Edit iklan' : 'Pasang iklan baru' }}</h2>
  @unless($post)<p class="mu">Paket {{ $plan->name }}: iklan tayang {{ $plan->ad_duration_days }} hari sejak hari ini.</p>@endunless
  <form method="post" action="{{ $post ? route('company.jobs.update', $post) : route('company.jobs.store') }}">
    @csrf
    @if($post) @method('PUT') @endif
    <label>Judul posisi</label><input name="title" value="{{ old('title', $post->title ?? '') }}" required>
    <div class="row">
      <div><label>Kota</label><input name="city" value="{{ old('city', $post->city ?? $company->city) }}" required></div>
      <div><label>Tipe</label>
        <select name="type">
          @foreach(['Full-time','Part-time','Kontrak','Magang','Freelance'] as $t)
            <option @selected(old('type', $post->type ?? 'Full-time') === $t)>{{ $t }}</option>
          @endforeach
        </select></div>
    </div>
    <div class="row">
      <div><label>Gaji minimum (Rp)</label><input type="number" name="salary_min" value="{{ old('salary_min', $post->salary_min ?? '') }}"></div>
      <div><label>Gaji maksimum (Rp)</label><input type="number" name="salary_max" value="{{ old('salary_max', $post->salary_max ?? '') }}"></div>
    </div>
    <div class="row">
      <div><label>Latitude (peta)</label><input name="lat" value="{{ old('lat', $post->lat ?? '') }}" placeholder="-6.2383"></div>
      <div><label>Longitude (peta)</label><input name="lng" value="{{ old('lng', $post->lng ?? '') }}" placeholder="106.9756"></div>
    </div>
    <p class="mu">Koordinat bisa diambil dari Google Maps: klik kanan pada lokasi, lalu klik angka koordinatnya untuk menyalin.</p>
    <label>Deskripsi</label><textarea name="description" rows="5">{{ old('description', $post->description ?? '') }}</textarea>
    <label>Skill yang dibutuhkan (pisahkan dengan koma, maks 15)</label>
    <input name="skills" value="{{ old('skills', $post ? implode(', ', $post->skills ?? []) : '') }}" placeholder="mis. Excel, Laravel, Public Speaking">
    <p class="mu">Dipakai untuk skor kecocokan di CV pelamar. Tulis nama skill yang sama dengan yang biasa dipakai pelamar.</p>
    <label>Pertanyaan screening (satu baris satu pertanyaan, maks {{ $plan->max_screening_questions }})</label>
    <textarea name="questions" rows="4">{{ old('questions', $post ? $post->questions->pluck('question')->implode("\n") : '') }}</textarea>
    <div class="acts"><button class="btn p">Simpan</button><a class="btn" href="{{ route('company.dashboard') }}">Batal</a></div>
  </form>
</div>
@endsection
