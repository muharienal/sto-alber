# Catatan validasi paket

Pemeriksaan lokal yang berhasil:

- PDF asli diekstrak sebagai teks: 62 nomor STO, semuanya unik.
- Struktur nomor, tanggal, asal, tujuan, tabel barang dan total ditemukan pada seluruh 62 STO dengan pemeriksaan pola menggunakan Python pada hasil pdftotext.
- Baris pertama: 5520078169, Urea SUB @50 KG, qty 185, total 2.617.750.
- STO ketiga memiliki qty 172,400, diperlakukan sebagai desimal Indonesia pada kode PHP.
- Semua nama route yang dipakai halaman Blade tersedia pada routes/web.php.
- composer.json valid; tidak ada package.json atau referensi @vite.

Batas validasi:

PHP dan Composer tidak tersedia di lingkungan pembuat paket. Tidak dilakukan php -l, composer install, eksekusi Laravel, rendering Blade, pengujian HTTP, atau eksekusi Smalot. Pemeriksaan pola pada hasil pdftotext bukan pengganti tes parser PHP. Jalankan php vendor/bin/phpunit setelah instalasi. Tes parser PDF asli menuntut hasil 62 STO tanpa bagian yang gagal dibaca.

PDF fixture tests/Fixtures/STO.pdf berasal dari lampiran pengguna dan disertakan untuk pengujian lokal. Hapus fixture jika mendistribusikan kode tanpa dokumen bisnis tersebut.

## Pembaruan responsif

- Seluruh halaman Blade utama memiliki viewport responsif dan menggunakan stylesheet yang sama.
- CSS memakai breakpoint 1100px, 900px, 820px, 720px, 480px dan 380px; layar kecil menampilkan kartu dan modal layar penuh; mode cetak menyembunyikan navigasi dan modal.
- Tabel daftar, pratinjau dan detail memiliki data-label untuk setiap kolom non-empty; kartu HP menampilkan seluruh nilai.
- Route yang dirujuk halaman tetap cocok dengan route proyek; tidak ada migration atau perubahan data pada pembaruan UI.
- Halaman error 403, 404, 409, 410, 419, 429, 500, 503 memakai stylesheet global.
- Fixture HTML statis untuk mencoba CSS bukan hasil render Laravel, dan PHP/Composer tidak tersedia di lingkungan pembuat paket. Verifikasi di laptop dengan browser responsive mode pada lebar 320, 375, 390, 768, 1024, 1366 dan 1920px serta jalankan suite PHP sebelum penggunaan operasional.

- Desain ulang dan pratinjau modal: CSS dan JavaScript diperiksa di Chromium (desktop 1440px dan HP 390px) memakai fixture HTML statis berisi markup yang sama dengan Blade. JavaScript lolos `node --check`. Render Blade/Laravel dan `php artisan test` belum dijalankan karena PHP tidak tersedia di lingkungan pembuat paket; jalankan `php artisan test` di laptop.
- Deteksi Bongkar/Muat otomatis: pada tests/Fixtures/STO.pdf, hasil pdftotext (mode raw, mendekati urutan stream) menunjukkan 62 blok STO, masing-masing berisi tepat satu kalimat 'Mohon ...' setelah 'No. Po': 52 MUAT dan 10 BONGKAR (STO ke-12 = 5520078285 adalah BONGKAR pertama). Test PHP untuk ini sudah ditambahkan tetapi belum dijalankan (PHP tidak tersedia di lingkungan pembuat); jalankan `php artisan test`.
- Perubahan SOJ/MMJ, penghapusan status, modal detail, dan CRUD: seluruh berkas PHP (app, routes, database, tests, config) lolos pengurai PHP 8 (paket npm php-parser). View Blade diperiksa dengan kompiler mini (direktif diubah ke PHP lalu diurai), sehingga struktur if/foreach/for seimbang. CSS dan JS diuji di Chromium memakai fixture HTML statis: mode detail dengan peringatan, mode edit, tambah/hapus baris barang, hitung biaya (10,125 x 1.000,50 = 10.130,06, sama dengan hitungan server), Esc tidak menutup saat mengedit, batal edit mengembalikan isi awal, konfirmasi hapus, dan layar HP 390px. Belum dijalankan karena PHP/Composer tidak tersedia di lingkungan pembuat: `php artisan migrate` dan `php artisan test` (termasuk 11 test baru untuk CRUD, SOJ/MMJ, filter, dan CSV) perlu dijalankan di laptop.

## Pembaruan tema Cuci Alber dan redaksi

- `php vendor/bin/phpunit` dijalankan dengan PHP 8.3: 33 test lolos (207 assertion), termasuk parser pada PDF asli 62 STO dan 2 test baru (pesan validasi berbahasa Indonesia, pesan simpan impor).
- Test `test_search_and_completeness_filters` sebelumnya gagal karena flash `reopen` dari POST terbawa ke GET berikutnya (di browser flash itu terpakai oleh redirect). Test disesuaikan; perilaku aplikasi tidak berubah.
- Aplikasi dijalankan dengan `php artisan serve` (SQLite) dan diperiksa di Chromium pada lebar 1440px dan 390px: login, dashboard, Master STO, modal detail, modal ubah, pratinjau impor PDF asli, akun, dan halaman 404.

