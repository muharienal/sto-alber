# STO Alber — Laravel PHP tanpa npm

Proyek lengkap Laravel 12, Blade, CSS lokal, SQLite atau MySQL, dan Smalot PDF Parser (PHP melalui Composer). Tidak menggunakan npm, Node.js, Vite, Bootstrap CDN, framework JavaScript, API AI, OCR, atau executable PDF tambahan pada server aplikasi. Composer diperlukan untuk memasang Laravel dan library PHP. Folder vendor tidak disertakan dalam ZIP.

## Instalasi

Persyaratan: PHP 8.2–8.5, Composer 2, ekstensi standar Laravel termasuk mbstring, openssl, fileinfo, DOM/XML, ctype, tokenizer, PDO, serta pdo_sqlite untuk SQLite (atau pdo_mysql untuk MySQL). Jalankan dari folder hasil ekstrak.

```bash
composer install
php -r "copy('.env.example', '.env');"
php artisan key:generate
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sto_alber CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Buka `.env`, isi `ADMIN_PASSWORD` dengan password pilihan Anda minimal 12 karakter. Username awal `admin`; email dan nama juga dapat diubah sebelum seeding. Tidak ada password bawaan yang ditanam dalam kode.

```dotenv
ADMIN_NAME=Superadmin
ADMIN_USERNAME=admin
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD="IsiPasswordKuatAndaDiSini"
```

Lalu:

```bash
php artisan migrate --seed
php artisan serve
```

Buka http://127.0.0.1:8000, login dengan username dan password yang diisi. Tidak perlu menjalankan perintah npm atau storage:link. Perintah `composer run dev` tidak digunakan.

Default paket ini menggunakan MariaDB/MySQL dengan nama database `sto_alber`. Jalankan layanan database Laragon terlebih dahulu. Sesuaikan username, password dan port `.env` sebelum migrate:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sto_alber
DB_USERNAME=root
DB_PASSWORD=""
```

Jika memakai SQLite, ubah DB_CONNECTION=sqlite dan DB_DATABASE menjadi path absolut file SQLite yang dibuat terlebih dahulu. Saat mengganti database setelah server berjalan, jalankan `php artisan config:clear`.

## Fitur dan alur

1. Login username/password, session Laravel, CSRF semua form, pembatasan percobaan login, logout, dan perubahan password.
2. Dashboard dari data database aktual: total STO, total nilai, jumlah STO dengan SOJ/MMJ kosong, diagram komposisi bongkar/muat dan kelengkapan SOJ/MMJ, tren impor tujuh hari, dan STO terbaru.
3. Master STO (CRUD lengkap): cari nomor/asal/tujuan/SOJ/MMJ, filter kegiatan dan kelengkapan SOJ/MMJ, pagination 20 STO. Tombol Detail membuka modal; tombol Tambah STO membuka form tambah manual; tombol Edit pada modal mengubah seluruh data dan rincian barang; tombol Hapus meminta konfirmasi.
4. Unggah satu file per proses. Satu PDF dapat berisi satu ataupun banyak STO; bisa mengunggah PDF lain setelah proses selesai. Maksimum 10 MB dan 200 halaman.
5. Jenis kegiatan dibaca otomatis per STO dari kalimat pembuka dokumen: `Mohon dibongkar barang berikut :` menjadi BONGKAR dan `Mohon dilaksanakan pemuatan barang berikut :` menjadi MUAT. Satu PDF boleh berisi keduanya. Bila kalimat tidak ditemukan atau memuat kedua kata, STO itu tidak disimpan dan muncul pada daftar peringatan pratinjau.
6. Sistem mengekstrak nomor, tanggal dokumen (bukan tanggal cetak), asal dan tujuan lengkap dengan alamat, nama barang, qty, satuan, tarif, biaya, total, dan halaman sumber. Barang disimpan dalam tabel tersendiri; mendukung lebih dari satu item per STO.
7. Pratinjau seluruh STO tanpa batas 50 baris. Duplikat ditandai terhadap database dan antar-STO dalam file. Data belum masuk Master STO hingga Simpan dipilih.
8. Simpan menggunakan transaksi database, nomor STO unik, lock impor, dan pemeriksaan ulang duplikat. Pengiriman ulang pratinjau yang sudah disimpan ditolak. Duplikat dilewati, tidak menimpa data lama.
9. SOJ dan MMJ diisi manual oleh admin pada modal detail (atau pada form Edit). Selama salah satunya kosong, modal menampilkan peringatan, tabel menandai "Kosong", dan Master STO serta Dashboard menampilkan jumlah STO yang belum lengkap. Admin juga dapat membuka PDF privat dan mengunduh laporan CSV (termasuk kolom SOJ dan MMJ). Tidak ada lagi status verifikasi maupun status pemakaian.
10. Pratinjau kedaluwarsa setelah 24 jam. Pembatalan menghapus PDF sementara; cleanup membersihkan pratinjau kedaluwarsa. PDF impor yang disimpan tetap dipertahankan untuk jejak dokumen, termasuk bila STO dihapus.

