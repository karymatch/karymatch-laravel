{{-- $data: array isi CV (dari model atau salinan lamaran), $name, $email --}}
@php
  $skillText = collect($data['skills'] ?? [])->map(fn ($lv, $n) => $lv && $lv !== '✓' ? "{$n} ({$lv})" : $n)->implode(', ');
  $head = collect([$email, $data['phone'] ?? null, $data['city'] ?? null])->filter()->implode(' · ');
  $block = [
    'Riwayat Pendidikan'               => $data['education'] ?? [],
    'Pengalaman Kerja'                 => $data['experience'] ?? [],
    'Pengalaman Organisasi & Proyek'   => $data['projects'] ?? [],
    'Sertifikasi & Pelatihan'          => $data['certificates'] ?? [],
  ];
@endphp
<div class="card cvp">
  <h2 style="margin:6px 0 2px">{{ $name }}</h2>
  <div class="mu">{{ $head }}</div>
  @if(!empty($data['link']))<div class="mu"><a href="{{ $data['link'] }}" target="_blank" rel="noopener noreferrer">{{ $data['link'] }}</a></div>@endif
  @if(!empty($data['summary']))<h4>Ringkasan Profil</h4><p>{{ $data['summary'] }}</p>@endif
  @foreach($block as $title => $items)
    @if($items)<h4>{{ $title }}</h4><ul>@foreach($items as $i)<li>{{ $i }}</li>@endforeach</ul>@endif
  @endforeach
  @if($skillText)<h4>Keahlian (Skills)</h4><p>{{ $skillText }}</p>@endif
  @if(!empty($data['languages']))<h4>Bahasa</h4><ul>@foreach($data['languages'] as $i)<li>{{ $i }}</li>@endforeach</ul>@endif
  @if(!empty($data['pref_salary']) || !empty($data['pref_location']) || !empty($data['pref_type']))
    <h4>Preferensi Kerja</h4>
    <p>{{ collect([
        !empty($data['pref_salary']) ? 'Harapan gaji: Rp '.$data['pref_salary'].' juta per bulan' : null,
        $data['pref_location'] ?? null,
        $data['pref_type'] ?? null,
      ])->filter()->implode(' · ') }}</p>
  @endif
</div>
