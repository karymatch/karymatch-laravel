# Jalankan SEKALI dari folder proyek (yang berisi file artisan), butuh internet:
#   powershell -ExecutionPolicy Bypass -File .\download-leaflet.ps1
# Mengunduh Leaflet 1.9.4 ke public/vendor/leaflet agar peta tidak bergantung CDN.
$base = "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4"
$dest = Join-Path $PSScriptRoot "public\vendor\leaflet"
New-Item -ItemType Directory -Force -Path "$dest\images" | Out-Null
foreach ($f in @("leaflet.min.js","leaflet.min.css")) {
  Invoke-WebRequest "$base/$f" -OutFile "$dest\$f"
}
foreach ($f in @("marker-icon.png","marker-icon-2x.png","marker-shadow.png","layers.png","layers-2x.png")) {
  Invoke-WebRequest "$base/images/$f" -OutFile "$dest\images\$f"
}
Write-Host "Selesai: Leaflet tersimpan di $dest"
