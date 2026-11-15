#!/usr/bin/env bash
# Jalankan SEKALI dari folder proyek: bash download-leaflet.sh  (butuh internet)
set -e
base="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4"
dest="$(dirname "$0")/public/vendor/leaflet"
mkdir -p "$dest/images"
for f in leaflet.min.js leaflet.min.css; do curl -fsSL "$base/$f" -o "$dest/$f"; done
for f in marker-icon.png marker-icon-2x.png marker-shadow.png layers.png layers-2x.png; do curl -fsSL "$base/images/$f" -o "$dest/images/$f"; done
echo "Selesai: Leaflet tersimpan di $dest"