Semua akun autentikasi adalah administrator pada versi ini. Modul invoice, persetujuan berjenjang, Master Gudang, Master User, dan QR tidak termasuk scope login + dashboard + Master STO yang diminta; menu tersebut tidak ditampilkan sebagai tombol kosong. Total nilai dan tren impor dipakai sebagai statistik dashboard, bukan statistik tagihan yang belum memiliki data.

## Format PDF dan batas pembacaan

Parser ditujukan untuk struktur `STO.pdf` yang dilampirkan: `No. Po :`, `Tanggal :`, `Asal Barang :`, `Tujuan Barang :`, dan tabel `No. Nama Barang Qty Satuan Tarif Biaya ... Total`. Pemisahan berdasarkan nomor STO, bukan asumsi satu halaman = satu STO. Halaman sumber dipakai untuk membuka PDF pada posisi dokumen. PDF teks dengan susunan ekstraksi berbeda dapat memerlukan penyesuaian parser. Dukungan universal semua format PDF tidak dijanjikan.

Angka memakai format Indonesia: `172,400` menjadi `172.400` dalam database, `14.150` menjadi `14150.00`, `2.439.460` menjadi `2439460.00`. Nama barang tidak digabung dengan qty/tarif/total. Nilai harga disimpan sebagai decimal database. Jumlah biaya atau hasil qty × tarif yang berbeda ditampilkan sebagai peringatan untuk diperiksa admin, tidak diperbaiki diam-diam.

PDF gambar/scan tanpa teks ditolak dengan pesan. Tidak ada OCR, sesuai implementasi PHP saja. Jika ada STO tidak lengkap, bagian itu dilewati dan nomor/pesan kesalahan ditampilkan di pratinjau. Jika seluruh STO gagal, impor ditolak. Pratinjau baca saja; koreksi sumber lalu unggah ulang jika ditemukan kesalahan.

## Pengujian

```bash
php vendor/bin/phpunit
```

Disertakan pengujian parser pada PDF asli 62 STO, satu STO dengan banyak item, dua STO satu halaman, angka desimal, tanggal tidak valid, dan PDF tanpa teks. Pengujian HTTP meliputi login/logout, proteksi route, pratinjau tanpa menyimpan STO, duplikat, submit berulang, pemilik pratinjau, pratinjau kedaluwarsa, PDF sementara, tambah/ubah/hapus STO manual, perhitungan biaya di server, validasi dan nomor duplikat, pengisian SOJ/MMJ beserta peringatannya, filter kelengkapan, CSV, serta pemastian kolom status tidak ada lagi.

Lingkungan pembuat paket tidak memiliki PHP/Composer. Suite PHP, render Blade, dan eksekusi Smalot belum dijalankan di lingkungan tersebut. Struktur PDF contoh diperiksa dengan ekstraksi teks lokal: terdapat 62 nomor STO unik. Jalankan suite di atas pada server/lokal setelah Composer selesai sebelum digunakan untuk operasional. Lihat `VALIDASI.md` untuk catatan pemeriksaan.

## Server produksi

Arahkan document root ke `public/`, gunakan HTTPS, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` ke domain Anda dan `SESSION_SECURE_COOKIE=true`. `storage/` dan `bootstrap/cache/` harus writable oleh proses PHP. Lindungi `.env` dan backup database serta `storage/app/private/`. Jangan memindahkan PDF ke `public/`.

Atur `upload_max_filesize=10M` dan `post_max_size=12M` atau lebih besar dalam php.ini; sesuaikan batas body Nginx/Apache. Untuk file 200 halaman, alokasikan memory_limit 256M atau 512M dan max_execution_time yang sesuai kapasitas server. Validasi angka halaman dilakukan sesudah pembacaan PDF sehingga ukuran file tetap batas utama.

```bash
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

Bersihkan pratinjau manual dengan `php artisan sto:cleanup`, atau pasang cron Laravel (ganti path dengan path instalasi):

```cron
* * * * * cd /path/sto-alber && php artisan schedule:run >> /dev/null 2>&1
```

Lock file ditujukan untuk satu server aplikasi. Untuk beberapa node, ganti cache dengan shared lock store seperti Redis dan sesuaikan konfigurasi. Unique constraint database tetap menjaga nomor STO.

## Peta kode

