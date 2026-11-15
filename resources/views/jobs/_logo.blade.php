@php
  // Warna dan inisial turunan dari nama perusahaan, seperti logo di prototipe
  $pal = [['#202f49','#93a2b7'],['#0f5a6b','#5cc4d4'],['#344c75','#d3ccbd'],['#7a1f2b','#e58a96'],['#1f5a3a','#7fd1a0'],['#31476d','#afbaca'],['#8a5a12','#e8bf6a'],['#202f49','#bbc4d1']];
  [$c1, $c2] = $pal[crc32($name) % count($pal)];
  $words = array_values(array_filter(preg_split('/\s+/', trim($name)), fn ($w) => ! preg_match('/^(pt\.?|cv\.?|ud\.?|tbk\.?)$/i', $w)));
  $ini = mb_strtoupper(mb_substr($words[0] ?? '?', 0, 1).(isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
  $sz = $size ?? 48;
@endphp
<span class="lg" style="width:{{ $sz }}px;height:{{ $sz }}px;background:linear-gradient(135deg,{{ $c1 }},{{ $c2 }})">{{ $ini }}</span>
