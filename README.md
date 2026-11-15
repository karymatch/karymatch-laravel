# KaryMatch — Laravel

Versi ini memakai antarmuka SPA KaryMatch dari HTML referensi sebagai halaman utama (`/`) dan tetap mempertahankan modul Laravel CRUD inti pada `/laravel`.

## Menjalankan di Windows

Persyaratan: PHP 8.3+, Composer, dan ekstensi PHP `pdo_sqlite`, `sqlite3`, `mbstring`, `dom`, `xml`, `xmlwriter`, `fileinfo`, `openssl`, `ctype`, `tokenizer`.

1. Ekstrak ZIP dan buka folder yang berisi file `artisan` di VS Code.
2. Buka Terminal VS Code, lalu jalankan:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan serve
   ```

3. Buka `http://127.0.0.1:8000` di Chrome. Biarkan terminal server tetap terbuka.
4. Untuk menghentikan server, fokus ke terminal lalu tekan `Ctrl+C`.

Jika `.env` sudah ada, jangan jalankan `Copy-Item` lagi; langsung lanjutkan `php artisan key:generate` bila APP_KEY masih kosong.

## Bagian aplikasi

- `/` — antarmuka KaryMatch dari HTML referensi, mencakup area publik, pencari kerja, perusahaan, admin, chat, interview, talent/jaringan, tiket/laporan, paket, dan peta.
- `/laravel` — halaman CRUD Laravel yang memakai model dan tabel relasional inti.
- `TEST-REPORT.md` — catatan cakupan dan hasil pemeriksaan.

## Catatan penting tentang mode demo

- Antarmuka SPA mempertahankan perilaku dan alur demo HTML. Sinkronisasi data fitur SPA kini memakai endpoint Laravel dan tabel `karymatch_sync_records`; password/hash password sengaja tidak dikirim ke tabel sinkronisasi.
- Akun/sesi SPA masih dikelola di sisi browser dan sebagian modul SPA disimpan sebagai payload JSON, bukan seluruhnya dinormalisasi ke tabel Laravel. Karena itu ini **mode demo lokal/presentasi**, bukan aplikasi produksi multi-pengguna.
- Jangan masukkan data pribadi sungguhan, dokumen identitas, atau kata sandi penting. Sebelum dipublikasikan ke internet, perlu migrasi autentikasi SPA ke Laravel Auth, otorisasi server-side per peran, penyimpanan file privat, dan tabel relasional khusus untuk chat/interview/tiket/audit/pembayaran.
- Email, QRIS/pembayaran sungguhan, dan integrasi eksternal memerlukan kredensial/provider resmi; halaman demo tidak membuktikan transaksi atau pengiriman sungguhan.
- Peta memakai layanan peta eksternal sehingga memerlukan koneksi internet dan izin lokasi dari browser untuk posisi pengguna.

## Pembaruan: AI, login lintas perangkat, aset lokal

1. **Fitur AI**: default memakai Gemini (ada paket gratis). Buat kunci di https://aistudio.google.com/apikey lalu isi `GEMINI_API_KEY` di `.env`. Untuk memakai Claude, set `AI_PROVIDER=anthropic` dan isi `ANTHROPIC_API_KEY`. Browser memanggil `POST /karymatch/ai` (Laravel), kunci tidak pernah sampai ke browser.
2. **Login lintas perangkat**: hash kata sandi (SHA-256) kembali ikut tersinkron seperti di HTML asli. Lihat catatan keamanan di bawah.
3. **Leaflet lokal**: jalankan sekali `powershell -ExecutionPolicy Bypass -File .\download-leaflet.ps1` (atau `bash download-leaflet.sh`) agar Leaflet diambil dari `public/vendor/leaflet`. Jika file lokal belum ada, otomatis jatuh balik ke CDN. Google Fonts dihapus karena Poppins sudah tertanam di halaman.
4. Ubin peta kini dari OpenStreetMap (tanpa API key; CartoDB sekarang mewajibkan kunci) dan tetap butuh internet. Untuk trafik besar/produksi, pakai penyedia ubin sendiri sesuai kebijakan OSM; tautan Google Maps, WhatsApp, dan Google Calendar hanyalah tautan keluar.

**Catatan keamanan**: endpoint `/karymatch/sync` tidak memakai login Laravel, sehingga siapa pun yang bisa membuka situs dapat membaca hash kata sandi. Hash-nya hanya SHA-256 ber-salt email, jadi mudah ditebak untuk kata sandi lemah. Pakai hanya untuk demo.

## Pemeriksaan

Jalankan `php artisan test` setelah ekstensi PHP yang dibutuhkan tersedia. Pemeriksaan sintaks PHP dan JavaScript juga dapat dilakukan tanpa menjalankan browser.