- `routes/web.php`: semua route web.
- `app/Http/Controllers/`: autentikasi, dashboard, impor dan Master STO (CRUD).
- `app/Http/Requests/StoRequest.php`: validasi tambah/ubah STO.
- `app/Services/StoPdfParser.php`: parsing PDF dan konversi angka.
- `app/Models/`: User, Sto, StoItem, ImportBatch.
- `database/migrations/`: skema lengkap.
- `database/seeders/`: admin awal dari `.env`.
- `resources/views/`: seluruh halaman Blade.
- `public/css/app.css` dan `public/js/app.js`: seluruh tampilan dan interaksi modal, tanpa proses build.
- `tests/`: suite unit/feature dan PDF fixture.
- `FULL_CODE.md`: isi semua source teks dalam satu dokumen agar mudah dibaca/disalin.

Referensi API: https://laravel.com/docs/12.x dan https://github.com/smalot/pdfparser/blob/master/doc/Usage.md .

## Mengganti folder proyek yang sudah terpasang

Untuk instalasi sebelumnya, hentikan php artisan serve, ubah nama folder tamara-laravel menjadi sto-alber, lalu ubah APP_NAME="STO Alber" di .env. Biarkan APP_KEY dan konfigurasi database lama agar akun serta data tetap tersedia. Jalankan php artisan config:clear dan php artisan view:clear, lalu php artisan serve dari folder baru. Tidak perlu migrate:fresh atau membuat ulang database.

Jika ingin menerapkan tampilan baru pada instalasi lama, salin resources/views/, public/css/, serta config/app.php dari paket ini. Nama file migration lama dipertahankan untuk menjaga riwayat migrasi.

## Pembaruan tampilan modern dan pratinjau modal

Seluruh halaman (login, dashboard, akun, Master STO, detail STO, dan halaman error) didesain ulang dengan sistem desain baru di `public/css/app.css`: font Plus Jakarta Sans (dimuat dari Google Fonts, otomatis memakai font sistem bila offline), palet biru kobalt dan teal, ikon SVG inline (`resources/views/components/icon.blade.php`), tombol, badge, kartu, dan tabel yang konsisten. Skala `zoom: .9` yang lama dihapus; ukuran huruf dasar kini 14px.

**Pratinjau data kini berupa modal pop-up.** Setelah memilih PDF dan menekan *Pratinjau Data*, halaman Master STO terbuka dengan jendela pratinjau di atasnya (URL tetap `/impor/{batch}/preview`, view `sto/index.blade.php`; `sto/preview.blade.php` dihapus). Isi modal: ringkasan jumlah STO terbaca/baru/duplikat, peringatan bagian yang gagal dibaca, tabel hasil bacaan, serta tombol *Batalkan Impor* dan *Simpan N STO Baru*. Tombol X sama dengan *Batalkan Impor* dan tombol Esc tidak menutup modal, supaya hasil bacaan tidak hilang tanpa sengaja. Tombol simpan nonaktif bila semua baris duplikat.

Jenis kegiatan tidak lagi dipilih saat unggah (parser: `parse($path)`, deteksi di `StoPdfParser::activity()`). Pada PDF contoh 62 STO: 52 MUAT dan 10 BONGKAR. Perubahan lain: area unggah mendukung seret-dan-lepas dengan pemeriksaan tipe dan ukuran di browser (server tetap memvalidasi), tabel Master STO memakai satu baris per barang dengan sel dokumen digabung (rowspan) sehingga qty, satuan dan tarif sejajar, layar kecil menampilkan kartu, konfirmasi hapus STO memakai modal, tombol form menampilkan status memuat dan mencegah kirim ganda, dan halaman akun serta login dirapikan.

Berkas baru: `public/js/app.js` (JavaScript lokal tanpa library atau npm; halaman tetap bisa dipakai tanpa berkas ini kecuali bahwa modal tampil tanpa efek modal), `resources/views/partials/assets.blade.php`, `resources/views/components/icon.blade.php`. Perubahan PHP hanya pada `StoController` (aksi `index` dan `preview` memakai satu metode `listing()` yang sama). Tidak ada perubahan skema database dan rute.

## Perubahan: SOJ/MMJ, tanpa status, modal detail, dan CRUD

