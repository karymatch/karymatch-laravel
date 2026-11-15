@extends('layouts.app')
@section('title', 'CV & profil')
@section('content')
@php($pct = $cv->completeness())
<h2>CV &amp; profil</h2>

<div class="card noprint">
  <b>Kelengkapan {{ $pct }}%</b>
  <div class="bar"><i style="width:{{ $pct }}%"></i></div>
  <div class="mu">Isi seperti CV pada umumnya. Foto, usia, dan status pernikahan opsional dan tidak diminta.
    @unless($cv->readyToApply())<br><b class="wn">Untuk melamar wajib: ringkasan profil, nomor HP, dan minimal satu skill.</b>@endunless</div>
</div>

<form method="post" action="{{ route('cv.update') }}" class="noprint">
  @csrf @method('PUT')

  <div class="card f">
    <b>Data diri &amp; ringkasan</b>
    <div class="mu">Nama: {{ $user->name }} · Email: {{ $user->email }}</div>
    <label>Ringkasan profil
      <textarea name="summary" rows="4" maxlength="1000" placeholder="Contoh: Lulusan SMK Akuntansi yang teliti, terbiasa membuat laporan kas harian di Excel, dan siap bekerja dalam tim.">{{ old('summary', $cv->summary) }}</textarea></label>
    <div class="two">
      <label>Nomor HP / WhatsApp<input name="phone" type="tel" inputmode="tel" value="{{ old('phone', $cv->phone) }}" placeholder="081234567890"></label>
      <label>Domisili (kota)<input name="city" value="{{ old('city', $cv->city) }}" placeholder="Bekasi"></label>
    </div>
    <label>Tautan portofolio / LinkedIn (opsional)<input name="link" type="url" value="{{ old('link', $cv->link) }}" placeholder="https://"></label>
  </div>

  <div class="card f">
    <b>Keahlian (Skills)</b>
    <label>Tulis dengan kata-katamu sendiri, pisahkan dengan koma. Tingkat kemahiran boleh di dalam kurung.
      <textarea name="skills" rows="3" placeholder="Microsoft Excel (Mahir), Public Speaking, Desain Grafis (Menengah)">{{ old('skills', $cv->skillsText()) }}</textarea></label>
    <div class="mu">Nama skill dicocokkan dengan syarat lowongan untuk menghitung skor kecocokan.</div>
  </div>

  <div class="card f">
    <b>Pendidikan, pengalaman, dan lainnya</b>
    <div class="mu">Satu butir satu baris.</div>
    <label>Riwayat pendidikan<textarea name="education" rows="3" placeholder="SMK Negeri 1 Bekasi, Akuntansi (2021–2024)">{{ old('education', implode("\n", $cv->education ?? [])) }}</textarea></label>
    <label>Pengalaman kerja<textarea name="experience" rows="3" placeholder="Admin toko, Toko Maju Jaya (2024–sekarang)">{{ old('experience', implode("\n", $cv->experience ?? [])) }}</textarea></label>
    <label>Pengalaman organisasi &amp; proyek<textarea name="projects" rows="3">{{ old('projects', implode("\n", $cv->projects ?? [])) }}</textarea></label>
    <label>Sertifikasi &amp; pelatihan<textarea name="certificates" rows="3">{{ old('certificates', implode("\n", $cv->certificates ?? [])) }}</textarea></label>
    <label>Bahasa<textarea name="languages" rows="2" placeholder="Indonesia (ibu), Inggris (menengah)">{{ old('languages', implode("\n", $cv->languages ?? [])) }}</textarea></label>
  </div>

  <div class="card f">
    <b>Preferensi kerja</b>
    <div class="two">
      <label>Harapan gaji (juta rupiah per bulan)<input name="pref_salary" type="number" min="0" max="1000" value="{{ old('pref_salary', $cv->pref_salary) }}" placeholder="5"></label>
      <label>Lokasi yang diinginkan<input name="pref_location" value="{{ old('pref_location', $cv->pref_location) }}" placeholder="Bekasi, Jakarta, atau Remote"></label>
    </div>
    <label>Tipe pekerjaan
      <select name="pref_type">
        <option value="">(bebas)</option>
        @foreach(['Full-time','Part-time','Kontrak','Magang','Freelance','Remote'] as $t)<option @selected(old('pref_type', $cv->pref_type) === $t)>{{ $t }}</option>@endforeach
      </select></label>
  </div>

  <div class="fl" style="margin-bottom:14px"><button class="btn p">Simpan CV</button></div>
</form>

<div class="mu noprint">Pratinjau CV. Inilah yang terkirim ke perusahaan saat kamu melamar (disalin saat tombol Lamar ditekan).</div>
@include('seeker._cv_preview', ['data' => $cv->toArray(), 'name' => $user->name, 'email' => $user->email])
<div class="noprint"><button type="button" class="btn" onclick="window.print()">Unduh CV (cetak / simpan PDF)</button></div>
@endsection