- **Status pemakaian dan verifikasi dihapus seluruhnya** (tampilan, rute, filter, CSV, dashboard, test, dan kolom database `used`, `verified_at`, `verified_by`).
- **Kolom baru `soj` dan `mmj`** pada tabel `stos` (teks maksimal 100 karakter, boleh kosong, diisi manual).
- **Detail STO berupa modal** pada halaman Master STO. Alamat lama `/sto/{id}` otomatis diteruskan ke `/sto?open={id}` yang membuka modal tersebut.
- **Tambah STO manual**: nomor unik (huruf, angka, titik, garis miring, strip), tanggal, jenis kegiatan, asal, tujuan, SOJ, MMJ, dan 1 sampai 100 baris barang. Biaya per baris dan total dihitung di server dari Qty × Tarif (bilangan bulat, tanpa selisih pembulatan desimal), jadi isian biaya tidak dipercaya dari browser. STO manual tidak punya PDF sumber, sehingga `import_batch_id` dan `source_page` kini boleh kosong.
- **Ubah dan hapus**: seluruh kolom dan rincian barang dapat diubah; hapus selalu meminta konfirmasi dan menghapus rincian barang (cascade). PDF impor tetap tersimpan.
- Kesalahan validasi tambah/ubah memakai error bag `sto` sehingga tidak tercampur dengan kesalahan unggah PDF, dan modal terkait terbuka kembali dengan isian terakhir.
- Rute baru: `POST /sto` (tambah), `PUT /sto/{sto}` (ubah), `PATCH /sto/{sto}/referensi` (isi SOJ/MMJ). Rute `verifikasi` dan `pemakaian` dihapus.
- Berkas baru: `app/Http/Requests/StoRequest.php`, `database/migrations/2026_10_09_000000_update_stos_remove_status_add_soj_mmj.php`, `resources/views/sto/_modal.blade.php`, `_form.blade.php`, `_item_row.blade.php`. `sto/show.blade.php` dihapus.

### Menerapkan perubahan pada instalasi yang sudah berjalan

**Backup database dulu** (mis. `mysqldump -u root sto_alber > backup.sql`), karena migrasi menghapus kolom status pemakaian dan verifikasi secara permanen. Hentikan server dengan Ctrl+C. Salin ke folder instalasi (misalnya `C:\laragon\www\sto-alber`): seluruh folder `app/`, `routes/`, `database/migrations/`, `public/css/app.css`, `public/js/app.js`, seluruh `resources/views/`, dan `tests/`. Hapus `resources/views/sto/show.blade.php` dan `resources/views/sto/preview.blade.php` bila masih ada. Pertahankan `.env`, `vendor/`, dan `storage/app/private/`.

```bash
cd /c/laragon/www/sto-alber
php artisan migrate
php artisan view:clear
php artisan test
php artisan serve
```

`php artisan migrate` menjalankan migrasi baru: menghapus kolom status, menambah `soj` dan `mmj`, dan membuat `import_batch_id`/`source_page` boleh kosong. Migrasi aman diulang dan juga aman pada instalasi baru. Data STO lama tetap ada; SOJ dan MMJ-nya kosong sampai diisi. Jika tampilan masih lama, tekan Ctrl+F5.

## Pembaruan: tema Cuci Alber dan redaksi ringkas

- **Tema** diselaraskan dengan Cuci Alber: topbar dan judul halaman navy gradasi dengan garis oranye, tombol aksi oranye, penanda amber, latar gradasi hangat. Semua warna diatur lewat variabel di awal `public/css/app.css` (`--navy`, `--primary`, dst.).
- **Redaksi** seluruh halaman, pesan sukses, pesan validasi, dan pesan parser dipersingkat dan diseragamkan (mis. "Asal/Tujuan", "Kegiatan", format tanggal `dd/mm/yyyy`). Kolom "Biaya" pada daftar STO yang sebenarnya berisi total STO kini berlabel "Total".
- Pesan validasi login, ubah password, dan unggah PDF kini berbahasa Indonesia (sebelumnya bahasa Inggris bawaan Laravel).
- Footer seragam lewat `resources/views/partials/copyright.blade.php`, termasuk di halaman login dan error.
- Tombol **Unduh CSV** dipindah ke Master STO; bagian "Menu utama" di Dashboard dihapus karena mengulang menu navigasi.
- View usang `sto/show.blade.php` dan `sto/preview.blade.php` dihapus. Jika masih ada di instalasi Anda, hapus juga.
- Tidak ada perubahan database, route, atau migrasi. Setelah menyalin file: `php artisan view:clear`, lalu Ctrl+F5 di browser.

## Pembaruan: warna lebih lembut dan logo STO Alber

- Palet dilunakkan: navy lebih terang dan semi-transparan (`--navy-glass`), oranye lebih kalem (`--primary: #e58b52`), garis aksen oranye transparan (`--accent-line`), bayangan dan latar modal lebih tipis.
- Logo baru: peti barang (stok) dengan panah (transport) di kotak navy. Komponen Blade `<x-logo :size="40" />` di `resources/views/components/logo.blade.php`, dipakai di topbar, login, dan halaman error. Tulisan merek "STO **Alber**" dengan "Alber" oranye.
- Favicon: `public/favicon.svg`, `public/favicon.ico`, `public/apple-touch-icon.png`, `public/logo-512.png`. Jika ikon tab belum berubah, tutup tab lalu buka lagi (browser menyimpan cache favicon).

