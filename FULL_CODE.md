# Full Code STO Alber Laravel — tema Cuci Alber, redaksi ringkas

Seluruh source teks proyek. Panduan instalasi dan pembaruan ada di README.md.


## .env.example

```text
APP_NAME="STO Alber"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sto_alber
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=single
ADMIN_NAME=Superadmin
ADMIN_USERNAME=admin
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=

```


## .gitignore

```text
/vendor
/.env
/database/*.sqlite*
/.phpunit.cache

```


## README.md

```text
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

```


## VALIDASI.md

```text
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

```


## app/Http/Controllers/AuthController.php

```text
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
 public function login(){return view('auth.login');}
 public function authenticate(Request $r){
  $data=$r->validate(['username'=>'required|string|max:255','password'=>'required|string|max:255'],['required'=>':attribute wajib diisi.','max'=>':attribute terlalu panjang.'],['username'=>'Username','password'=>'Password']);
  $key='login:'.hash('sha256',strtolower($data['username']).'|'.$r->ip());
  if(RateLimiter::tooManyAttempts($key,5)) throw ValidationException::withMessages(['username'=>'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
  if(!Auth::attempt($data)){RateLimiter::hit($key,60);throw ValidationException::withMessages(['username'=>'Username atau password salah.']);}
  RateLimiter::clear($key);$r->session()->regenerate();return redirect()->intended(route('dashboard'));
 }
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
 public function password(Request $r){
  $data=$r->validate(
   ['current_password'=>['required','current_password'],'password'=>['required','confirmed',\Illuminate\Validation\Rules\Password::min(12)]],
   ['required'=>':attribute wajib diisi.','current_password'=>'Password saat ini salah.','confirmed'=>'Ulangan password baru tidak sama.','password.min'=>'Password baru minimal :min karakter.'],
   ['current_password'=>'Password saat ini','password'=>'Password baru']
  );
  $r->user()->update(['password'=>$data['password'],'remember_token'=>null]);$r->session()->regenerate();return back()->with('success','Password berhasil diubah.');
 }
}
```


## app/Http/Controllers/Controller.php

```text
<?php
namespace App\Http\Controllers;
abstract class Controller {}

```


## app/Http/Controllers/DashboardController.php

```text
<?php

namespace App\Http\Controllers;

use App\Models\Sto;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $total = Sto::count();
        $bongkar = Sto::where('activity', 'BONGKAR')->count();
        $incomplete = Sto::incomplete()->count();
        $sojEmpty = Sto::where(fn ($w) => $w->whereNull('soj')->orWhere('soj', ''))->count();
        $mmjEmpty = Sto::where(fn ($w) => $w->whereNull('mmj')->orWhere('mmj', ''))->count();

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $days[] = ['label' => $day->format('d M'), 'count' => Sto::whereDate('created_at', $day->toDateString())->count()];
        }

        return view('dashboard', compact('total', 'bongkar', 'incomplete', 'sojEmpty', 'mmjEmpty', 'days') + [
            'amount' => Sto::sum('total'),
            'latest' => Sto::latest()->orderByDesc('id')->limit(5)->get(),
        ]);
    }
}

```


## app/Http/Controllers/StoController.php

```text
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoRequest;
use App\Models\{ImportBatch, Sto, StoItem};
use App\Services\StoPdfParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Storage};
use Illuminate\Validation\ValidationException;

class StoController extends Controller
{
    /* ------------------------------------------------------------------
     |  Daftar (Read)
     * ------------------------------------------------------------------ */

    private function filtered(Request $r): Builder
    {
        $query = Sto::with(['items', 'batch']);

        if ($r->filled('q')) {
            $s = substr((string) $r->q, 0, 100);
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%$s%")
                    ->orWhere('origin', 'like', "%$s%")
                    ->orWhere('destination', 'like', "%$s%")
                    ->orWhere('soj', 'like', "%$s%")
                    ->orWhere('mmj', 'like', "%$s%");
            });
        }
        if (in_array($r->activity, ['BONGKAR', 'MUAT'], true)) {
            $query->where('activity', $r->activity);
        }
        if ($r->completeness === 'incomplete') {
            $query->incomplete();
        } elseif ($r->completeness === 'complete') {
            $query->complete();
        }

        return $query;
    }

    /** Data untuk halaman Master STO (juga dipakai saat pratinjau impor tampil sebagai modal). */
    private function indexData(Request $r): array
    {
        $stos = $this->filtered($r)
            ->latest()->orderByDesc('id')
            ->paginate(20)
            ->withPath(route('sto.index'))
            ->appends($r->except('open'));

        // Setiap STO pada halaman ini punya modal detail. STO yang diminta lewat
        // ?open=ID atau dibuka ulang setelah simpan ditambahkan bila tidak ada di halaman ini.
        $dialogStos = $stos->getCollection()->values();
        $reopen = (string) session('reopen');
        $openId = $r->query('open') ?? (str_starts_with($reopen, 'sto-') ? substr($reopen, 4) : null);
        if ($openId !== null && ctype_digit((string) $openId) && ! $dialogStos->contains('id', (int) $openId)) {
            $extra = Sto::with(['items', 'batch'])->find((int) $openId);
            if ($extra) {
                $dialogStos->push($extra);
            }
        }

        return [
            'stos' => $stos,
            'dialogStos' => $dialogStos,
            'incompleteCount' => Sto::incomplete()->count(),
        ];
    }

    public function index(Request $r)
    {
        return view('sto.index', $this->indexData($r));
    }

    /** Detail kini berupa modal pada halaman Master STO; alamat lama diteruskan ke sana. */
    public function show(Sto $sto)
    {
        return redirect()->route('sto.index', ['open' => $sto->id]);
    }

    /* ------------------------------------------------------------------
     |  Impor PDF (pratinjau, simpan, batal)
     * ------------------------------------------------------------------ */

    public function upload(Request $r, StoPdfParser $parser)
    {
        $r->validate(['pdf' => 'required|file|mimes:pdf|extensions:pdf|max:10240'], [
            'pdf.required' => 'Pilih file PDF terlebih dahulu.',
            'pdf.file' => 'File gagal diunggah. Coba lagi.',
            'pdf.uploaded' => 'File gagal diunggah. Pastikan ukurannya maks. 10 MB.',
            'pdf.mimes' => 'File harus berformat PDF.',
            'pdf.extensions' => 'File harus berformat PDF.',
            'pdf.max' => 'Ukuran PDF maks. 10 MB.',
        ]);
        $path = $r->file('pdf')->store('sto', 'local');
        try {
            $result = $parser->parse(Storage::path($path));
        } catch (\Throwable $e) {
            Storage::delete($path);
            report($e);
            throw ValidationException::withMessages(['pdf' => $e instanceof \RuntimeException ? $e->getMessage() : 'PDF tidak bisa dibuka. Pastikan file tidak rusak atau terkunci.']);
        }
        try {
            $batch = ImportBatch::create([
                'user_id' => $r->user()->id,
                'original_name' => $r->file('pdf')->getClientOriginalName(),
                'path' => $path,
                'sha256' => hash_file('sha256', Storage::path($path)),
                'payload' => $result['rows'],
                'warnings' => $result['warnings'],
                'expires_at' => now()->addHours(24),
            ]);
        } catch (\Throwable $e) {
            Storage::delete($path);
            throw $e;
        }

        return redirect()->route('sto.preview', $batch);
    }

    private function own(Request $r, ImportBatch $batch): void
    {
        abort_unless((int) $batch->user_id === (int) $r->user()->id, 403);
    }

    public function preview(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);
        abort_unless($batch->status === 'preview' && $batch->expires_at->isFuture(), 410, 'Pratinjau tidak berlaku.');

        $seen = [];
        $existing = Sto::whereIn('number', array_column($batch->payload, 'number'))->pluck('number')->all();
        $rows = array_map(function ($row) use (&$seen, $existing) {
            $row['duplicate'] = isset($seen[$row['number']]) || in_array($row['number'], $existing, true);
            $seen[$row['number']] = true;

            return $row;
        }, $batch->payload);

        // Pratinjau tampil sebagai modal di atas halaman Master STO.
        return view('sto.index', $this->indexData($r) + ['batch' => $batch, 'rows' => $rows]);
    }

    public function commit(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);

        return Cache::lock('sto-import-global', 120)->block(5, function () use ($batch) {
            $counts = DB::transaction(function () use ($batch) {
                $b = ImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
                abort_unless($b->status === 'preview' && $b->expires_at->isFuture(), 410, 'Pratinjau tidak berlaku.');
                $saved = 0;
                $skip = 0;
                foreach ($b->payload as $row) {
                    if (Sto::where('number', $row['number'])->exists()) {
                        $skip++;
                        continue;
                    }
                    $items = $row['items'];
                    unset($row['items'], $row['notes']);
                    $sto = Sto::create($row + ['import_batch_id' => $b->id]);
                    $sto->items()->createMany($items);
                    $saved++;
                }
                $b->update(['status' => 'committed', 'payload' => []]);

                return [$saved, $skip];
            });

            $message = "{$counts[0]} STO disimpan.".($counts[1] ? " {$counts[1]} duplikat dilewati." : '');

            return redirect()->route('sto.index')->with('success', $message);
        });
    }

    public function cancel(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);
        DB::transaction(function () use ($batch) {
            $b = ImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            abort_unless($b->status === 'preview', 409);
            Storage::delete($b->path);
            $b->delete();
        });

        return redirect()->route('sto.index')->with('success', 'Impor dibatalkan.');
    }

    /* ------------------------------------------------------------------
     |  Tambah, ubah, hapus (Create, Update, Delete)
     * ------------------------------------------------------------------ */

    public function store(StoRequest $request)
    {
        $sto = DB::transaction(fn () => $this->persist(new Sto(), $request->validated()));

        return redirect()->route('sto.index')->with([
            'success' => "STO {$sto->number} ditambahkan.",
            'reopen' => 'sto-'.$sto->id,
        ]);
    }

    public function update(StoRequest $request, Sto $sto)
    {
        $saved = DB::transaction(function () use ($request, $sto) {
            $locked = Sto::whereKey($sto->id)->lockForUpdate()->firstOrFail();

            return $this->persist($locked, $request->validated());
        });

        return back()->with([
            'success' => "STO {$saved->number} diperbarui.",
            'reopen' => 'sto-'.$saved->id,
        ]);
    }

    /** Pengisian cepat SOJ dan MMJ dari modal detail. */
    public function references(Request $r, Sto $sto)
    {
        $data = $r->validateWithBag('sto', [
            'soj' => ['nullable', 'string', 'max:100'],
            'mmj' => ['nullable', 'string', 'max:100'],
        ], StoRequest::indonesianMessages(), ['soj' => 'SOJ', 'mmj' => 'MMJ']);

        $sto->update([
            'soj' => $this->nullable($data['soj'] ?? null),
            'mmj' => $this->nullable($data['mmj'] ?? null),
        ]);

        return back()->with([
            'success' => "SOJ/MMJ STO {$sto->number} disimpan.",
            'reopen' => 'sto-'.$sto->id,
        ]);
    }

    public function destroy(Sto $sto)
    {
        $number = $sto->number;
        DB::transaction(function () use ($sto) {
            Sto::whereKey($sto->id)->lockForUpdate()->firstOrFail()->delete();
        });

        return redirect()->route('sto.index')->with('success', "STO {$number} dihapus.");
    }

    /** Menyimpan STO beserta rincian barangnya; biaya dan total dihitung di server. */
    private function persist(Sto $sto, array $data): Sto
    {
        $lines = [];
        $total = 0;
        foreach (array_values($data['items']) as $i => $item) {
            // Hitung dengan bilangan bulat (qty per 0,001; tarif per sen) agar tidak ada selisih pembulatan desimal.
            $milli = (int) round(((float) $item['quantity']) * 1000);
            $cents = (int) round(((float) $item['rate']) * 100);
            $amount = intdiv($milli * $cents + 500, 1000);
            $total += $amount;
            $lines[] = [
                'line' => $i + 1,
                'name' => trim($item['name']),
                'quantity' => $this->fixed($milli, 3),
                'unit' => mb_strtoupper(trim($item['unit'])),
                'rate' => $this->fixed($cents, 2),
                'amount' => $this->fixed($amount, 2),
            ];
        }

        $sto->fill([
            'number' => trim($data['number']),
            'document_date' => $data['document_date'],
            'activity' => $data['activity'],
            'origin' => trim($data['origin']),
            'destination' => trim($data['destination']),
            'soj' => $this->nullable($data['soj'] ?? null),
            'mmj' => $this->nullable($data['mmj'] ?? null),
            'total' => $this->fixed($total, 2),
        ])->save();

        StoItem::where('sto_id', $sto->id)->delete();
        $sto->items()->createMany($lines);

        return $sto;
    }

    private function fixed(int $value, int $decimals): string
    {
        $base = 10 ** $decimals;

        return sprintf('%d.%0'.$decimals.'d', intdiv($value, $base), $value % $base);
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /* ------------------------------------------------------------------
     |  PDF dan laporan
     * ------------------------------------------------------------------ */

    public function pdf(Sto $sto)
    {
        abort_unless($sto->batch && Storage::exists($sto->batch->path), 404);

        return response()->file(Storage::path($sto->batch->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sto-'.$sto->number.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['Nomor STO', 'Tanggal', 'Kegiatan', 'Asal', 'Tujuan', 'SOJ', 'MMJ', 'Nama Barang', 'Qty', 'Satuan', 'Tarif', 'Biaya', 'Total STO'], ';');
            Sto::with('items')->orderBy('id')->chunkById(100, function ($stos) use ($f) {
                foreach ($stos as $s) {
                    foreach ($s->items as $i) {
                        $row = [$s->number, $s->document_date->format('Y-m-d'), $s->activity, $s->origin, $s->destination, $s->soj, $s->mmj, $i->name, $i->quantity, $i->unit, $i->rate, $i->amount, $s->total];
                        // Cegah formula spreadsheet (CSV injection).
                        $row = array_map(fn ($v) => preg_match('/^[\s]*[=+@\-]/u', (string) $v) ? "'".$v : $v, $row);
                        fputcsv($f, $row, ';');
                    }
                }
            });
            fclose($f);
        }, 'laporan-sto-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
```


## app/Http/Requests/StoRequest.php

```text
<?php

namespace App\Http\Requests;

use App\Models\Sto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah dan ubah STO secara manual.
 * Kesalahan disimpan pada error bag "sto" agar tidak tercampur dengan
 * kesalahan unggah PDF pada halaman yang sama.
 */
class StoRequest extends FormRequest
{
    protected $errorBag = 'sto';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        $this->merge([
            'items' => is_array($items) ? array_values(array_filter($items, 'is_array')) : [],
        ]);
    }

    public function rules(): array
    {
        $current = $this->route('sto');

        return [
            'number' => [
                'required', 'string', 'max:40',
                'regex:/^[0-9A-Za-z][0-9A-Za-z._\/-]*$/',
                Rule::unique('stos', 'number')->ignore($current instanceof Sto ? $current->id : null),
            ],
            'document_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'activity' => ['required', 'in:BONGKAR,MUAT'],
            'origin' => ['required', 'string', 'max:1000'],
            'destination' => ['required', 'string', 'max:1000'],
            'soj' => ['nullable', 'string', 'max:100'],
            'mmj' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:1000000'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'number' => 'Nomor STO',
            'document_date' => 'Tanggal',
            'activity' => 'Kegiatan',
            'origin' => 'Asal',
            'destination' => 'Tujuan',
            'soj' => 'SOJ',
            'mmj' => 'MMJ',
            'items' => 'Barang',
            'items.*.name' => 'Nama barang',
            'items.*.quantity' => 'Qty',
            'items.*.unit' => 'Satuan',
            'items.*.rate' => 'Tarif',
        ];
    }

    public function messages(): array
    {
        return self::indonesianMessages();
    }

    /** Pesan validasi berbahasa Indonesia (aplikasi belum memiliki berkas lang). */
    public static function indonesianMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
            'decimal' => ':attribute maksimal :decimal angka di belakang koma.',
            'in' => ':attribute tidak valid.',
            'regex' => ':attribute hanya boleh berisi huruf, angka, titik, garis miring, garis bawah, dan strip.',
            'unique' => ':attribute sudah dipakai STO lain.',
            'date_format' => ':attribute tidak valid.',
            'after_or_equal' => ':attribute tidak boleh sebelum :date.',
            'before_or_equal' => ':attribute tidak boleh setelah :date.',
            'array' => ':attribute tidak valid.',
            'max' => [
                'string' => ':attribute maksimal :max karakter.',
                'numeric' => ':attribute maksimal :max.',
                'array' => 'Maksimal :max baris barang.',
            ],
            'min' => [
                'numeric' => ':attribute minimal :min.',
                'array' => 'Isi minimal :min baris barang.',
            ],
            'gt' => [
                'numeric' => ':attribute harus lebih besar dari :value.',
            ],
        ];
    }
}
```


## app/Models/ImportBatch.php

```text
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ImportBatch extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['payload'=>'array','warnings'=>'array','expires_at'=>'datetime']; }
 public function stos() { return $this->hasMany(Sto::class); }
}

```


## app/Models/Sto.php

```text
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['document_date' => 'date', 'total' => 'decimal:2'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoItem::class)->orderBy('line');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    /** Nama kolom referensi (SOJ/MMJ) yang masih kosong, untuk peringatan di tampilan. */
    public function missingReferences(): array
    {
        return array_keys(array_filter(
            ['SOJ' => $this->soj, 'MMJ' => $this->mmj],
            fn ($value) => blank($value)
        ));
    }

    /** STO yang SOJ atau MMJ-nya masih kosong. */
    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where(fn (Builder $w) => $w
            ->whereNull('soj')->orWhere('soj', '')
            ->orWhereNull('mmj')->orWhere('mmj', ''));
    }

    /** STO yang SOJ dan MMJ-nya sudah terisi. */
    public function scopeComplete(Builder $query): Builder
    {
        return $query
            ->whereNotNull('soj')->where('soj', '!=', '')
            ->whereNotNull('mmj')->where('mmj', '!=', '');
    }
}

```


## app/Models/StoItem.php

```text
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StoItem extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['quantity'=>'decimal:3','rate'=>'decimal:2','amount'=>'decimal:2']; }
 public function sto() { return $this->belongsTo(Sto::class); }
}

```


## app/Models/User.php

```text
<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable {
 protected $fillable=['name','username','email','password'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['password'=>'hashed']; }
}

```


## app/Providers/AppServiceProvider.php

```text
<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
 public function register(): void {}
 public function boot(): void { \Illuminate\Pagination\Paginator::defaultView('components.pagination'); }
}

```


## app/Services/StoPdfParser.php

```text
<?php
namespace App\Services;
use Smalot\PdfParser\Parser;
use RuntimeException;
class StoPdfParser {
 public function parse(string $path): array {
  $pdf=(new Parser())->parseFile($path);
  $pages=$pdf->getPages();
  if(count($pages)>200) throw new RuntimeException('PDF melebihi 200 halaman.');
  $text='';
  foreach($pages as $i=>$page) $text.="\n[[PAGE:".($i+1)."]]\n".$page->getText();
  return $this->parseText($text);
 }
 public function parseText(string $text): array {
  $text=str_replace(["\r\n","\r","\xc2\xa0"],["\n","\n"," "],$text);
  preg_match_all('/No\.?\s*(?:Po|PO|STO)\s*:\s*([0-9]{6,40})/iu',$text,$matches,PREG_OFFSET_CAPTURE);
  if(!$matches[0]) throw new RuntimeException('Nomor STO tidak ditemukan. Gunakan PDF teks (bukan scan) dengan format STO standar.');
  $rows=[];$warnings=[];
  foreach($matches[0] as $i=>$hit){
   $offset=$hit[1];$number=$matches[1][$i][0];
   $block=substr($text,$offset,($matches[0][$i+1][1]??strlen($text))-$offset);
   preg_match_all('/\[\[PAGE:(\d+)\]\]/',substr($text,0,$offset),$pm);
   $page=(int)(end($pm[1])?:1);
   try {
    $activity=$this->activity($block);
    if(!preg_match('/(?<!Cetak\s)\bTanggal\s*:\s*(\d{2})[.\/-](\d{2})[.\/-](\d{4})/i',$block,$d) || !checkdate((int)$d[2],(int)$d[1],(int)$d[3])) throw new RuntimeException('Tanggal dokumen tidak valid.');
    $origin=$this->location($block,'Asal','Tujuan\s+Barang');
    $destination=$this->location($block,'Tujuan','Moda');
    if(!preg_match('/No\.?\s+Nama\s+Barang\s+Qty\s+Satuan\s+Tarif\s+Biaya(.*?)\bTotal\s*:?\s*([\d.,]+)/is',$block,$table)) throw new RuntimeException('Tabel barang atau total tidak terbaca.');
    preg_match_all('/(?:^|\s)(\d+)\s+(.+?)\s+([\d.,]+)\s+([A-Z]{1,20})\s+([\d.,]+)\s+([\d.,]+)(?=\s|$)/s',trim($table[1]),$items,PREG_SET_ORDER);
    if(!$items) throw new RuntimeException('Baris barang tidak terbaca.');
    $detail=[];$sum=0;$notes=[];
    foreach($items as $item){
     $qty=$this->decimal($item[3],3);$rate=$this->decimal($item[5],2);$amount=$this->decimal($item[6],2);
     if((float)$qty<=0) throw new RuntimeException('Qty harus lebih besar dari nol.');
     if(abs(round((float)$qty*(float)$rate)-(float)$amount)>1) $notes[]='Baris '.$item[1].': Qty × Tarif tidak sama dengan Biaya.';
     $detail[]=['line'=>(int)$item[1],'name'=>preg_replace('/\s+/',' ',trim($item[2])),'quantity'=>$qty,'unit'=>$item[4],'rate'=>$rate,'amount'=>$amount];$sum+=(float)$amount;
    }
    if(count(array_unique(array_column($detail,'line')))!==count($detail)) throw new RuntimeException('Nomor baris barang ganda.');
    $total=$this->decimal($table[2],2);
    if(abs($sum-(float)$total)>1) $notes[]='Total tidak sama dengan jumlah Biaya.';
    $rows[]=['number'=>$number,'document_date'=>"{$d[3]}-{$d[2]}-{$d[1]}",'activity'=>$activity,'origin'=>$origin,'destination'=>$destination,'total'=>$total,'source_page'=>$page,'items'=>$detail,'notes'=>array_values(array_unique($notes))];
   }catch(RuntimeException $e){$warnings[]="STO $number (hal. $page): ".$e->getMessage();}
  }
  if(!$rows) throw new RuntimeException(implode(' ',array_slice($warnings,0,5)));
  return ['rows'=>$rows,'warnings'=>$warnings];
 }
 /**
  * Jenis kegiatan dibaca dari kalimat pembuka tiap STO, bukan dari pilihan pengguna:
  *   "Mohon dibongkar barang berikut :"            => BONGKAR
  *   "Mohon dilaksanakan pemuatan barang berikut :" => MUAT
  * Bila kalimat tidak ditemukan, bagian kepala dokumen (sebelum "Asal Barang") diperiksa.
  * Jika hasilnya tidak ada atau ambigu (memuat kedua kata), STO ditolak agar tidak salah simpan.
  */
 private function activity(string $block): string {
  $text=trim(preg_replace('/\s+/u',' ',preg_replace('/\[\[PAGE:\d+\]\]/','',$block)));
  if(preg_match('/\bMohon\b(.{0,120}?)\bberikut\b/iu',$text,$m)) $scope=$m[1];
  elseif(preg_match('/^(.*?)\bAsal\s+Barang\b/iu',$text,$h)) $scope=$h[1];
  else $scope='';
  $bongkar=(bool)preg_match('/\b\w*bongkar\w*\b/iu',$scope);
  $muat=(bool)preg_match('/\b\w*muat\w*\b/iu',$scope);
  if($bongkar===$muat) throw new RuntimeException($bongkar ? 'Kegiatan ambigu: memuat kata bongkar dan muat sekaligus.' : 'Kegiatan (bongkar/muat) tidak ditemukan.');
  return $bongkar ? 'BONGKAR' : 'MUAT';
 }
 private function location(string $block,string $label,string $next): string {
  if(!preg_match('/(?<!Pengelola\s)\b'.$label.'\s+Barang\s*:(.*?)\b'.$next.'\s*:/is',$block,$m)) throw new RuntimeException("$label barang tidak terbaca.");
  $value=preg_replace('/Pengelola\s+Barang\s+(?:Asal|Tujuan)\s*:[^\n]*/i','',$m[1]);
  $value=preg_replace('/\[\[PAGE:\d+\]\]/','',$value);
  $value=trim(preg_replace('/[ \t]+/',' ',$value));
  if($value==='') throw new RuntimeException("$label barang kosong.");
  return $value;
 }
 public function decimal(string $value,int $scale): string {
  // Format Indonesia: titik ribuan, koma desimal. Tidak mengubah 172,400 menjadi 172400.
  if(!preg_match('/^(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{1,'.$scale.'})?$/',$value)) throw new RuntimeException('Format angka tidak valid: '.$value);
  $normalized=str_replace(',','.',str_replace('.','',$value));
  [$whole,$fraction]=array_pad(explode('.',$normalized,2),2,'');
  if(strlen(ltrim($whole,'0'))>18-$scale) throw new RuntimeException('Angka melebihi kapasitas kolom.');
  return $whole.'.'.str_pad($fraction,$scale,'0');
 }
}
```


## artisan

```text
#!/usr/bin/env php
<?php
use Illuminate\Foundation\Application;
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';
exit($app->handleCommand(new Symfony\Component\Console\Input\ArgvInput));

```


## bootstrap/app.php

```text
<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
 ->withMiddleware(function (Middleware $middleware) { $middleware->redirectGuestsTo('/login'); })
 ->withExceptions(function (Exceptions $exceptions) {})->create();

```


## bootstrap/cache/.gitignore

```text
*
!.gitignore

```


## bootstrap/providers.php

```text
<?php
return [App\Providers\AppServiceProvider::class];

```


## composer.json

```text
{
  "name": "sto-alber/app",
  "type": "project",
  "description": "STO Alber Master STO tanpa npm",
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "smalot/pdfparser": "^2.12"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.5",
    "mockery/mockery": "^1.6",
    "fakerphp/faker": "^1.23"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Database\\Seeders\\": "database/seeders/",
      "Database\\Factories\\": "database/factories/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Tests\\": "tests/"
    }
  },
  "scripts": {
    "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
      "@php artisan package:discover --ansi"
    ],
    "test": "@php vendor/bin/phpunit"
  },
  "config": {
    "sort-packages": true,
    "optimize-autoloader": true
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}

```


## config/app.php

```text
<?php
return ['name'=>env('APP_NAME','STO Alber'),'env'=>env('APP_ENV','production'),'debug'=>(bool)env('APP_DEBUG',false),'url'=>env('APP_URL','http://localhost'),'timezone'=>'Asia/Jakarta','locale'=>'id','fallback_locale'=>'en','faker_locale'=>'id_ID','key'=>env('APP_KEY'),'cipher'=>'AES-256-CBC','previous_keys'=>array_filter(explode(',',env('APP_PREVIOUS_KEYS',''))),'maintenance'=>['driver'=>'file']];

```


## config/auth.php

```text
<?php
return ['defaults'=>['guard'=>'web','passwords'=>'users'],'guards'=>['web'=>['driver'=>'session','provider'=>'users']],'providers'=>['users'=>['driver'=>'eloquent','model'=>App\Models\User::class]],'passwords'=>['users'=>['provider'=>'users','table'=>'password_reset_tokens','expire'=>60,'throttle'=>60]],'password_timeout'=>10800];

```


## config/cache.php

```text
<?php
return ['default'=>env('CACHE_STORE','file'),'stores'=>['file'=>['driver'=>'file','path'=>storage_path('framework/cache/data'),'lock_path'=>storage_path('framework/cache/data')],'array'=>['driver'=>'array','serialize'=>false]],'prefix'=>'sto_alber_'];

```


## config/database.php

```text
<?php
return ['default'=>env('DB_CONNECTION','sqlite'),'connections'=>[
'sqlite'=>['driver'=>'sqlite','database'=>env('DB_DATABASE',database_path('database.sqlite')),'prefix'=>'','foreign_key_constraints'=>true,'busy_timeout'=>5000,'journal_mode'=>'WAL'],
'mysql'=>['driver'=>'mysql','host'=>env('DB_HOST','127.0.0.1'),'port'=>env('DB_PORT','3306'),'database'=>env('DB_DATABASE','sto_alber'),'username'=>env('DB_USERNAME','root'),'password'=>env('DB_PASSWORD',''),'unix_socket'=>'','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true]],'migrations'=>['table'=>'migrations','update_date_on_publish'=>true]];

```


## config/filesystems.php

```text
<?php
return ['default'=>'local','disks'=>['local'=>['driver'=>'local','root'=>storage_path('app/private'),'throw'=>true]]];

```


## config/logging.php

```text
<?php
return ['default'=>'single','channels'=>['single'=>['driver'=>'single','path'=>storage_path('logs/laravel.log'),'level'=>'debug'],'null'=>['driver'=>'monolog','handler'=>Monolog\Handler\NullHandler::class]]];

```


## config/session.php

```text
<?php
return ['driver'=>env('SESSION_DRIVER','file'),'lifetime'=>(int)env('SESSION_LIFETIME',120),'expire_on_close'=>false,'encrypt'=>false,'files'=>storage_path('framework/sessions'),'connection'=>null,'table'=>'sessions','store'=>null,'lottery'=>[2,100],'cookie'=>'sto_alber_session','path'=>'/','domain'=>env('SESSION_DOMAIN'),'secure'=>env('SESSION_SECURE_COOKIE'),'http_only'=>true,'same_site'=>'lax','partitioned'=>false];

```


## config/view.php

```text
<?php
return ['paths'=>[resource_path('views')],'compiled'=>storage_path('framework/views')];

```


## database/migrations/2026_01_01_000000_create_tamara_tables.php

```text
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('username')->unique();$t->string('email')->unique();$t->string('password');$t->rememberToken();$t->timestamps();});
  Schema::create('import_batches',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->string('original_name');$t->string('path');$t->string('sha256',64);$t->json('payload');$t->json('warnings');$t->string('status')->default('preview')->index();$t->timestamp('expires_at');$t->timestamps();});
  Schema::create('stos',function(Blueprint $t){$t->id();$t->string('number',40)->unique();$t->date('document_date')->index();$t->string('activity',10);$t->text('origin');$t->text('destination');$t->string('soj',100)->nullable();$t->string('mmj',100)->nullable();$t->decimal('total',18,2);$t->foreignId('import_batch_id')->nullable()->constrained()->restrictOnDelete();$t->unsignedInteger('source_page')->nullable();$t->timestamps();});
  Schema::create('sto_items',function(Blueprint $t){$t->id();$t->foreignId('sto_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('line');$t->text('name');$t->decimal('quantity',18,3);$t->string('unit',20);$t->decimal('rate',18,2);$t->decimal('amount',18,2);$t->timestamps();$t->unique(['sto_id','line']);});
 }
 public function down(): void {foreach(['sto_items','stos','import_batches','users'] as $table) Schema::dropIfExists($table);}
};

```


## database/migrations/2026_10_09_000000_update_stos_remove_status_add_soj_mmj.php

```text
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Untuk database yang sudah berjalan:
 *  - menghapus status pemakaian dan verifikasi (used, verified_at, verified_by);
 *  - menambah kolom SOJ dan MMJ (diisi manual oleh admin);
 *  - membuat import_batch_id dan source_page boleh kosong, karena STO kini
 *    juga dapat ditambahkan manual tanpa PDF.
 * Semua langkah diperiksa dulu (idempotent), sehingga aman dijalankan pada
 * instalasi baru yang skema awalnya sudah final.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('stos', 'verified_by') && Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('stos', function (Blueprint $table) {
                $table->dropForeign(['verified_by']);
            });
        }

        $obsolete = array_values(array_filter(
            ['used', 'verified_at', 'verified_by'],
            fn (string $column) => Schema::hasColumn('stos', $column)
        ));
        if ($obsolete !== []) {
            Schema::table('stos', function (Blueprint $table) use ($obsolete) {
                $table->dropColumn($obsolete);
            });
        }

        foreach (['soj', 'mmj'] as $column) {
            if (! Schema::hasColumn('stos', $column)) {
                Schema::table('stos', function (Blueprint $table) use ($column) {
                    $table->string($column, 100)->nullable()->after('destination');
                });
            }
        }

        $columns = collect(Schema::getColumns('stos'))->keyBy('name');
        if (! ($columns['import_batch_id']['nullable'] ?? true)) {
            Schema::table('stos', function (Blueprint $table) {
                $table->unsignedBigInteger('import_batch_id')->nullable()->change();
            });
        }
        if (! ($columns['source_page']['nullable'] ?? true)) {
            Schema::table('stos', function (Blueprint $table) {
                $table->unsignedInteger('source_page')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['soj', 'mmj'] as $column) {
            if (Schema::hasColumn('stos', $column)) {
                Schema::table('stos', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
        if (! Schema::hasColumn('stos', 'used')) {
            Schema::table('stos', function (Blueprint $table) {
                $table->boolean('used')->default(false);
            });
        }
        if (! Schema::hasColumn('stos', 'verified_at')) {
            Schema::table('stos', function (Blueprint $table) {
                $table->timestamp('verified_at')->nullable();
            });
        }
        if (! Schema::hasColumn('stos', 'verified_by')) {
            Schema::table('stos', function (Blueprint $table) {
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
        // import_batch_id dan source_page tetap boleh kosong (data manual mungkin sudah ada).
    }
};

```


## database/seeders/DatabaseSeeder.php

```text
<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  $password=env('ADMIN_PASSWORD');
  if (!$password || strlen($password)<12) throw new \RuntimeException('Isi ADMIN_PASSWORD minimal 12 karakter di .env sebelum seeding.');
  User::firstOrCreate(['username'=>env('ADMIN_USERNAME','admin')],['name'=>env('ADMIN_NAME','Superadmin'),'email'=>env('ADMIN_EMAIL','admin@example.com'),'password'=>$password]);
 }
}

```


## phpunit.xml

```text
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true" cacheDirectory=".phpunit.cache"><testsuites><testsuite name="STO Alber"><directory>tests</directory></testsuite></testsuites><php><env name="APP_ENV" value="testing"/><env name="APP_KEY" value="base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA="/><env name="DB_CONNECTION" value="sqlite"/><env name="DB_DATABASE" value=":memory:"/><env name="CACHE_STORE" value="array"/><env name="SESSION_DRIVER" value="array"/><env name="LOG_CHANNEL" value="null"/></php></phpunit>

```


## public/.htaccess

```text
<IfModule mod_rewrite.c>
Options -MultiViews -Indexes
RewriteEngine On
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
</IfModule>

```


## public/css/app.css

```text
/* ==========================================================================
   STO Alber — design system
   Palet diselaraskan dengan Cuci Alber: navy (identitas), oranye (aksi),
   amber (penanda), latar gradasi hangat.
   Font : Plus Jakarta Sans (fallback ke font sistem bila offline).
   ========================================================================== */

:root {
  --bg: #f1eff2;
  --surface: #ffffff;
  --ink: #0f1c2e;
  --ink-2: #44546a;
  --ink-3: #74839a;
  --line: #e2e8f0;
  --line-soft: #eef2f7;

  /* identitas: navy (header Cuci Alber) */
  --navy: #2b3a6b;
  --navy-2: #4a5d9c;
  --navy-soft: #edf0f8;
  --navy-glass: linear-gradient(105deg, rgba(43, 58, 107, .93) 0%, rgba(74, 93, 156, .88) 100%);
  /* aksi: oranye (tombol Lanjut Cuci Alber) */
  --primary: #e58b52;
  --primary-d: #cf7440;
  --primary-soft: #fdf1e9;
  --accent-line: rgba(229, 139, 82, .6);
  --teal: #2f9a8b;
  --teal-soft: #e2f4f1;
  --green: #2f8a66;
  --green-soft: #e3f4ec;
  --amber: #9a6a12;
  --amber-soft: #fff7e2;
  --rose: #bd2d4b;
  --rose-soft: #fde7ec;
  --slate-soft: #eaeef4;

  --shadow-sm: 0 1px 2px rgba(15, 28, 46, .05);
  --shadow-md: 0 14px 34px -18px rgba(43, 58, 107, .22);
  --shadow-lg: 0 40px 90px -28px rgba(30, 40, 80, .38);

  --r-sm: 8px;
  --r-md: 12px;
  --r-lg: 18px;
  --r-xl: 22px;

  --font: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --topbar-h: 66px;
}

/* ---------- Reset & dasar ---------- */
*, *::before, *::after { box-sizing: border-box; }
html { background: var(--bg); -webkit-text-size-adjust: 100%; scroll-padding-top: calc(var(--topbar-h) + 16px); }
body {
  margin: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(115deg, #f8eee8 0%, #f4f2f4 45%, #e9ecf4 100%);
  color: var(--ink);
  font: 400 14px/1.6 var(--font);
  -webkit-font-smoothing: antialiased;
}
body:has(dialog[open]) { overflow: hidden; }
h1, h2, h3, p, ul, ol, dl, dd, figure { margin: 0; }
h1 { font-size: 28px; line-height: 1.2; font-weight: 800; letter-spacing: -.02em; }
h2 { font-size: 18px; line-height: 1.3; font-weight: 700; letter-spacing: -.01em; }
h3 { font-size: 15px; font-weight: 700; }
p { color: var(--ink-2); }
a { color: var(--navy-2); text-decoration: none; }
a:hover { color: var(--primary-d); }
button, input, select { font: inherit; color: inherit; }
button { cursor: pointer; }
ul, ol { padding: 0; list-style: none; }
svg { flex: none; }
.icon { display: inline-block; vertical-align: middle; }
:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
.topbar :focus-visible, .page-head :focus-visible, .auth-panel :focus-visible { outline-color: #ffd28a; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
.skip-link { position: absolute; left: 12px; top: -80px; z-index: 100; background: var(--surface); padding: 10px 16px; border-radius: var(--r-sm); box-shadow: var(--shadow-md); font-weight: 600; }
.skip-link:focus { top: 12px; }
.num { font-variant-numeric: tabular-nums; }

/* ---------- Layout ---------- */
.container { width: 100%; max-width: 1400px; margin-inline: auto; padding-inline: 28px; }
.page { flex: 1; padding-top: 32px; padding-bottom: 40px; }
.page > * + * { margin-top: 24px; }
.page-head {
  display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
  padding: 26px 28px; border-radius: var(--r-xl); color: #fff;
  background: var(--navy-glass);
  border-bottom: 3px solid var(--accent-line); box-shadow: 0 16px 36px -24px rgba(43, 58, 107, .55);
}
.page-head h1 { color: #fff; }
.page-head .lead { margin-top: 6px; max-width: 62ch; color: rgba(255, 255, 255, .82); }
.page-head .btn-primary { box-shadow: 0 8px 18px -12px rgba(0, 0, 0, .35); }
.section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.section-head p { margin-top: 2px; }
.footer { padding-block: 22px 28px; text-align: center; color: var(--ink-3); font-size: 12.5px; }

/* ---------- Topbar ---------- */
.topbar {
  position: sticky; top: 0; z-index: 40;
  background: var(--navy-glass);
  backdrop-filter: saturate(1.3) blur(12px);
  -webkit-backdrop-filter: saturate(1.3) blur(12px);
  border-bottom: 2px solid var(--accent-line);
  box-shadow: 0 8px 24px -18px rgba(30, 40, 80, .5);
}
.topbar-in { height: var(--topbar-h); display: flex; align-items: center; gap: 28px; }
.brand { display: inline-flex; align-items: center; gap: 12px; color: var(--ink); font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
.brand:hover { color: var(--ink); }
.topbar .brand, .topbar .brand:hover { color: #fff; }
.brand-logo { display: block; flex: none; border-radius: 11px; box-shadow: 0 6px 16px -8px rgba(30, 40, 80, .55); }
.topbar .brand-logo { box-shadow: 0 0 0 1px rgba(255, 255, 255, .22), 0 6px 16px -8px rgba(0, 0, 0, .4); }
.brand-name b { font-weight: 800; color: var(--primary-d); }
.topbar .brand-name b { color: #f6b88c; }
.mainnav { display: flex; align-items: center; gap: 4px; margin-right: auto; }
.navlink { white-space: nowrap; display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 10px; color: rgba(255, 255, 255, .74); font-weight: 600; transition: background .15s, color .15s; }
.navlink:hover { background: rgba(255, 255, 255, .1); color: #fff; }
.navlink.is-active { background: rgba(255, 255, 255, .14); color: #fff; box-shadow: inset 0 -2px 0 var(--accent-line); }
.usermenu { position: relative; }
.usermenu summary { list-style: none; display: inline-flex; align-items: center; gap: 10px; height: 42px; padding: 0 12px 0 5px; border: 1px solid rgba(255, 255, 255, .22); border-radius: 999px; background: rgba(255, 255, 255, .08); color: #fff; cursor: pointer; font-weight: 600; transition: border-color .15s, box-shadow .15s; }
.usermenu summary::-webkit-details-marker { display: none; }
.usermenu summary:hover { border-color: rgba(255, 255, 255, .45); }
.usermenu[open] summary { border-color: var(--accent-line); box-shadow: 0 0 0 3px rgba(229, 139, 82, .22); }
.usermenu summary .chev { color: rgba(255, 255, 255, .7); transition: transform .15s; }
.usermenu[open] summary .chev { transform: rotate(180deg); }
.avatar { display: grid; place-items: center; width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #e99a68, #f2b98f); color: #fff; font-size: 13px; font-weight: 800; }
.avatar-lg { width: 64px; height: 64px; font-size: 24px; }
.menu-pop { position: absolute; right: 0; top: calc(100% + 8px); min-width: 220px; padding: 6px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-md); box-shadow: var(--shadow-md); z-index: 50; }
.menu-pop .who { padding: 10px 12px 8px; border-bottom: 1px solid var(--line-soft); margin-bottom: 6px; }
.menu-pop .who strong { display: block; color: var(--ink); }
.menu-pop .who span { font-size: 12.5px; color: var(--ink-3); }
.menu-pop a, .menu-pop button { display: flex; width: 100%; align-items: center; gap: 10px; padding: 9px 12px; border: 0; background: none; border-radius: 8px; color: var(--ink-2); font-weight: 600; text-align: left; }
.menu-pop a:hover, .menu-pop button:hover { background: var(--slate-soft); color: var(--ink); }
.menu-pop .is-danger { color: var(--rose); }
.menu-pop .is-danger:hover { background: var(--rose-soft); color: var(--rose); }
.menu-pop form { margin: 0; }

/* ---------- Tombol ---------- */
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  min-height: 42px; padding: 0 18px; border: 1px solid transparent; border-radius: 10px;
  background: var(--primary); color: #fff; font-weight: 650; line-height: 1.2; text-align: center; white-space: nowrap;
  transition: background .15s, border-color .15s, box-shadow .15s, transform .05s;
}
.btn:hover { background: var(--primary-d); color: #fff; }
.btn:active { transform: translateY(1px); }
.btn:disabled, .btn[aria-disabled="true"] { opacity: .55; cursor: not-allowed; transform: none; }
.btn-primary { box-shadow: 0 8px 18px -12px rgba(229, 139, 82, .7); }
.btn-secondary { background: var(--surface); color: var(--ink); border-color: var(--line); box-shadow: var(--shadow-sm); }
.btn-secondary:hover { background: #fbfbfd; color: var(--navy-2); border-color: #c7cee2; }
.btn-navy { background: var(--navy-2); }
.btn-navy:hover { background: var(--navy); }
.btn-ghost { background: transparent; color: var(--ink-2); }
.btn-ghost:hover { background: var(--slate-soft); color: var(--ink); }
.btn-danger { background: var(--rose); }
.btn-danger:hover { background: #a02039; }
.btn-danger-soft { background: var(--rose-soft); color: var(--rose); }
.btn-danger-soft:hover { background: #fbd5de; color: #a02039; }
.btn-sm { min-height: 34px; padding: 0 12px; font-size: 13px; border-radius: 8px; }
.btn-lg { min-height: 48px; padding: 0 24px; font-size: 15px; border-radius: 12px; }
.btn-block { width: 100%; }
.icon-btn { display: inline-grid; place-items: center; width: 38px; height: 38px; border: 0; border-radius: 10px; background: transparent; color: var(--ink-3); transition: background .15s, color .15s; }
.icon-btn:hover { background: var(--slate-soft); color: var(--ink); }
.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255, 255, 255, .4); border-top-color: #fff; animation: spin .7s linear infinite; }
.btn-secondary .spinner, .btn-ghost .spinner { border-color: rgba(15, 28, 46, .2); border-top-color: var(--ink); }
@keyframes spin { to { transform: rotate(360deg); } }

/* ---------- Kartu ---------- */
.card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); padding: 24px; box-shadow: var(--shadow-sm); min-width: 0; }
.card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
.card-head p { margin-top: 2px; font-size: 13px; }
.card-title { display: flex; align-items: center; gap: 10px; }
.chip-icon { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 11px; background: var(--tone-soft, var(--navy-soft)); color: var(--tone, var(--navy-2)); flex: none; }
.tone-blue { --tone: var(--navy-2); --tone-soft: var(--navy-soft); }
.tone-orange { --tone: var(--primary-d); --tone-soft: var(--primary-soft); }
.tone-teal { --tone: var(--teal); --tone-soft: var(--teal-soft); }
.tone-green { --tone: var(--green); --tone-soft: var(--green-soft); }
.tone-amber { --tone: var(--amber); --tone-soft: var(--amber-soft); }
.tone-rose { --tone: var(--rose); --tone-soft: var(--rose-soft); }

/* ---------- Form ---------- */
.field { display: block; }
.field-label { display: block; margin-bottom: 7px; color: var(--ink); font-weight: 650; font-size: 13px; padding: 0; }
.field + .field { margin-top: 16px; }
.hint { color: var(--ink-3); font-size: 12.5px; margin-top: 6px; }
.input, .select {
  width: 100%; height: 44px; padding: 0 14px; border: 1px solid var(--line); border-radius: 10px;
  background: var(--surface); color: var(--ink); transition: border-color .15s, box-shadow .15s;
}
.input::placeholder { color: #9aa7ba; }
.input:hover, .select:hover { border-color: #cbd6e6; }
.input:focus, .select:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.select {
  appearance: none; -webkit-appearance: none; padding-right: 38px; cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2374839a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 12px center;
}
.input-icon { position: relative; }
.input-icon > .icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none; }
.input-icon .input { padding-left: 42px; }
.input-icon .reveal { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); }
.input-icon.has-reveal .input { padding-right: 46px; }
.reveal .eye-off { display: none; }
.reveal.is-on .eye-on { display: none; }
.reveal.is-on .eye-off { display: inline-block; }

fieldset { border: 0; margin: 0; padding: 0; min-width: 0; }

/* ---------- Dropzone ---------- */
.upload-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr); gap: 28px; align-items: stretch; }
.dropzone {
  position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
  min-height: 280px; padding: 32px 24px; text-align: center; cursor: pointer;
  border: 2px dashed #cdd2e2; border-radius: var(--r-lg);
  background: linear-gradient(180deg, #fcfcfe 0%, #f4f5fa 100%);
  transition: border-color .15s, background .15s, box-shadow .15s;
}
.dropzone:hover { border-color: var(--primary); background: #fff8f2; }
.dropzone:focus-within { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-soft); }
.dropzone.is-drag { border-color: var(--primary); border-style: solid; background: var(--primary-soft); }
.dz-icon { display: grid; place-items: center; width: 64px; height: 64px; margin-bottom: 8px; border-radius: 18px; color: #fff; background: linear-gradient(135deg, var(--navy-2), #7083bd); box-shadow: 0 14px 28px -14px rgba(43, 58, 107, .55); transition: transform .2s; }
.dropzone:hover .dz-icon, .dropzone.is-drag .dz-icon { transform: translateY(-3px); }
.dz-title { font-size: 17px; font-weight: 750; color: var(--ink); max-width: 100%; overflow-wrap: anywhere; }
.dz-hint { color: var(--ink-3); font-size: 13px; }
.dz-btn { margin-top: 14px; }
.dropzone.has-file { border-style: solid; border-color: #9fd9c8; background: linear-gradient(180deg, #fbfffd, #effaf6); }
.dropzone.has-file .dz-icon { background: linear-gradient(135deg, var(--green), var(--teal)); }
.dropzone.has-error { border-color: #efb0bf; background: #fff8fa; }
.dropzone.has-error .dz-icon { background: linear-gradient(135deg, var(--rose), #e0617c); }
.dropzone.has-error .dz-title { color: var(--rose); }
.upload-side { display: flex; flex-direction: column; gap: 22px; }
.detect { display: flex; gap: 14px; align-items: flex-start; padding: 16px; border: 1px solid #cfeae3; border-radius: var(--r-md); background: linear-gradient(180deg, #f6fcfa, #ecf8f4); }
.detect strong { display: block; color: var(--ink); font-weight: 700; }
.detect p { margin-top: 4px; font-size: 13px; }
.detect-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.steps { display: grid; gap: 14px; counter-reset: step; }
.steps li { display: flex; gap: 12px; align-items: flex-start; color: var(--ink-2); }
.steps li::before { counter-increment: step; content: counter(step); flex: none; display: grid; place-items: center; width: 24px; height: 24px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); font-size: 12px; font-weight: 800; margin-top: 1px; }
.steps strong { color: var(--ink); font-weight: 650; }
.upload-side .btn { margin-top: auto; }

/* ---------- Filter ---------- */
.filters { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }
.filters .input-icon { flex: 1 1 260px; }
.filters .select { width: auto; min-width: 170px; }
.count-pill { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; margin-left: 8px; border-radius: 999px; background: var(--navy-soft); color: var(--navy-2); font-size: 12px; font-weight: 700; vertical-align: middle; }

/* ---------- Tabel ---------- */
.table-card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
.table-scroll { overflow-x: auto; overscroll-behavior-x: contain; }
.table-scroll:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: left; }
.data-table th { position: sticky; top: 0; padding: 13px 12px; background: #f5f6fa; color: var(--ink-3); font-size: 12.5px; font-weight: 700; line-height: 1.35; vertical-align: bottom; border-bottom: 1px solid var(--line); }
.data-table td { padding: 14px 12px; color: var(--ink-2); border-bottom: 1px solid var(--line-soft); vertical-align: top; }
.data-table tbody:last-of-type tr:last-child td, .data-table tbody:last-child tr:last-child td { border-bottom: 0; }
.data-table tbody.group:hover td { background: #fbf9f7; }
.data-table tbody.group tr:not(:last-child) td.item { border-bottom: 1px dashed var(--line); }
.data-table .ta-r { text-align: right; }
.data-table td.item, .data-table td.num { font-variant-numeric: tabular-nums; }
.data-table .col-no { width: 52px; color: var(--ink-3); }
.data-table .sto-no { font-weight: 750; color: var(--ink); white-space: nowrap; }
.data-table a.sto-no:hover { color: var(--primary-d); }
.data-table .place { min-width: 130px; max-width: 210px; white-space: pre-line; overflow-wrap: anywhere; }
.data-table .name { min-width: 140px; color: var(--ink); font-weight: 550; }
.data-table .total { font-weight: 750; color: var(--ink); white-space: nowrap; font-variant-numeric: tabular-nums; }
.data-table .muted { color: var(--ink-3); }
.row-actions { display: flex; flex-direction: column; align-items: stretch; gap: 6px; }
.data-table tfoot th, .data-table tfoot td { position: static; background: #f7f9fc; color: var(--ink); font-size: 14px; border-top: 1px solid var(--line); border-bottom: 0; padding: 14px; }
.data-table tr.is-dup td { background: #fffaf0; }
.data-table.compact td, .data-table.compact th { padding: 11px 12px; }
.line-items { min-width: 230px; }
.line-items .li + .li { margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--line); }
.line-items .li strong { display: block; color: var(--ink); font-weight: 650; }
.line-items .li span { font-size: 12.5px; color: var(--ink-3); font-variant-numeric: tabular-nums; }
.route-cell { min-width: 220px; overflow-wrap: anywhere; }
.addr { display: block; white-space: pre-line; }
.route-cell .to { margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--line); }
.route-cell small { display: block; color: var(--ink-3); font-size: 11.5px; font-weight: 600; margin-bottom: 1px; }
.notes { margin-top: 8px; font-size: 12.5px; color: var(--amber); }
.mobile-only { display: none; }

.empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 56px 24px; text-align: center; }
.empty .chip-icon { width: 56px; height: 56px; border-radius: 16px; margin-bottom: 8px; background: var(--slate-soft); color: var(--ink-3); }
.empty h3 { font-size: 16px; color: var(--ink); }
.empty p { max-width: 42ch; }

/* ---------- Kartu STO (mobile) ---------- */
.sto-cards { display: none; gap: 14px; }
.sto-card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); padding: 16px; box-shadow: var(--shadow-sm); }
.sto-card .top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; }
.sto-card .sto-no { font-size: 16px; font-weight: 800; color: var(--ink); overflow-wrap: anywhere; }
.sto-card .date { color: var(--ink-3); font-size: 12.5px; white-space: nowrap; }
.sto-card .badges { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0 12px; }
.sto-card .route { display: grid; gap: 10px; padding: 12px; border-radius: var(--r-md); background: #f7f9fc; overflow-wrap: anywhere; }
.sto-card .route small { display: block; color: var(--ink-3); font-weight: 600; font-size: 12px; }
.sto-card .route p { color: var(--ink); white-space: pre-line; }
.sto-card .items { margin-top: 12px; }
.sto-card .items li { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed var(--line); }
.sto-card .items li:last-child { border-bottom: 0; }
.sto-card .items b { color: var(--ink); font-weight: 600; }
.sto-card .items span { color: var(--ink-3); text-align: right; font-size: 12.5px; font-variant-numeric: tabular-nums; }
.sto-card .sum { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-top: 1px solid var(--line); margin-top: 4px; }
.sto-card .sum strong { font-size: 16px; color: var(--ink); font-variant-numeric: tabular-nums; }
.sto-card .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

/* ---------- Badge ---------- */
.badge { display: inline-flex; align-items: center; gap: 6px; height: 24px; padding: 0 10px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; background: var(--slate-soft); color: var(--ink-2); }
.badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-green { background: var(--green-soft); color: var(--green); }
.badge-amber { background: var(--amber-soft); color: var(--amber); }
.badge-blue { background: var(--navy-soft); color: var(--navy-2); }
.badge-teal { background: var(--teal-soft); color: #0b7d6c; }
.badge-rose { background: var(--rose-soft); color: var(--rose); }
.badge-gray { background: var(--slate-soft); color: var(--ink-2); }

/* ---------- Alert & toast ---------- */
.alert { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border-radius: var(--r-md); border: 1px solid transparent; }
.alert > .icon { margin-top: 2px; }
.alert p, .alert ul { color: inherit; }
.alert ul { margin-top: 4px; padding-left: 18px; list-style: disc; }
.alert-info { background: var(--navy-soft); border-color: #d3d9ec; color: #25366f; }
.alert-danger { background: #fdeff2; border-color: #f4cfd8; color: #8f2a40; }
.alert-warn { background: var(--amber-soft); border-color: #f3e2b6; color: #7d560e; }
.alert-success { background: var(--green-soft); border-color: #bfe8d3; color: #0b5e41; }
.toast-stack { position: fixed; top: calc(var(--topbar-h) + 14px); right: 20px; z-index: 60; display: grid; gap: 10px; width: min(380px, calc(100vw - 32px)); }
.toast { display: flex; align-items: center; gap: 12px; padding: 12px 8px 12px 16px; background: rgba(43, 58, 107, .96); border-left: 4px solid var(--accent-line); color: #fff; border-radius: var(--r-md); box-shadow: var(--shadow-md); animation: toast-in .25s ease-out; }
.toast p { flex: 1; color: #fff; }
.toast > .icon { color: #5fe0b4; }
.toast .icon-btn { color: #aebbd0; }
.toast .icon-btn:hover { background: rgba(255, 255, 255, .1); color: #fff; }
.toast.is-leaving { opacity: 0; transform: translateY(-6px); transition: opacity .2s, transform .2s; }
@keyframes toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }

/* ---------- Pagination ---------- */
.pager { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 16px; }
.pager-info { color: var(--ink-3); font-size: 13px; }
.pager-links { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
.page-link { display: inline-grid; place-items: center; min-width: 38px; height: 38px; padding: 0 10px; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); color: var(--ink-2); font-weight: 650; font-size: 13px; }
.page-link:hover { border-color: #f0c3a2; color: var(--primary-d); }
.page-link.is-current { background: var(--primary); border-color: var(--primary); color: #fff; }
.page-link.is-disabled { opacity: .45; pointer-events: none; }
.page-link.is-wide { display: inline-flex; gap: 6px; padding: 0 12px; }

/* ---------- Modal (dialog) ---------- */
.modal {
  display: none; flex-direction: column; padding: 0; border: 0; border-top: 3px solid var(--accent-line); color: var(--ink);
  width: min(1180px, calc(100vw - 32px)); max-width: none; max-height: calc(100dvh - 32px); margin: auto;
  background: var(--surface); border-radius: var(--r-xl); box-shadow: var(--shadow-lg); overflow: hidden;
}
.modal[open] { display: flex; animation: modal-in .22s cubic-bezier(.2, .8, .2, 1); }
.modal::backdrop { background: rgba(43, 58, 107, .38); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); animation: fade-in .2s ease-out; }
.modal:focus { outline: 0; }
.modal-sm { width: min(460px, calc(100vw - 32px)); }
.modal-head { display: flex; align-items: center; gap: 14px; padding: 20px 24px; border-bottom: 1px solid var(--line); }
.modal-head .titles { flex: 1; min-width: 0; }
.modal-head h2 { font-size: 19px; }
.modal-head p { font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.modal-body { flex: 1; min-height: 0; overflow: auto; padding: 22px 24px; overscroll-behavior: contain; }
.modal-body > * + * { margin-top: 18px; }
.modal-foot { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 16px 24px; border-top: 1px solid var(--line); background: #f9fbfd; }
.modal-foot .note { font-size: 12.5px; color: var(--ink-3); max-width: 46ch; }
.modal-foot .foot-actions { display: flex; gap: 10px; margin-left: auto; }
.modal-foot form { margin: 0; }
.summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.summary > div { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--surface); }
.summary strong { display: block; font-size: 22px; line-height: 1.1; color: var(--ink); font-variant-numeric: tabular-nums; }
.summary span { font-size: 12.5px; color: var(--ink-3); }
.modal-body .table-card { box-shadow: none; }
@keyframes modal-in { from { opacity: 0; transform: translateY(14px) scale(.985); } to { opacity: 1; transform: none; } }
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

/* ---------- Dashboard ---------- */
.stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.stat { display: flex; flex-direction: column; gap: 14px; }
.stat .top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.stat .label { color: var(--ink-2); font-weight: 600; }
.stat strong { font-size: 30px; line-height: 1.1; font-weight: 800; letter-spacing: -.02em; color: var(--ink); font-variant-numeric: tabular-nums; }
.stat strong.money { font-size: 24px; }
.stat strong small { font-size: 14px; font-weight: 600; color: var(--ink-3); letter-spacing: 0; margin-left: 4px; }
.stat .sub { font-size: 12.5px; color: var(--ink-3); margin-top: -8px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.grid-main { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(300px, 1fr); gap: 20px; align-items: start; }
.donut-wrap { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; }
.donut { --percent: 0%; --color: var(--primary); --rest: var(--slate-soft); flex: none; width: 128px; height: 128px; border-radius: 50%; display: grid; place-items: center; background: conic-gradient(var(--color) 0 var(--percent), var(--rest) var(--percent) 100%); }
.donut-core { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 90px; height: 90px; border-radius: 50%; background: var(--surface); line-height: 1.2; }
.donut-core strong { font-size: 22px; font-weight: 800; color: var(--ink); }
.donut-core small { font-size: 11.5px; color: var(--ink-3); }
.legend { display: grid; gap: 10px; flex: 1; min-width: 130px; }
.legend li { display: flex; align-items: center; gap: 8px; color: var(--ink-2); }
.legend i { width: 10px; height: 10px; border-radius: 3px; flex: none; }
.legend span { flex: 1; }
.legend b { color: var(--ink); font-variant-numeric: tabular-nums; }
.bars { display: flex; align-items: stretch; gap: 10px; height: 230px; }
.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 0; }
.bar-val { font-size: 12.5px; font-weight: 700; color: var(--ink-2); font-variant-numeric: tabular-nums; }
.bar-track { flex: 1; width: 100%; display: flex; align-items: flex-end; justify-content: center; padding: 0 4px; border-radius: 10px; background: #f4f5f9; }
.bar { width: 100%; max-width: 44px; border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, #7b8dc4, #b7c2e2); }
.bar-col.is-today .bar { background: linear-gradient(180deg, #eb9c69, #f6c8a6); }
.bar-col.is-zero .bar { background: #dde5f0; }
.bar-lbl { font-size: 11.5px; color: var(--ink-3); white-space: nowrap; }
.recent-list { margin: -6px -10px 0; }
.recent { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: var(--r-md); color: inherit; transition: background .15s; }
.recent:hover { background: #faf6f3; color: inherit; }
.recent .chip-icon { width: 36px; height: 36px; border-radius: 10px; }
.recent .info { flex: 1; min-width: 0; }
.recent strong { display: block; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.recent small { color: var(--ink-3); font-size: 12.5px; }
.recent .go { color: var(--ink-3); }
.menu-card { display: flex; flex-direction: column; gap: 14px; color: inherit; transition: border-color .15s, box-shadow .15s; }
.menu-card:hover { color: inherit; border-color: #bfd0ee; box-shadow: var(--shadow-md); }
.menu-card h3 { font-size: 16px; }
.menu-card p { font-size: 13px; margin-top: 2px; }
.menu-card .go { margin-top: auto; display: inline-flex; align-items: center; gap: 6px; color: var(--primary-d); font-weight: 650; font-size: 13px; }

/* ---------- Detail STO (modal) ---------- */
.dl { display: grid; gap: 0; }
.dl > div { display: flex; justify-content: space-between; gap: 16px; padding: 11px 0; border-bottom: 1px solid var(--line-soft); }
.dl > div:last-child { border-bottom: 0; padding-bottom: 0; }
.dl dt { color: var(--ink-3); }
.dl dd { color: var(--ink); font-weight: 600; text-align: right; overflow-wrap: anywhere; }
.route-line { position: relative; display: grid; gap: 18px; padding-left: 26px; }
.route-line::before { content: ""; position: absolute; left: 7px; top: 10px; bottom: 10px; border-left: 2px dashed #c9cfe2; }
.route-line > div { position: relative; overflow-wrap: anywhere; }
.route-line > div::before { content: ""; position: absolute; left: -26px; top: 4px; width: 16px; height: 16px; border-radius: 50%; background: var(--surface); border: 4px solid var(--navy-2); }
.route-line > div:last-child::before { border-color: var(--primary); }
.route-line small { display: block; color: var(--ink-3); font-weight: 600; font-size: 12px; }
.route-line p { color: var(--ink); font-weight: 550; white-space: pre-line; }

.head-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.link-btn { padding: 0; border: 0; background: none; font: inherit; text-align: left; cursor: pointer; }
.data-table .sto-no.link-btn:hover, .sto-card .sto-no.link-btn:hover { color: var(--primary-d); text-decoration: underline; }
.clamp { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; line-clamp: 3; overflow: hidden; white-space: pre-line; }
.data-table .refs { min-width: 130px; }
.refs .ref { display: flex; align-items: center; gap: 8px; }
.refs .ref + .ref { margin-top: 8px; }
.refs small { width: 30px; flex: none; color: var(--ink-3); font-weight: 700; font-size: 12px; }
.refs .ref > span:not(.badge) { color: var(--ink); font-weight: 600; overflow-wrap: anywhere; }

.modal-lg { width: min(1040px, calc(100vw - 32px)); }
.modal-head h2 { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.modal-head h2 .badge { font-size: 11.5px; }
.modal:not(.is-editing) [data-edit] { display: none !important; }
.modal.is-editing [data-view] { display: none !important; }
.modal-foot .foot-main { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; width: 100%; }
.modal-foot .foot-confirm { display: none; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; width: 100%; }
.modal-foot .foot-confirm p { color: var(--rose); max-width: 60ch; }
.modal-foot .foot-confirm form { margin: 0; }
.modal.is-confirming .foot-main { display: none; }
.modal.is-confirming .foot-confirm { display: flex; }
.view-stack > * + * { margin-top: 18px; }
.panel { padding: 18px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--surface); }
.panel-warn { border-color: #f1d28b; background: #fffbf1; }
.panel > h3 { margin-bottom: 12px; }
.panel .alert { margin-bottom: 14px; }
.detail-cols { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.section-title { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.dl { display: grid; gap: 0; }
.dl > div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line-soft); }
.dl > div:first-child { padding-top: 0; }
.dl > div:last-child { border-bottom: 0; padding-bottom: 0; }
.dl dt { color: var(--ink-3); }
.dl dd { color: var(--ink); font-weight: 600; text-align: right; overflow-wrap: anywhere; }
.route-line { position: relative; display: grid; gap: 18px; padding-left: 26px; }
.route-line::before { content: ""; position: absolute; left: 7px; top: 10px; bottom: 10px; border-left: 2px dashed #c9cfe2; }
.route-line > div { position: relative; overflow-wrap: anywhere; }
.route-line > div::before { content: ""; position: absolute; left: -26px; top: 4px; width: 16px; height: 16px; border-radius: 50%; background: var(--surface); border: 4px solid var(--navy-2); }
.route-line > div:last-child::before { border-color: var(--primary); }
.route-line small { display: block; color: var(--ink-3); font-weight: 600; font-size: 12px; }
.route-line p { color: var(--ink); font-weight: 550; white-space: pre-line; }

/* ---------- Form STO ---------- */
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.form-grid .field + .field { margin-top: 0; }
.form-actions { margin-top: 16px; display: flex; justify-content: flex-end; }
.optional { color: var(--ink-3); font-weight: 500; }
.textarea { width: 100%; font: inherit; min-height: 84px; padding: 11px 14px; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); color: var(--ink); resize: vertical; line-height: 1.5; transition: border-color .15s, box-shadow .15s; }
.textarea:hover { border-color: #cbd6e6; }
.textarea:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.input.is-invalid, .select.is-invalid, .textarea.is-invalid { border-color: #e48aa0; background: #fffafb; }
.field-error { margin-top: 5px; font-size: 12.5px; color: var(--rose); }
.alert + .form-grid, form > .alert { margin-bottom: 16px; }
.items-block { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line); }
.items-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.item-list { display: grid; gap: 10px; }
.item-row { display: grid; grid-template-columns: minmax(0, 2.6fr) minmax(0, 1fr) minmax(0, .9fr) minmax(0, 1.2fr) minmax(0, 1.2fr) 38px; gap: 10px; align-items: start; padding: 12px; border: 1px solid var(--line); border-radius: var(--r-md); background: #fafbfd; }
.item-row .field + .field { margin-top: 0; }
.item-row .item-remove { margin-top: 30px; color: var(--ink-3); }
.item-row .item-remove:hover { background: var(--rose-soft); color: var(--rose); }
.item-row .amount { display: flex; align-items: center; height: 44px; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums; overflow: hidden; }
.items-total { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 14px; padding: 14px 16px; border-radius: var(--r-md); background: var(--navy-soft); color: var(--ink-2); font-weight: 600; }
.items-total strong { font-size: 18px; color: var(--ink); font-variant-numeric: tabular-nums; }

/* ---------- Akun ---------- */
.account-grid { display: grid; grid-template-columns: minmax(260px, 1fr) minmax(0, 1.6fr); gap: 20px; align-items: start; }
.profile { text-align: center; }
.profile .avatar { margin: 0 auto 14px; }
.profile h2 { font-size: 19px; }
.profile p { margin-top: 2px; }
.pw-rules { display: flex; gap: 10px; align-items: center; margin-top: 16px; padding: 12px 14px; border-radius: var(--r-md); background: #f7f9fc; font-size: 13px; color: var(--ink-2); }
.form-actions { margin-top: 22px; display: flex; justify-content: flex-end; }

/* ---------- Login ---------- */
.auth { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); background: var(--surface); }
.auth-tag { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px; padding: 6px 14px; border: 1px solid rgba(242, 193, 78, .55); border-radius: 999px; background: rgba(242, 193, 78, .12); color: #f6cf6a; font-size: 13px; font-weight: 700; align-self: flex-start; }
.auth-main { display: flex; flex-direction: column; padding: 32px clamp(24px, 6vw, 72px); }
.auth-form { width: 100%; max-width: 400px; margin: auto; }
.auth-form h1 { font-size: 28px; margin-top: 36px; }
.auth-form > p { margin-top: 6px; }
.auth-form form { margin-top: 26px; }
.auth-form .btn { margin-top: 24px; }
.auth-form .alert { margin-top: 20px; }
.auth-copy { text-align: center; color: var(--ink-3); font-size: 12.5px; }
.auth-panel { position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; padding: clamp(32px, 5vw, 64px); color: #fff; background: linear-gradient(150deg, #33447a 0%, #5568a6 100%); border-left: 3px solid var(--accent-line); }
.auth-panel::before { content: ""; position: absolute; inset: 0; background: radial-gradient(520px 320px at 85% 8%, rgba(255, 255, 255, .16), transparent 70%), radial-gradient(460px 320px at 0% 100%, rgba(229, 139, 82, .22), transparent 70%); }
.auth-panel > * { position: relative; }
.auth-art { position: absolute; top: 9%; left: 50%; width: min(78%, 480px); transform: translateX(-50%); }
.auth-panel h2 { font-size: clamp(28px, 3.2vw, 40px); line-height: 1.15; font-weight: 800; letter-spacing: -.02em; }
.auth-panel p { margin-top: 12px; color: rgba(255, 255, 255, .82); max-width: 40ch; font-size: 15px; }

/* ---------- Halaman error ---------- */
.error-wrap { flex: 1; display: grid; place-items: center; padding: 32px 20px; }
.error-card { width: min(480px, 100%); text-align: center; padding: 40px 32px; border-top: 3px solid var(--accent-line); }
.error-card .brand { margin-bottom: 28px; }
.error-code { font-size: 72px; line-height: 1; font-weight: 800; letter-spacing: -.04em; background: linear-gradient(135deg, var(--navy-2), #8395cc); -webkit-background-clip: text; background-clip: text; color: transparent; }
.error-card h2 { margin-top: 14px; font-size: 20px; }
.error-card p { margin: 8px 0 26px; }

/* ---------- Responsif ---------- */
@media (max-width: 1100px) {
  .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .grid-main, .account-grid { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 900px) {
  .detail-cols, .form-grid { grid-template-columns: minmax(0, 1fr); }
  .form-grid > .field:empty { display: none; }
  .item-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .item-row .item-name { grid-column: 1 / -1; }
  .item-row .item-remove { grid-column: 1 / -1; justify-self: end; margin-top: 0; }
  .upload-grid { grid-template-columns: minmax(0, 1fr); }
  .dropzone { min-height: 220px; }
  .auth { grid-template-columns: minmax(0, 1fr); }
  .auth-panel { display: none; }
  .grid-2 { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 820px) {
  .desk-only { display: none; }
  .sto-cards { display: grid; }
  .table-card.desk-only { display: none; }
}
@media (max-width: 720px) {
  .container { padding-inline: 16px; }
  .page { padding-top: 22px; }
  h1 { font-size: 24px; }
  .topbar-in { gap: 12px; }
  .brand .brand-name { display: none; }
  .mainnav { gap: 2px; }
  .navlink { padding: 0 12px; }
  .usermenu .uname, .usermenu .chev { display: none; }
  .usermenu summary { padding: 4px; }
  .card { padding: 18px; border-radius: 16px; }
  .stats, .grid-3 { grid-template-columns: minmax(0, 1fr); }
  .filters .select, .filters .btn, .filters .input-icon { flex: 1 1 100%; width: 100%; }
  .section-head .btn, .page-head .btn { width: 100%; }
  .page-head { padding: 20px; border-radius: 16px; }
  .head-actions { width: 100%; }
  .head-actions .btn { flex: 1 1 0; }
  .input, .select { font-size: 16px; height: 48px; }
  .pager { justify-content: center; }
  .pager-info { width: 100%; text-align: center; }
  .summary { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
  .summary > div { flex-direction: column; align-items: flex-start; gap: 2px; padding: 10px 12px; }
  .summary .chip-icon { display: none; }
  .summary strong { font-size: 20px; }
  .summary span { font-size: 11.5px; line-height: 1.3; }

  /* modal menjadi layar penuh */
  .modal, .modal-sm { width: 100vw; max-height: none; height: 100dvh; border-radius: 0; }
  .modal-sm { height: auto; width: calc(100vw - 24px); border-radius: var(--r-xl); max-height: calc(100dvh - 24px); }
  .modal-head, .modal-body, .modal-foot { padding-inline: 16px; }
  .modal-foot { position: sticky; bottom: 0; }
  .modal-foot .note { display: none; }
  .modal-foot .foot-actions { width: 100%; flex-direction: column-reverse; }
  .modal-foot .foot-actions form, .modal-foot .foot-actions .btn { width: 100%; }
  .modal-foot form .btn { width: 100%; }

  /* tabel menjadi kartu berlabel */
  .stack-table, .stack-table tbody { display: block; }
  .stack-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); }
  .stack-table tr { display: block; padding: 6px 14px; margin-bottom: 12px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-md); }
  .stack-table tr.is-dup { background: #fffaf0; }
  .stack-table td { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; text-align: right; border-bottom: 1px solid var(--line-soft) !important; }
  .stack-table td:last-child { border-bottom: 0 !important; }
  .stack-table td::before { content: attr(data-label); flex: none; color: var(--ink-3); font-weight: 600; text-align: left; }
  .stack-table td > * { text-align: right; }
  .stack-table .route-cell, .stack-table .place, .stack-table .line-items { min-width: 0; }
  .stack-table .col-no { width: auto; }
  .stack-table .line-items { text-align: right; }
  .stack-table tfoot, .stack-table tfoot tr { display: block; }
  .stack-table tfoot tr { background: #f7f9fc; }
  .stack-table tfoot th, .stack-table tfoot td { display: flex; justify-content: space-between; border: 0 !important; padding: 8px 0; }
  .stack-table tfoot td::before { content: none; }
  .table-card:has(.stack-table) { border: 0; background: transparent; box-shadow: none; overflow: visible; }

  .dl > div { flex-direction: column; gap: 2px; }
  .dl dd { text-align: left; }
  .error-code { font-size: 60px; }
}
@media (max-width: 480px) {
  .navlink { padding: 0 10px; }
  .navlink .icon { display: none; }
}
@media (max-width: 380px) {
  .container { padding-inline: 12px; }
}
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
}
@media print {
  .topbar, .footer, .filters, .dropzone, .toast-stack, .pager, .row-actions, .modal { display: none !important; }
  body { background: #fff; }
  .container { max-width: none; padding: 0; }
  .card, .table-card { box-shadow: none; }
}
```


## public/index.php

```text
<?php
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) require $maintenance;
require __DIR__.'/../vendor/autoload.php';
(require_once __DIR__.'/../bootstrap/app.php')->handleRequest(Request::capture());

```


## public/js/app.js

```text
/* STO Alber — peningkatan tampilan (tanpa library, tanpa npm).
   Halaman tetap berfungsi tanpa berkas ini; skrip hanya menambah kenyamanan. */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---- Menu pengguna: tutup saat klik di luar / tekan Esc ---- */
  var menu = $('.usermenu');
  if (menu) {
    document.addEventListener('click', function (e) {
      if (menu.open && !menu.contains(e.target)) { menu.open = false; }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && menu.open) { menu.open = false; $('summary', menu).focus(); }
    });
  }

  /* ---- Toast: hilang otomatis ---- */
  $$('.toast').forEach(function (toast) {
    var closeBtn = $('[data-toast-close]', toast);
    function dismiss() {
      toast.classList.add('is-leaving');
      setTimeout(function () { toast.remove(); }, 220);
    }
    if (closeBtn) { closeBtn.addEventListener('click', dismiss); }
    setTimeout(dismiss, 6000);
  });

  /* ---- Dropzone upload PDF ---- */
  var MAX_BYTES = 10 * 1024 * 1024;
  function formatSize(bytes) {
    if (bytes >= 1048576) { return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB'; }
    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }
  $$('[data-dropzone]').forEach(function (zone) {
    var input = $('input[type=file]', zone);
    var title = $('[data-dz-title]', zone);
    var hint = $('[data-dz-hint]', zone);
    if (!input || !title || !hint) { return; }
    var defaultTitle = title.textContent;
    var defaultHint = hint.textContent;

    function reset() {
      zone.classList.remove('has-file', 'has-error');
      title.textContent = defaultTitle;
      hint.textContent = defaultHint;
    }
    function fail(message) {
      input.value = '';
      zone.classList.remove('has-file');
      zone.classList.add('has-error');
      title.textContent = message;
      hint.textContent = 'Pilih file lain.';
    }
    function render() {
      var file = input.files && input.files[0];
      if (!file) { reset(); return; }
      zone.classList.remove('has-error');
      var isPdf = /\.pdf$/i.test(file.name) || file.type === 'application/pdf';
      if (!isPdf) { fail('File harus berformat PDF'); return; }
      if (file.size > MAX_BYTES) { fail('Ukuran file lebih dari 10 MB'); return; }
      zone.classList.add('has-file');
      title.textContent = file.name;
      hint.textContent = formatSize(file.size) + ' · siap dipratinjau';
    }

    input.addEventListener('change', render);
    ['dragenter', 'dragover'].forEach(function (name) {
      zone.addEventListener(name, function (e) { e.preventDefault(); zone.classList.add('is-drag'); });
    });
    ['dragleave', 'drop'].forEach(function (name) {
      zone.addEventListener(name, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); });
    });
    zone.addEventListener('drop', function (e) {
      var files = e.dataTransfer && e.dataTransfer.files;
      if (!files || !files.length) { return; }
      try {
        var transfer = new DataTransfer();
        transfer.items.add(files[0]);
        input.files = transfer.files;
      } catch (err) {
        input.files = files;
      }
      render();
    });
  });

  /* ---- Dialog / modal ---- */
  function openDialog(dlg) {
    if (dlg && dlg.showModal && !dlg.open) { dlg.showModal(); }
  }
  function formOf(dlg) { return $('form[data-sto-form]', dlg); }

  // Kembalikan form ubah ke isi awal (termasuk baris barang yang ditambah/dihapus).
  function resetEdit(dlg) {
    var form = formOf(dlg);
    if (!form) { return; }
    if (form.hasAttribute('data-reload-on-cancel')) {
      // Form dibuka ulang setelah gagal validasi: muat ulang agar kembali ke data tersimpan.
      window.location.replace(window.location.pathname + window.location.search.replace(/([?&])open=[^&]*&?/, '$1').replace(/[?&]$/, ''));
      return;
    }
    form.reset();
    if (form._itemsInitial !== undefined) {
      $('[data-item-list]', form).innerHTML = form._itemsInitial;
      form._recalc();
    }
    dlg.classList.remove('is-editing');
  }

  $$('[data-dialog-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openDialog(document.getElementById(btn.getAttribute('data-dialog-open')));
    });
  });
  $$('[data-dialog-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = btn.closest('dialog');
      if (dlg) { dlg.close(); }
    });
  });
  $$('[data-edit-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = btn.closest('dialog');
      dlg.classList.add('is-editing');
      var first = $('form[data-sto-form] input:not([type=hidden])', dlg);
      if (first) { first.focus(); }
      $('.modal-body', dlg).scrollTop = 0;
    });
  });
  $$('[data-edit-cancel]').forEach(function (btn) {
    btn.addEventListener('click', function () { resetEdit(btn.closest('dialog')); });
  });
  $$('[data-delete-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () { btn.closest('dialog').classList.toggle('is-confirming'); });
  });

  $$('dialog.modal').forEach(function (dlg) {
    var persistent = dlg.hasAttribute('data-persistent');
    // Selama mengisi form (ubah/tambah), Esc dan klik di luar tidak menutup agar isian tidak hilang.
    dlg.addEventListener('cancel', function (e) {
      if (persistent || dlg.classList.contains('is-editing')) { e.preventDefault(); }
    });
    if (!persistent) {
      dlg.addEventListener('click', function (e) {
        if (e.target === dlg && !dlg.classList.contains('is-editing')) { dlg.close(); }
      });
    }
    dlg.addEventListener('close', function () {
      dlg.classList.remove('is-confirming');
      if (!dlg.hasAttribute('data-always-edit') && dlg.classList.contains('is-editing')) { resetEdit(dlg); }
    });
    if (dlg.hasAttribute('data-autoopen') && dlg.showModal) {
      dlg.removeAttribute('open');
      dlg.showModal();
      // Fokus ke jendela itu sendiri agar tombol tutup tidak langsung tersorot.
      dlg.setAttribute('tabindex', '-1');
      dlg.focus({ preventScroll: true });
    }
  });

  /* ---- Form STO: baris barang dinamis dan hitung biaya otomatis ---- */
  var rupiah = function (cents) {
    return (cents / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  };
  $$('form[data-sto-form]').forEach(function (form) {
    var list = $('[data-item-list]', form);
    var tpl = $('template[data-item-template]', form);
    var totalEl = $('[data-total]', form);
    var nextIndex = 1000;
    form._itemsInitial = list.innerHTML;

    function recalc() {
      var total = 0;
      $$('[data-item-row]', list).forEach(function (row) {
        var q = parseFloat($('[data-q]', row).value);
        var r = parseFloat($('[data-r]', row).value);
        var cents = (isFinite(q) && isFinite(r)) ? Math.round(Math.round(q * 1000) * Math.round(r * 100) / 1000) : 0;
        total += cents;
        $('[data-amount]', row).textContent = rupiah(cents);
      });
      totalEl.textContent = rupiah(total);
    }
    form._recalc = recalc;

    list.addEventListener('input', recalc);
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-item-remove]');
      if (!btn) { return; }
      var rows = $$('[data-item-row]', list);
      var row = btn.closest('[data-item-row]');
      if (rows.length > 1) {
        row.remove();
      } else {
        // Baris terakhir tidak dihapus (minimal satu barang); cukup dikosongkan, satuan dipertahankan.
        $$('input', row).forEach(function (input) { if (!/\[unit\]$/.test(input.name)) { input.value = ''; } });
      }
      recalc();
    });
    $('[data-item-add]', form).addEventListener('click', function () {
      list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, String(nextIndex++)));
      var row = list.lastElementChild;
      var first = $('input', row);
      if (first) { first.focus(); }
      recalc();
    });
    recalc();
  });

  /* ---- Tampilkan / sembunyikan password ---- */
  $$('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-toggle-password'));
      if (!input) { return; }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('is-on', show);
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  /* ---- Status memuat pada form (cegah kirim ganda) ---- */
  // Tombol simpan bisa berada di luar <form> (atribut form="id"), mis. pada footer modal.
  function submitButton(form) {
    return $('[data-submit]', form) || (form.id ? $('[data-submit][form="' + form.id + '"]') : null);
  }
  var loadingForms = $$('form[data-loading]');
  loadingForms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.getAttribute('data-busy')) { e.preventDefault(); return; }
      form.setAttribute('data-busy', '1');
      var btn = submitButton(form);
      if (!btn) { return; }
      var text = btn.getAttribute('data-loading-text') || 'Memproses…';
      btn.setAttribute('data-original', btn.innerHTML);
      setTimeout(function () {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner" aria-hidden="true"></span><span>' + text + '</span>';
      }, 0);
    });
  });
  // Tombol Kembali pada browser (bfcache): pulihkan tampilan tombol.
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) { return; }
    loadingForms.forEach(function (form) {
      form.removeAttribute('data-busy');
      var btn = submitButton(form);
      if (btn && btn.getAttribute('data-original')) {
        btn.innerHTML = btn.getAttribute('data-original');
        btn.disabled = false;
      }
    });
  });
})();
```


## resources/views/auth/account.blade.php

```text
@extends('layouts.app')
@section('title', 'Akun')

@section('content')
<div class="page-head">
    <div>
        <h1>Akun</h1>
        <p class="lead">Profil dan password akun Anda.</p>
    </div>
</div>

<div class="account-grid">
    <section class="card profile" aria-label="Profil">
        <span class="avatar avatar-lg">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <h2>{{ auth()->user()->name }}</h2>
        <p>Username: {{ auth()->user()->username }}</p>
    </section>

    <section class="card" aria-labelledby="pw-title">
        <div class="card-head">
            <div class="card-title">
                <span class="chip-icon tone-blue"><x-icon name="lock" :size="20" /></span>
                <div><h2 id="pw-title">Ubah Password</h2></div>
            </div>
        </div>

        <form method="post" action="{{ route('password.update') }}" data-loading>
            @csrf @method('PUT')
            <div class="field">
                <label class="field-label" for="current_password">Password saat ini</label>
                <input class="input" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>
            <div class="field">
                <label class="field-label" for="password">Password baru</label>
                <input class="input" type="password" id="password" name="password" autocomplete="new-password" minlength="12" required>
            </div>
            <div class="field">
                <label class="field-label" for="password_confirmation">Ulangi password baru</label>
                <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <div class="pw-rules"><x-icon name="info" :size="18" />Minimal 12 karakter.</div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" data-submit data-loading-text="Menyimpan…">Simpan Password</button>
            </div>
        </form>
    </section>
</div>
@endsection
```


## resources/views/auth/login.blade.php

```text
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<div class="auth">
    <main class="auth-main">
        <a class="brand" href="{{ route('login') }}" aria-label="STO Alber">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>

        <div class="auth-form">
            <h1>Masuk</h1>
            <p>Gunakan username dan password Anda.</p>

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <x-icon name="alert" :size="20" />
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="post" action="{{ route('login.submit') }}" data-loading>
                @csrf
                <div class="field">
                    <label class="field-label" for="username">Username</label>
                    <div class="input-icon">
                        <x-icon name="user" :size="18" />
                        <input class="input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="Masukkan username">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <div class="input-icon has-reveal">
                        <x-icon name="lock" :size="18" />
                        <input class="input" id="password" type="password" name="password" autocomplete="current-password" required placeholder="Masukkan password">
                        <button type="button" class="icon-btn reveal" data-toggle-password="password" aria-label="Tampilkan password" aria-pressed="false">
                            <x-icon name="eye" :size="18" class="icon eye-on" /><x-icon name="eye-off" :size="18" class="icon eye-off" />
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit data-loading-text="Memproses…">Masuk</button>
            </form>
        </div>

        <p class="auth-copy">@include('partials.copyright')</p>
    </main>

    <aside class="auth-panel" aria-hidden="true">
        <svg class="auth-art" viewBox="0 0 480 330" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="8" y="296" width="464" height="10" rx="5" fill="#fff" opacity=".18"/>
            {{-- palet kiri --}}
            <rect x="40" y="276" width="170" height="18" rx="4" fill="#fff" opacity=".35"/>
            <rect x="48" y="226" width="74" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="128" y="226" width="74" height="46" rx="9" fill="#fff" opacity=".7"/>
            <rect x="48" y="176" width="74" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="128" y="176" width="74" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="88" y="126" width="74" height="46" rx="9" fill="#fff" opacity=".8"/>
            {{-- palet kanan --}}
            <rect x="260" y="276" width="190" height="18" rx="4" fill="#fff" opacity=".35"/>
            <rect x="268" y="226" width="80" height="46" rx="9" fill="#fff" opacity=".7"/>
            <rect x="354" y="226" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="268" y="176" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="354" y="176" width="80" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="268" y="126" width="80" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="354" y="126" width="80" height="46" rx="9" fill="#fff" opacity=".85"/>
            <rect x="310" y="76" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            {{-- tanda dokumen --}}
            <rect x="196" y="26" width="92" height="64" rx="12" fill="#e99a68"/>
            <path d="M222 48h40M222 60h40M222 72h24" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
        </svg>
        <span class="auth-tag">STO Alat Berat</span>
        <h2>Data STO<br>rapi di satu tempat.</h2>
        <p>Impor dari PDF, periksa, lalu simpan.</p>
    </aside>
</div>
</body>
</html>
```


## resources/views/components/donut.blade.php

```text
@php
    $pct = $total > 0 ? round($value / $total * 100, 1) : 0;
    $ringColor = $total > 0 ? $color : '#dfe5ee';
    $ringRest = $total > 0 ? $rest : '#dfe5ee';
@endphp
<div class="donut-wrap">
    <div class="donut" style="--percent:{{ $pct }}%;--color:{{ $ringColor }};--rest:{{ $ringRest }}" role="img" aria-label="{{ $label }}: {{ $value }} dari {{ $total }} STO ({{ (int) round($pct) }} persen)">
        <div class="donut-core"><strong>{{ (int) round($pct) }}%</strong><small>{{ $label }}</small></div>
    </div>
    <ul class="legend">
        <li><i style="background:{{ $color }}"></i><span>{{ $label }}</span><b>{{ $value }}</b></li>
        <li><i style="background:{{ $rest }}"></i><span>{{ $other }}</span><b>{{ $total - $value }}</b></li>
    </ul>
</div>

```


## resources/views/components/icon.blade.php

```text
@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
@case('package')<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5"/><path d="M12 12v9"/>@break
@case('upload')<path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>@break
@case('download')<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>@break
@case('file')<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/>@break
@case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>@break
@case('x')<path d="M18 6 6 18"/><path d="m6 6 12 12"/>@break
@case('check')<path d="m5 12 5 5L20 7"/>@break
@case('check-circle')<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>@break
@case('alert')<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>@break
@case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>@break
@case('grid')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
@case('logout')<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>@break
@case('eye')<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>@break
@case('eye-off')<path d="M3 3l18 18"/><path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1"/><path d="M6.6 6.6C3.7 8.4 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>@break
@case('arrow-left')<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>@break
@case('arrow-right')<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>@break
@case('chevron-down')<path d="m6 9 6 6 6-6"/>@break
@case('chevron-left')<path d="m15 18-6-6 6-6"/>@break
@case('chevron-right')<path d="m9 18 6-6-6-6"/>@break
@case('trash')<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>@break
@case('shield')<path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('external')<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>@break
@case('rotate')<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>@break
@case('wallet')<path d="M20 7H5a2 2 0 0 1 0-4h13v4"/><path d="M3 5v14a2 2 0 0 0 2 2h15V7"/><path d="M16 14h2"/>@break
@case('key')<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9"/><path d="m16 7 3 3"/>@break
@case('chart')<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>@break
@case('lock')<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>@break
@case('layers')<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>@break
@case('inbox')<path d="M3 13h5l1.5 3h5L16 13h5"/><path d="M5.5 5h13L21 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6z"/>@break
@case('plus')<path d="M12 5v14"/><path d="M5 12h14"/>@break
@case('edit')<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>@break
@endswitch
</svg>

```


## resources/views/components/pagination.blade.php

```text
@if($paginator->hasPages())
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $from = max(1, $current - 2);
    $to = min($last, $current + 2);
@endphp
<nav class="pager" aria-label="Halaman data">
    <span class="pager-info">Halaman {{ $current }} dari {{ $last }} · {{ number_format($paginator->total(), 0, ',', '.') }} STO</span>
    <div class="pager-links">
        @if($paginator->onFirstPage())
            <span class="page-link is-wide is-disabled" aria-disabled="true"><x-icon name="chevron-left" :size="16" />Sebelumnya</span>
        @else
            <a class="page-link is-wide" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="chevron-left" :size="16" />Sebelumnya</a>
        @endif
        @if($from > 1)
            <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
            @if($from > 2)<span class="page-link is-disabled">…</span>@endif
        @endif
        @for($page = $from; $page <= $to; $page++)
            @if($page === $current)
                <span class="page-link is-current" aria-current="page">{{ $page }}</span>
            @else
                <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor
        @if($to < $last)
            @if($to < $last - 1)<span class="page-link is-disabled">…</span>@endif
            <a class="page-link" href="{{ $paginator->url($last) }}">{{ $last }}</a>
        @endif
        @if($paginator->hasMorePages())
            <a class="page-link is-wide" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya<x-icon name="chevron-right" :size="16" /></a>
        @else
            <span class="page-link is-wide is-disabled" aria-disabled="true">Berikutnya<x-icon name="chevron-right" :size="16" /></span>
        @endif
    </div>
</nav>
@endif
```


## resources/views/dashboard.blade.php

```text
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $n = fn ($v) => number_format((float) $v, 0, ',', '.');
    $complete = $total - $incomplete;
@endphp

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p class="lead">Ringkasan jumlah, nilai, dan kelengkapan STO.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('sto.index') }}"><x-icon name="upload" :size="18" />Unggah STO</a>
</div>

@if($incomplete > 0)
    <div class="alert alert-warn" role="status">
        <x-icon name="alert" :size="20" />
        <p><strong>{{ $n($incomplete) }} STO belum lengkap.</strong> SOJ atau MMJ masih kosong. <a href="{{ route('sto.index', ['completeness' => 'incomplete']) }}">Lihat daftar</a></p>
    </div>
@endif

<div class="stats">
    <section class="card stat tone-blue" aria-label="Total STO">
        <div class="top"><span class="label">Total STO</span><span class="chip-icon"><x-icon name="layers" :size="20" /></span></div>
        <strong>{{ $n($total) }}</strong>
        <span class="sub">Dokumen tersimpan</span>
    </section>
    <section class="card stat tone-orange" aria-label="Total nilai">
        <div class="top"><span class="label">Total Nilai</span><span class="chip-icon"><x-icon name="wallet" :size="20" /></span></div>
        <strong class="money">Rp {{ $n($amount) }}</strong>
        <span class="sub">Jumlah biaya seluruh STO</span>
    </section>
    <section class="card stat {{ $sojEmpty > 0 ? 'tone-amber' : 'tone-green' }}" aria-label="STO tanpa SOJ">
        <div class="top"><span class="label">SOJ Kosong</span><span class="chip-icon"><x-icon :name="$sojEmpty > 0 ? 'alert' : 'check-circle'" :size="20" /></span></div>
        <strong>{{ $n($sojEmpty) }}</strong>
        <span class="sub">{{ $sojEmpty > 0 ? 'STO belum ada SOJ' : 'Semua sudah terisi' }}</span>
    </section>
    <section class="card stat {{ $mmjEmpty > 0 ? 'tone-amber' : 'tone-green' }}" aria-label="STO tanpa MMJ">
        <div class="top"><span class="label">MMJ Kosong</span><span class="chip-icon"><x-icon :name="$mmjEmpty > 0 ? 'alert' : 'check-circle'" :size="20" /></span></div>
        <strong>{{ $n($mmjEmpty) }}</strong>
        <span class="sub">{{ $mmjEmpty > 0 ? 'STO belum ada MMJ' : 'Semua sudah terisi' }}</span>
    </section>
</div>

<div class="grid-2">
    <section class="card">
        <div class="card-head"><div><h2>Jenis Kegiatan</h2><p>Perbandingan STO bongkar dan muat.</p></div></div>
        @include('components.donut', ['value' => $bongkar, 'total' => $total, 'label' => 'Bongkar', 'other' => 'Muat', 'color' => '#5b6ea9', 'rest' => '#62b3a7'])
    </section>
    <section class="card">
        <div class="card-head"><div><h2>Kelengkapan SOJ &amp; MMJ</h2><p>STO yang SOJ dan MMJ-nya sudah terisi.</p></div></div>
        @include('components.donut', ['value' => $complete, 'total' => $total, 'label' => 'Lengkap', 'other' => 'Belum lengkap', 'color' => '#4fa583', 'rest' => '#f4d58c'])
    </section>
</div>

<div class="grid-main">
    <section class="card">
        <div class="card-head"><div><h2>STO Masuk 7 Hari Terakhir</h2><p>Jumlah STO tersimpan per hari.</p></div></div>
        @php $max = max(1, max(array_column($days, 'count'))); @endphp
        <div class="bars" role="img" aria-label="Grafik jumlah STO tersimpan per hari, 7 hari terakhir">
            @foreach($days as $day)
                <div class="bar-col {{ $loop->last ? 'is-today' : '' }} {{ $day['count'] === 0 ? 'is-zero' : '' }}">
                    <span class="bar-val">{{ $day['count'] }}</span>
                    <div class="bar-track"><div class="bar" style="height:{{ max(3, round($day['count'] / $max * 100)) }}%"></div></div>
                    <span class="bar-lbl">{{ $day['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card">
        <div class="card-head"><div><h2>STO Terbaru</h2><p>5 STO terakhir yang disimpan.</p></div></div>
        @if($latest->isEmpty())
            <p>Belum ada STO. Unggah PDF atau tambah manual di <a href="{{ route('sto.index') }}">Master STO</a>.</p>
        @else
            <div class="recent-list">
                @foreach($latest as $sto)
                    <a class="recent" href="{{ route('sto.index', ['open' => $sto->id]) }}">
                        <span class="chip-icon {{ $sto->activity === 'BONGKAR' ? 'tone-blue' : 'tone-teal' }}"><x-icon name="file" :size="18" /></span>
                        <span class="info">
                            <strong>{{ $sto->number }}</strong>
                            <small>{{ $sto->document_date->format('d/m/Y') }} · {{ $sto->activity }}@if($sto->missingReferences()) · {{ implode(' & ', $sto->missingReferences()) }} kosong @endif</small>
                        </span>
                        <x-icon name="chevron-right" :size="18" class="go" />
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
```


## resources/views/errors/403.blade.php

```text
@extends('errors.layout')
@section('code','403')
@section('message','Akses ditolak')
@section('detail','Anda tidak punya akses ke halaman ini.')
```


## resources/views/errors/404.blade.php

```text
@extends('errors.layout')
@section('code','404')
@section('message','Halaman tidak ditemukan')
@section('detail','Periksa kembali alamatnya.')
```


## resources/views/errors/409.blade.php

```text
@extends('errors.layout')
@section('code','409')
@section('message','Permintaan tidak dapat diproses')
@section('detail','Data sudah berubah. Muat ulang halaman, lalu coba lagi.')
```


## resources/views/errors/410.blade.php

```text
@extends('errors.layout')
@section('code','410')
@section('message','Pratinjau tidak berlaku')
@section('detail','Impor sudah disimpan, dibatalkan, atau lewat 24 jam. Unggah ulang PDF bila perlu.')
```


## resources/views/errors/419.blade.php

```text
@extends('errors.layout')
@section('code','419')
@section('message','Sesi berakhir')
@section('detail','Muat ulang halaman, lalu ulangi tindakan Anda.')
```


## resources/views/errors/429.blade.php

```text
@extends('errors.layout')
@section('code','429')
@section('message','Terlalu banyak permintaan')
@section('detail','Tunggu sebentar, lalu coba lagi.')
```


## resources/views/errors/500.blade.php

```text
@extends('errors.layout')
@section('code','500')
@section('message','Terjadi kesalahan sistem')
@section('detail','Coba lagi. Hubungi admin IT jika masih terjadi.')
```


## resources/views/errors/503.blade.php

```text
@extends('errors.layout')
@section('code','503')
@section('message','Sedang pemeliharaan')
@section('detail','Coba lagi beberapa saat lagi.')
```


## resources/views/errors/layout.blade.php

```text
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('code') · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<main class="error-wrap">
    <section class="card error-card">
        <a class="brand" href="{{ url('/') }}" aria-label="STO Alber">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>
        <div class="error-code">@yield('code')</div>
        <h2>@yield('message')</h2>
        <p>@yield('detail')</p>
        <a class="btn btn-primary" href="{{ url('/') }}"><x-icon name="arrow-left" :size="18" />Kembali</a>
    </section>
</main>
<footer class="footer container">@include('partials.copyright')</footer>
</body>
</html>
```


## resources/views/layouts/app.blade.php

```text
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Dashboard') · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<a class="skip-link" href="#main-content">Langsung ke konten</a>

<header class="topbar">
    <div class="topbar-in container">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="STO Alber, ke Dashboard">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>

        <nav class="mainnav" aria-label="Menu utama">
            <a class="navlink {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <x-icon name="grid" :size="18" /><span>Dashboard</span>
            </a>
            <a class="navlink {{ request()->routeIs('sto.*') ? 'is-active' : '' }}" href="{{ route('sto.index') }}" @if(request()->routeIs('sto.*')) aria-current="page" @endif>
                <x-icon name="layers" :size="18" /><span>Master STO</span>
            </a>
        </nav>

        <details class="usermenu">
            <summary aria-label="Menu akun">
                <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="uname">{{ auth()->user()->name }}</span>
                <x-icon name="chevron-down" :size="16" class="chev" />
            </summary>
            <div class="menu-pop">
                <div class="who">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->username }}</span>
                </div>
                <a href="{{ route('account') }}"><x-icon name="key" :size="18" />Akun</a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="is-danger"><x-icon name="logout" :size="18" />Keluar</button>
                </form>
            </div>
        </details>
    </div>
</header>

@if(session('success'))
    <div class="toast-stack">
        <div class="toast" role="status">
            <x-icon name="check-circle" :size="22" />
            <p>{{ session('success') }}</p>
            <button type="button" class="icon-btn" data-toast-close aria-label="Tutup pemberitahuan"><x-icon name="x" :size="18" /></button>
        </div>
    </div>
@endif

<main class="container page" id="main-content">
    @if($errors->any() && ! $__env->hasSection('local_errors'))
        <div class="alert alert-danger" role="alert">
            <x-icon name="alert" :size="20" />
            <div>
                <strong>Periksa kembali isian Anda:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="footer container">@include('partials.copyright')</footer>
</body>
</html>
```


## resources/views/partials/copyright.blade.php

```text
{{-- Teks hak cipta tunggal untuk semua footer (layout utama, login, halaman error). --}}
© 2026 IT &amp; Alat Berat Wilayah 1 - PT Petrokopindo Cipta Selaras
```


## resources/views/components/logo.blade.php

```blade
{{--
    Logo STO Alber: peti barang (stok) + panah (transport) di atas kotak navy.
    Pakai: <x-logo :size="38" />  — salinan berkas yang sama ada di public/favicon.svg.
--}}
@props(['size' => 38])
@php $gid = 'sto-logo-'.\Illuminate\Support\Str::random(6); @endphp
<svg {{ $attributes->merge(['class' => 'logo-mark']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64" role="img" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $gid }}" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#4a5d9c"/>
            <stop offset="1" stop-color="#2b3a6b"/>
        </linearGradient>
    </defs>
    <rect width="64" height="64" rx="16" fill="url(#{{ $gid }})"/>
    <path d="M32 9 50 18.5v19L32 47 14 37.5v-19z" fill="#fff"/>
    <path d="M32 9 50 18.5 32 28 14 18.5z" fill="#f2a46b"/>
    <path d="M32 28v19" stroke="#d5dbee" stroke-width="2.4"/>
    <path d="M23 13.7 41 23.2v6.6" fill="none" stroke="#fff" stroke-width="2.6" stroke-linejoin="round"/>
    <path d="M15 54.5h30" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round"/>
    <path d="M41 49.5 47.5 54.5 41 59.5" fill="none" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
```


## public/favicon.svg

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <defs>
    <linearGradient id="sto-bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#4a5d9c"/>
      <stop offset="1" stop-color="#2b3a6b"/>
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="16" fill="url(#sto-bg)"/>
  <path d="M32 9 50 18.5v19L32 47 14 37.5v-19z" fill="#fff"/>
  <path d="M32 9 50 18.5 32 28 14 18.5z" fill="#f2a46b"/>
  <path d="M32 28v19" stroke="#d5dbee" stroke-width="2.4"/>
  <path d="M23 13.7 41 23.2v6.6" fill="none" stroke="#fff" stroke-width="2.6" stroke-linejoin="round"/>
  <path d="M15 54.5h30" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round"/>
  <path d="M41 49.5 47.5 54.5 41 59.5" fill="none" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
```


## resources/views/partials/assets.blade.php

```text
<meta name="color-scheme" content="light">
<link rel="icon" href="{{ asset('favicon.svg') }}?v=2" type="image/svg+xml">
<link rel="alternate icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#33447a">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}" defer></script>
```


## resources/views/sto/_form.blade.php

```text
{{--
    Form tambah / ubah STO (hanya isi form; tombol simpan ada pada footer modal lewat atribut form="").
    Parameter: $formId, $action, $method (POST|PUT), $sto (Sto|null), $modalId, $formName, $submitted (bool)
--}}
@php
    $bag = $submitted ? $errors->getBag('sto') : new \Illuminate\Support\MessageBag();
    $val = fn (string $key, $default = '') => $submitted ? old($key, '') : $default;
    $trim = fn ($n, int $d) => rtrim(rtrim(number_format((float) $n, $d, '.', ''), '0'), '.');
    if ($submitted) {
        $items = array_values(array_filter((array) old('items', []), 'is_array'));
    } elseif ($sto) {
        $items = $sto->items->map(fn ($it) => [
            'name' => $it->name,
            'quantity' => $trim($it->quantity, 3),
            'unit' => $it->unit,
            'rate' => $trim($it->rate, 2),
        ])->all();
    } else {
        $items = [];
    }
    if (count($items) === 0) {
        $items = [['name' => '', 'quantity' => '', 'unit' => 'TON', 'rate' => '']];
    }
@endphp
<form id="{{ $formId }}" method="post" action="{{ $action }}" data-sto-form data-loading @if($submitted && $sto) data-reload-on-cancel @endif>
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="_modal" value="{{ $modalId }}">
    <input type="hidden" name="_form" value="{{ $formName }}">

    @if($bag->any())
        <div class="alert alert-danger" role="alert">
            <x-icon name="alert" :size="20" />
            <div>
                <strong>Periksa isian berikut:</strong>
                <ul>@foreach($bag->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <div class="form-grid">
        <div class="field">
            <label class="field-label" for="{{ $formId }}-number">Nomor STO</label>
            <input class="input {{ $bag->has('number') ? 'is-invalid' : '' }}" id="{{ $formId }}-number" name="number" value="{{ $val('number', $sto?->number ?? '') }}" maxlength="40" required autocomplete="off">
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-date">Tanggal</label>
            <input class="input {{ $bag->has('document_date') ? 'is-invalid' : '' }}" id="{{ $formId }}-date" type="date" name="document_date" value="{{ $val('document_date', $sto?->document_date?->format('Y-m-d') ?? '') }}" required>
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-activity">Kegiatan</label>
            @php $activity = $val('activity', $sto?->activity ?? ''); @endphp
            <select class="select {{ $bag->has('activity') ? 'is-invalid' : '' }}" id="{{ $formId }}-activity" name="activity" required>
                <option value="" @selected($activity === '')>Pilih</option>
                <option value="BONGKAR" @selected($activity === 'BONGKAR')>BONGKAR</option>
                <option value="MUAT" @selected($activity === 'MUAT')>MUAT</option>
            </select>
        </div>
        <div class="field"></div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-soj">SOJ <span class="optional">(opsional)</span></label>
            <input class="input {{ $bag->has('soj') ? 'is-invalid' : '' }}" id="{{ $formId }}-soj" name="soj" value="{{ $val('soj', $sto?->soj ?? '') }}" maxlength="100" autocomplete="off">
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-mmj">MMJ <span class="optional">(opsional)</span></label>
            <input class="input {{ $bag->has('mmj') ? 'is-invalid' : '' }}" id="{{ $formId }}-mmj" name="mmj" value="{{ $val('mmj', $sto?->mmj ?? '') }}" maxlength="100" autocomplete="off">
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-origin">Asal</label>
            <textarea class="textarea {{ $bag->has('origin') ? 'is-invalid' : '' }}" id="{{ $formId }}-origin" name="origin" rows="3" maxlength="1000" required>{{ $val('origin', $sto?->origin ?? '') }}</textarea>
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-destination">Tujuan</label>
            <textarea class="textarea {{ $bag->has('destination') ? 'is-invalid' : '' }}" id="{{ $formId }}-destination" name="destination" rows="3" maxlength="1000" required>{{ $val('destination', $sto?->destination ?? '') }}</textarea>
        </div>
    </div>

    <div class="items-block">
        <div class="items-head">
            <h3>Barang</h3>
            <button type="button" class="btn btn-secondary btn-sm" data-item-add><x-icon name="plus" :size="16" />Tambah baris</button>
        </div>
        <datalist id="{{ $formId }}-units"><option value="TON"></option><option value="KG"></option><option value="ZAK"></option><option value="PCS"></option></datalist>
        <div class="item-list" data-item-list>
            @foreach($items as $i => $item)
                @include('sto._item_row', ['i' => $i, 'item' => $item, 'bag' => $bag, 'rid' => $formId])
            @endforeach
        </div>
        <template data-item-template>
            @include('sto._item_row', ['i' => '__INDEX__', 'item' => ['name' => '', 'quantity' => '', 'unit' => 'TON', 'rate' => ''], 'bag' => new \Illuminate\Support\MessageBag(), 'rid' => $formId])
        </template>
        <div class="items-total"><span>Total</span><strong>Rp <span data-total>0</span></strong></div>
    </div>
</form>
```


## resources/views/sto/_item_row.blade.php

```text
{{-- Satu baris barang pada form STO. Dipakai untuk baris awal dan sebagai template "Tambah barang". --}}
@php
    $fieldError = fn (string $field) => $bag->first("items.$i.$field");
    $uid = $rid.'-i'.$i;
@endphp
<div class="item-row" data-item-row>
    <div class="field item-name">
        <label class="field-label" for="{{ $uid }}-name">Nama barang</label>
        <input class="input {{ $fieldError('name') ? 'is-invalid' : '' }}" id="{{ $uid }}-name" name="items[{{ $i }}][name]" value="{{ $item['name'] ?? '' }}" maxlength="255" required placeholder="Contoh: Urea Prill @50 KG">
        @if($fieldError('name'))<p class="field-error">{{ $fieldError('name') }}</p>@endif
    </div>
    <div class="field">
        <label class="field-label" for="{{ $uid }}-qty">Qty</label>
        <input class="input {{ $fieldError('quantity') ? 'is-invalid' : '' }}" id="{{ $uid }}-qty" type="number" step="any" min="0" inputmode="decimal" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? '' }}" required data-q>
        @if($fieldError('quantity'))<p class="field-error">{{ $fieldError('quantity') }}</p>@endif
    </div>
    <div class="field">
        <label class="field-label" for="{{ $uid }}-unit">Satuan</label>
        <input class="input {{ $fieldError('unit') ? 'is-invalid' : '' }}" id="{{ $uid }}-unit" list="{{ $rid }}-units" name="items[{{ $i }}][unit]" value="{{ $item['unit'] ?? '' }}" maxlength="20" required>
        @if($fieldError('unit'))<p class="field-error">{{ $fieldError('unit') }}</p>@endif
    </div>
    <div class="field">
        <label class="field-label" for="{{ $uid }}-rate">Tarif</label>
        <input class="input {{ $fieldError('rate') ? 'is-invalid' : '' }}" id="{{ $uid }}-rate" type="number" step="any" min="0" inputmode="decimal" name="items[{{ $i }}][rate]" value="{{ $item['rate'] ?? '' }}" required data-r>
        @if($fieldError('rate'))<p class="field-error">{{ $fieldError('rate') }}</p>@endif
    </div>
    <div class="field item-amount">
        <span class="field-label">Biaya</span>
        <output class="amount" data-amount>0</output>
    </div>
    <button type="button" class="icon-btn item-remove" data-item-remove aria-label="Hapus baris"><x-icon name="trash" :size="18" /></button>
</div>
```


## resources/views/sto/_modal.blade.php

```text
{{--
    Modal detail satu STO: tampilan detail, isian SOJ/MMJ, ubah penuh (edit), dan hapus.
    Parameter: $sto (dengan relasi items dan batch)
--}}
@php
    $id = 'sto-'.$sto->id;
    $submitted = old('_modal') === $id;
    $formName = $submitted ? old('_form') : null;
    $editing = $formName === 'edit';
    $refSubmitted = $formName === 'references';
    $refBag = $refSubmitted ? $errors->getBag('sto') : new \Illuminate\Support\MessageBag();
    $autoOpen = $submitted || session('reopen') === $id || (string) request('open') === (string) $sto->id;
    $missing = $sto->missingReferences();
    $rupiah = fn ($v) => number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => number_format((float) $v, 3, ',', '.');
    $hasPdf = $sto->batch && $sto->source_page;
@endphp
<dialog class="modal modal-lg {{ $editing ? 'is-editing' : '' }}" id="{{ $id }}" aria-labelledby="{{ $id }}-title" @if($autoOpen) data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon {{ $sto->activity === 'BONGKAR' ? 'tone-blue' : 'tone-teal' }}"><x-icon name="file" :size="20" /></span>
        <div class="titles">
            <h2 id="{{ $id }}-title">STO {{ $sto->number }} <span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></h2>
            <p>
                <span data-view>@if($hasPdf)PDF {{ $sto->batch->original_name }} · hal. {{ $sto->source_page }}@else Input manual @endif</span>
                <span data-edit>Ubah data STO</span>
            </p>
        </div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>

    <div class="modal-body">
        {{-- ===== Tampilan detail ===== --}}
        <div data-view class="view-stack">
            @if($missing)
                <div class="alert alert-warn" role="alert">
                    <x-icon name="alert" :size="20" />
                    <p><strong>{{ implode(' dan ', $missing) }} masih kosong.</strong> Isi di bawah.</p>
                </div>
            @endif

            <section class="panel {{ $missing ? 'panel-warn' : '' }}" aria-labelledby="{{ $id }}-refs-title">
                <h3 id="{{ $id }}-refs-title">SOJ &amp; MMJ</h3>
                <form method="post" action="{{ route('sto.references', $sto) }}" data-loading>
                    @csrf @method('PATCH')
                    <input type="hidden" name="_modal" value="{{ $id }}">
                    <input type="hidden" name="_form" value="references">
                    @if($refBag->any())
                        <div class="alert alert-danger" role="alert">
                            <x-icon name="alert" :size="20" />
                            <ul>@foreach($refBag->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="form-grid">
                        <div class="field">
                            <label class="field-label" for="{{ $id }}-soj">SOJ</label>
                            <input class="input {{ $refBag->has('soj') ? 'is-invalid' : '' }}" id="{{ $id }}-soj" name="soj" value="{{ $refSubmitted ? old('soj', '') : $sto->soj }}" maxlength="100" placeholder="Belum diisi" autocomplete="off">
                        </div>
                        <div class="field">
                            <label class="field-label" for="{{ $id }}-mmj">MMJ</label>
                            <input class="input {{ $refBag->has('mmj') ? 'is-invalid' : '' }}" id="{{ $id }}-mmj" name="mmj" value="{{ $refSubmitted ? old('mmj', '') : $sto->mmj }}" maxlength="100" placeholder="Belum diisi" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="16" /><span>Simpan</span></button>
                    </div>
                </form>
            </section>

            <div class="detail-cols">
                <section class="panel">
                    <h3>Informasi</h3>
                    <dl class="dl">
                        <div><dt>Tanggal</dt><dd class="num">{{ $sto->document_date->format('d/m/Y') }}</dd></div>
                        <div><dt>Kegiatan</dt><dd>{{ $sto->activity }}</dd></div>
                        <div><dt>Sumber</dt><dd>@if($hasPdf){{ $sto->batch->original_name }} (hal. {{ $sto->source_page }})@else Input manual @endif</dd></div>
                        <div><dt>Terakhir diubah</dt><dd class="num">{{ $sto->updated_at->format('d/m/Y H:i') }}</dd></div>
                    </dl>
                </section>
                <section class="panel">
                    <h3>Rute</h3>
                    <div class="route-line">
                        <div><small>Asal</small><p>{{ $sto->origin }}</p></div>
                        <div><small>Tujuan</small><p>{{ $sto->destination }}</p></div>
                    </div>
                </section>
            </div>

            <section aria-labelledby="{{ $id }}-items-title">
                <h3 class="section-title" id="{{ $id }}-items-title">Barang <span class="count-pill">{{ $sto->items->count() }} baris</span></h3>
                <div class="table-card">
                    <div class="table-scroll" tabindex="0" role="region" aria-label="Barang STO {{ $sto->number }}">
                        <table class="data-table compact stack-table">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col" class="ta-r">Qty</th>
                                    <th scope="col">Satuan</th>
                                    <th scope="col" class="ta-r">Tarif</th>
                                    <th scope="col" class="ta-r">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sto->items as $item)
                                    <tr>
                                        <td data-label="No" class="col-no num">{{ $item->line }}</td>
                                        <td data-label="Nama Barang" class="name">{{ $item->name }}</td>
                                        <td data-label="Qty" class="num ta-r">{{ $qty($item->quantity) }}</td>
                                        <td data-label="Satuan">{{ $item->unit }}</td>
                                        <td data-label="Tarif" class="num ta-r">{{ $rupiah($item->rate) }}</td>
                                        <td data-label="Biaya" class="num ta-r">{{ $rupiah($item->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row" colspan="5">Total</th>
                                    <td class="total ta-r" data-label="Total">Rp {{ $rupiah($sto->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        {{-- ===== Form ubah ===== --}}
        <div data-edit>
            @include('sto._form', [
                'formId' => 'form-'.$id,
                'action' => route('sto.update', $sto),
                'method' => 'PUT',
                'sto' => $sto,
                'modalId' => $id,
                'formName' => 'edit',
                'submitted' => $editing,
            ])
        </div>
    </div>

    {{-- ===== Footer: tampilan detail ===== --}}
    <div class="modal-foot" data-view>
        <div class="foot-main">
            <button type="button" class="btn btn-danger-soft" data-delete-toggle><x-icon name="trash" :size="18" />Hapus</button>
            <div class="foot-actions">
                @if($hasPdf)
                    <a class="btn btn-secondary" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="18" />Buka PDF</a>
                @endif
                <button type="button" class="btn btn-primary" data-edit-toggle><x-icon name="edit" :size="18" />Ubah</button>
            </div>
        </div>
        <div class="foot-confirm">
            <p><strong>Hapus STO {{ $sto->number }}?</strong> Data dan rincian barangnya terhapus permanen.</p>
            <div class="foot-actions">
                <button type="button" class="btn btn-secondary" data-delete-toggle>Batal</button>
                <form method="post" action="{{ route('sto.destroy', $sto) }}" data-loading>
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" data-submit data-loading-text="Menghapus…"><x-icon name="trash" :size="18" /><span>Ya, Hapus</span></button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Footer: ubah ===== --}}
    <div class="modal-foot" data-edit>
        <p class="note">Biaya = Qty × Tarif, dihitung otomatis.</p>
        <div class="foot-actions">
            <button type="button" class="btn btn-secondary" data-edit-cancel>Batal</button>
            <button type="submit" form="form-{{ $id }}" class="btn btn-primary" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan</span></button>
        </div>
    </div>
</dialog>
```


## resources/views/sto/index.blade.php

```text
@extends('layouts.app')
@section('title', 'Master STO')
@section('local_errors', '1')

@section('content')
@php
    $hasFilter = request()->filled('q') || request()->filled('activity') || request()->filled('completeness');
    $rupiah = fn ($v) => number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => number_format((float) $v, 3, ',', '.');
    $date = fn ($v) => \Illuminate\Support\Carbon::parse($v)->format('d/m/Y');
    $createSubmitted = old('_modal') === 'create-sto';
@endphp

<div class="page-head">
    <div>
        <h1>Master STO</h1>
        <p class="lead">Impor STO dari PDF atau tambah manual, lalu lengkapi SOJ dan MMJ.</p>
    </div>
</div>

{{-- ============ Unggah PDF ============ --}}
<section class="card" aria-labelledby="upload-title">
    <h2 class="sr-only" id="upload-title">Unggah PDF STO</h2>

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin-bottom:20px">
            <x-icon name="alert" :size="20" />
            <div>
                <strong>PDF gagal diproses</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <form method="post" action="{{ route('sto.upload') }}" enctype="multipart/form-data" data-loading data-upload-form>
        @csrf
        <div class="upload-grid">
            <label class="dropzone" data-dropzone for="pdf">
                <input class="sr-only" type="file" id="pdf" name="pdf" accept="application/pdf,.pdf" required aria-label="Pilih PDF STO">
                <span class="dz-icon"><x-icon name="upload" :size="28" /></span>
                <span class="dz-title" data-dz-title>Pilih PDF STO</span>
                <span class="dz-hint" data-dz-hint>PDF teks (bukan scan), maks. 10 MB / 200 halaman</span>
                <span class="btn btn-secondary btn-sm dz-btn">Pilih file atau seret ke sini</span>
            </label>

            <div class="upload-side">
                <ol class="steps">
                    <li><div><strong>Unggah PDF</strong><br><span class="hint">Satu file boleh berisi banyak STO.</span></div></li>
                    <li><div><strong>Periksa pratinjau</strong><br><span class="hint">Data belum tersimpan pada tahap ini.</span></div></li>
                    <li><div><strong>Simpan</strong><br><span class="hint">Nomor STO yang sudah ada dilewati.</span></div></li>
                </ol>

                <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit data-loading-text="Membaca PDF…">
                    <x-icon name="eye" :size="18" /><span>Pratinjau</span>
                </button>
            </div>
        </div>
    </form>
</section>

{{-- ============ Daftar STO ============ --}}
<div class="section-head">
    <h2>Daftar STO <span class="count-pill">{{ number_format($stos->total(), 0, ',', '.') }} STO</span></h2>
    <div class="head-actions">
        <a class="btn btn-secondary" href="{{ route('sto.export') }}"><x-icon name="download" :size="18" />Unduh CSV</a>
        <button type="button" class="btn btn-primary" data-dialog-open="create-sto"><x-icon name="plus" :size="18" />Tambah STO</button>
    </div>
</div>

@if($incompleteCount > 0 && request('completeness') !== 'incomplete')
    <div class="alert alert-warn" role="status">
        <x-icon name="alert" :size="20" />
        <p><strong>{{ number_format($incompleteCount, 0, ',', '.') }} STO belum lengkap.</strong> SOJ atau MMJ masih kosong. <a href="{{ route('sto.index', ['completeness' => 'incomplete']) }}">Lihat daftar</a></p>
    </div>
@endif

<form class="filters" method="get" action="{{ route('sto.index') }}" role="search">
    <div class="input-icon">
        <x-icon name="search" :size="18" />
        <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nomor, asal, tujuan, SOJ, MMJ" aria-label="Cari nomor, asal, tujuan, SOJ, atau MMJ" maxlength="100">
    </div>
    <select class="select" name="activity" aria-label="Filter kegiatan">
        <option value="">Semua kegiatan</option>
        @foreach(['BONGKAR', 'MUAT'] as $v)
            <option value="{{ $v }}" @selected(request('activity') === $v)>{{ $v }}</option>
        @endforeach
    </select>
    <select class="select" name="completeness" aria-label="Filter kelengkapan SOJ dan MMJ">
        <option value="">Semua kelengkapan</option>
        <option value="incomplete" @selected(request('completeness') === 'incomplete')>SOJ/MMJ belum lengkap</option>
        <option value="complete" @selected(request('completeness') === 'complete')>SOJ/MMJ lengkap</option>
    </select>
    <button type="submit" class="btn btn-navy">Cari</button>
    @if($hasFilter)
        <a class="btn btn-ghost" href="{{ route('sto.index') }}"><x-icon name="rotate" :size="16" />Reset</a>
    @endif
</form>

@if($stos->isEmpty())
    <div class="table-card">
        <div class="empty">
            <span class="chip-icon"><x-icon name="inbox" :size="26" /></span>
            @if($hasFilter)
                <h3>Tidak ada STO yang cocok</h3>
                <p>Ubah kata kunci atau reset filter.</p>
                <a class="btn btn-secondary btn-sm" href="{{ route('sto.index') }}" style="margin-top:10px">Reset filter</a>
            @else
                <h3>Belum ada STO</h3>
                <p>Unggah PDF di atas atau tambah STO manual.</p>
                <button type="button" class="btn btn-primary btn-sm" data-dialog-open="create-sto" style="margin-top:10px"><x-icon name="plus" :size="16" />Tambah STO</button>
            @endif
        </div>
    </div>
@else
    {{-- Tabel (desktop) --}}
    <div class="table-card desk-only">
        <div class="table-scroll" tabindex="0" role="region" aria-label="Tabel STO">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nomor STO</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Kegiatan</th>
                        <th scope="col">Asal</th>
                        <th scope="col">Tujuan</th>
                        <th scope="col">Nama Barang</th>
                        <th scope="col" class="ta-r">Qty</th>
                        <th scope="col">Satuan</th>
                        <th scope="col" class="ta-r">Tarif</th>
                        <th scope="col" class="ta-r">Total</th>
                        <th scope="col">SOJ / MMJ</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                @foreach($stos as $sto)
                    @php
                        $items = $sto->items->values();
                        $span = max(1, $items->count());
                        $no = $stos->firstItem() + $loop->index;
                    @endphp
                    <tbody class="group">
                        @for($i = 0; $i < $span; $i++)
                            @php $item = $items[$i] ?? null; @endphp
                            <tr>
                                @if($i === 0)
                                    <td rowspan="{{ $span }}" class="col-no num">{{ $no }}</td>
                                    <td rowspan="{{ $span }}"><button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button></td>
                                    <td rowspan="{{ $span }}" class="num" style="white-space:nowrap">{{ $sto->document_date->format('d/m/Y') }}</td>
                                    <td rowspan="{{ $span }}"><span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></td>
                                    <td rowspan="{{ $span }}" class="place"><div class="clamp" title="{{ $sto->origin }}">{{ $sto->origin }}</div></td>
                                    <td rowspan="{{ $span }}" class="place"><div class="clamp" title="{{ $sto->destination }}">{{ $sto->destination }}</div></td>
                                @endif
                                <td class="item name">{{ $item?->name ?? '—' }}</td>
                                <td class="item ta-r">{{ $item ? $qty($item->quantity) : '—' }}</td>
                                <td class="item">{{ $item?->unit ?? '—' }}</td>
                                <td class="item ta-r">{{ $item ? $rupiah($item->rate) : '—' }}</td>
                                @if($i === 0)
                                    <td rowspan="{{ $span }}" class="total ta-r">{{ $rupiah($sto->total) }}</td>
                                    <td rowspan="{{ $span }}" class="refs">
                                        <div class="ref"><small>SOJ</small>@if(filled($sto->soj))<span>{{ $sto->soj }}</span>@else<span class="badge badge-amber">Kosong</span>@endif</div>
                                        <div class="ref"><small>MMJ</small>@if(filled($sto->mmj))<span>{{ $sto->mmj }}</span>@else<span class="badge badge-amber">Kosong</span>@endif</div>
                                    </td>
                                    <td rowspan="{{ $span }}">
                                        <div class="row-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dialog-open="sto-{{ $sto->id }}">Detail</button>
                                            @if($sto->batch && $sto->source_page)
                                                <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="15" />PDF</a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>

    {{-- Kartu (HP & tablet kecil) --}}
    <div class="sto-cards" aria-label="Daftar STO">
        @foreach($stos as $sto)
            <article class="sto-card">
                <div class="top">
                    <button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button>
                    <span class="date num">{{ $sto->document_date->format('d/m/Y') }}</span>
                </div>
                <div class="badges">
                    <span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span>
                    @if($sto->missingReferences())
                        <span class="badge badge-amber">{{ implode(' & ', $sto->missingReferences()) }} kosong</span>
                    @endif
                </div>
                <div class="route">
                    <div><small>Asal</small><p>{{ $sto->origin }}</p></div>
                    <div><small>Tujuan</small><p>{{ $sto->destination }}</p></div>
                </div>
                <div class="route">
                    <div><small>SOJ</small><p>{{ filled($sto->soj) ? $sto->soj : '—' }}</p></div>
                    <div><small>MMJ</small><p>{{ filled($sto->mmj) ? $sto->mmj : '—' }}</p></div>
                </div>
                <ul class="items">
                    @foreach($sto->items as $item)
                        <li>
                            <b>{{ $item->name }}</b>
                            <span>{{ $qty($item->quantity) }} {{ $item->unit }}<br>× {{ $rupiah($item->rate) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="sum"><span>Total</span><strong>Rp {{ $rupiah($sto->total) }}</strong></div>
                <div class="actions">
                    <button type="button" class="btn btn-secondary btn-sm" data-dialog-open="sto-{{ $sto->id }}">Detail</button>
                    @if($sto->batch && $sto->source_page)
                        <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="15" />PDF</a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    {{ $stos->links() }}
@endif

{{-- ============ Modal tambah STO ============ --}}
<dialog class="modal modal-lg is-editing" id="create-sto" data-always-edit aria-labelledby="create-sto-title" @if($createSubmitted) data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon tone-orange"><x-icon name="plus" :size="20" /></span>
        <div class="titles">
            <h2 id="create-sto-title">Tambah STO</h2>
            <p>SOJ dan MMJ boleh diisi nanti.</p>
        </div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>
    <div class="modal-body">
        @include('sto._form', [
            'formId' => 'form-create-sto',
            'action' => route('sto.store'),
            'method' => 'POST',
            'sto' => null,
            'modalId' => 'create-sto',
            'formName' => 'create',
            'submitted' => $createSubmitted,
        ])
    </div>
    <div class="modal-foot">
        <p class="note">Biaya = Qty × Tarif, dihitung otomatis.</p>
        <div class="foot-actions">
            <button type="button" class="btn btn-secondary" data-dialog-close>Batal</button>
            <button type="submit" form="form-create-sto" class="btn btn-primary" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan</span></button>
        </div>
    </div>
</dialog>

{{-- ============ Modal detail tiap STO ============ --}}
@foreach($dialogStos as $dialogSto)
    @include('sto._modal', ['sto' => $dialogSto])
@endforeach

{{-- ============ Modal pratinjau impor ============ --}}
@isset($batch)
@php
    $newCount = collect($rows)->where('duplicate', false)->count();
    $dupCount = collect($rows)->where('duplicate', true)->count();
@endphp
<dialog class="modal" id="preview-modal" open autofocus data-autoopen data-persistent aria-labelledby="preview-title">
    <div class="modal-head">
        <span class="chip-icon"><x-icon name="file" :size="20" /></span>
        <div class="titles">
            <h2 id="preview-title">Pratinjau Impor</h2>
            <p title="{{ $batch->original_name }}">{{ $batch->original_name }}</p>
        </div>
        <button type="submit" form="cancel-form" class="icon-btn" aria-label="Tutup dan batalkan impor"><x-icon name="x" :size="20" /></button>
    </div>

    <div class="modal-body">
        <div class="summary">
            <div><span class="chip-icon tone-blue"><x-icon name="layers" :size="20" /></span><div><strong>{{ count($rows) }}</strong><span>STO terbaca</span></div></div>
            <div><span class="chip-icon tone-green"><x-icon name="check-circle" :size="20" /></span><div><strong>{{ $newCount }}</strong><span>Akan disimpan</span></div></div>
            <div><span class="chip-icon {{ $dupCount ? 'tone-amber' : 'tone-teal' }}"><x-icon :name="$dupCount ? 'alert' : 'shield'" :size="20" /></span><div><strong>{{ $dupCount }}</strong><span>Duplikat (dilewati)</span></div></div>
        </div>

        <div class="alert alert-info">
            <x-icon name="info" :size="20" />
            <p>Data belum tersimpan. Periksa hasil bacaan, lalu klik <strong>Simpan</strong>.</p>
        </div>

        @if(count($batch->warnings))
            <div class="alert alert-danger" role="alert">
                <x-icon name="alert" :size="20" />
                <div>
                    <strong>Gagal dibaca (tidak disimpan):</strong>
                    <ul>@foreach($batch->warnings as $w)<li>{{ $w }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        <div class="table-card">
            <div class="table-scroll" tabindex="0" role="region" aria-label="Hasil bacaan PDF">
                <table class="data-table compact stack-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nomor STO</th>
                            <th scope="col">Hal.</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Kegiatan</th>
                            <th scope="col">Rute</th>
                            <th scope="col">Barang</th>
                            <th scope="col" class="ta-r">Total</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="{{ $row['duplicate'] ? 'is-dup' : '' }}">
                                <td data-label="No" class="col-no num">{{ $loop->iteration }}</td>
                                <td data-label="Nomor STO"><span class="sto-no">{{ $row['number'] }}</span></td>
                                <td data-label="Halaman" class="num">{{ $row['source_page'] }}</td>
                                <td data-label="Tanggal" class="num" style="white-space:nowrap">{{ $date($row['document_date']) }}</td>
                                <td data-label="Kegiatan"><span class="badge {{ $row['activity'] === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $row['activity'] }}</span></td>
                                <td data-label="Rute" class="route-cell">
                                    <div>
                                        <div><small>Asal</small><span class="addr">{{ $row['origin'] }}</span></div>
                                        <div class="to"><small>Tujuan</small><span class="addr">{{ $row['destination'] }}</span></div>
                                    </div>
                                </td>
                                <td data-label="Barang">
                                    <div class="line-items">
                                        @foreach($row['items'] as $item)
                                            <div class="li">
                                                <strong>{{ $item['name'] }}</strong>
                                                <span>{{ $qty($item['quantity']) }} {{ $item['unit'] }} × {{ $rupiah($item['rate']) }} = {{ $rupiah($item['amount']) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td data-label="Total" class="total ta-r">{{ $rupiah($row['total']) }}</td>
                                <td data-label="Status">
                                    <div>
                                        <span class="badge {{ $row['duplicate'] ? 'badge-amber' : 'badge-green' }}">{{ $row['duplicate'] ? 'Duplikat' : 'Baru' }}</span>
                                        @foreach($row['notes'] as $note)<p class="notes">{{ $note }}</p>@endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-foot">
        <p class="note">Berlaku sampai {{ $batch->expires_at->format('d/m/Y H:i') }}. Menutup jendela ini membatalkan impor.</p>
        <div class="foot-actions">
            <form id="cancel-form" method="post" action="{{ route('sto.cancel', $batch) }}" data-loading>
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-secondary" data-submit data-loading-text="Membatalkan…">Batalkan</button>
            </form>
            <form id="commit-form" method="post" action="{{ route('sto.commit', $batch) }}" data-loading>
                @csrf
                <button type="submit" class="btn btn-primary" data-submit data-loading-text="Menyimpan…" @disabled($newCount === 0)>
                    <x-icon name="check" :size="18" /><span>{{ $newCount === 0 ? 'Tidak ada STO baru' : 'Simpan '.$newCount.' STO' }}</span>
                </button>
            </form>
        </div>
    </div>
</dialog>
@endisset
@endsection
```


## routes/console.php

```text
<?php
use Illuminate\Support\Facades\{Artisan,DB,Storage,Schedule};
use App\Models\ImportBatch;
Artisan::command('sto:cleanup',function(){
 $count=0;ImportBatch::where('status','preview')->where('expires_at','<',now())->pluck('id')->each(function($id)use(&$count){DB::transaction(function()use($id,&$count){$b=ImportBatch::whereKey($id)->lockForUpdate()->first();if($b&&$b->status==='preview'&&$b->expires_at->isPast()){Storage::delete($b->path);$b->delete();$count++;}});});
 $this->info("$count pratinjau kedaluwarsa dibersihkan.");
})->purpose('Hapus PDF sementara yang tidak disimpan setelah 24 jam.');
Schedule::command('sto:cleanup')->daily();

```


## routes/web.php

```text
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,DashboardController,StoController};
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'login'])->name('login');Route::post('/login',[AuthController::class,'authenticate'])->name('login.submit')->middleware('throttle:20,1');});
Route::middleware('auth')->group(function(){
 Route::get('/',DashboardController::class)->name('dashboard');Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::view('/akun','auth.account')->name('account');Route::put('/akun/password',[AuthController::class,'password'])->name('password.update');
 Route::get('/sto',[StoController::class,'index'])->name('sto.index');Route::post('/sto',[StoController::class,'store'])->name('sto.store');
 Route::post('/sto/upload',[StoController::class,'upload'])->name('sto.upload')->middleware('throttle:10,1');
 Route::get('/sto/export',[StoController::class,'export'])->name('sto.export');
 Route::get('/impor/{batch}/preview',[StoController::class,'preview'])->name('sto.preview');Route::post('/impor/{batch}/simpan',[StoController::class,'commit'])->name('sto.commit');Route::delete('/impor/{batch}',[StoController::class,'cancel'])->name('sto.cancel');
 Route::get('/sto/{sto}',[StoController::class,'show'])->name('sto.show');Route::put('/sto/{sto}',[StoController::class,'update'])->name('sto.update');Route::delete('/sto/{sto}',[StoController::class,'destroy'])->name('sto.destroy');
 Route::patch('/sto/{sto}/referensi',[StoController::class,'references'])->name('sto.references');Route::get('/sto/{sto}/pdf',[StoController::class,'pdf'])->name('sto.pdf');
});

```


## storage/app/private/.gitignore

```text
*
!.gitignore

```


## storage/framework/cache/data/.gitignore

```text
*
!.gitignore

```


## storage/framework/sessions/.gitignore

```text
*
!.gitignore

```


## storage/framework/views/.gitignore

```text
*
!.gitignore

```


## storage/logs/.gitignore

```text
*
!.gitignore

```


## tests/Feature/StoFlowTest.php

```text
<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\{User,ImportBatch,Sto};
use App\Services\StoPdfParser;
class StoFlowTest extends TestCase {
 use RefreshDatabase;
 private function user(string $name='admin'): User {return User::create(['name'=>$name,'username'=>$name,'email'=>$name.'@example.com','password'=>'SecureExample123!']);}
 private function batch(User $user): ImportBatch {return ImportBatch::create(['user_id'=>$user->id,'original_name'=>'test.pdf','path'=>'sto/test.pdf','sha256'=>str_repeat('a',64),'warnings'=>[],'payload'=>[$this->row(),$this->row()],'expires_at'=>now()->addDay()]);}
 private function row(): array {return ['number'=>'1234567890','document_date'=>'2026-01-05','activity'=>'BONGKAR','origin'=>'Gresik','destination'=>'Malang','total'=>'200.00','source_page'=>1,'notes'=>[],'items'=>[['line'=>1,'name'=>'Urea','quantity'=>'2.000','unit'=>'TON','rate'=>'100.00','amount'=>'200.00']]];}
 public function test_guest_cannot_access_master_or_pdf(): void {$this->get('/sto')->assertRedirect('/login');$this->get('/sto/1/pdf')->assertRedirect('/login');}
 public function test_login_and_logout(): void {$u=$this->user();$this->post('/login',['username'=>$u->username,'password'=>'SecureExample123!'])->assertRedirect('/');$this->assertAuthenticatedAs($u);$this->post('/logout')->assertRedirect('/login');$this->assertGuest();}
 public function test_login_throttles_wrong_passwords(): void {$u=$this->user();for($i=0;$i<6;$i++)$this->post('/login',['username'=>$u->username,'password'=>'wrong'])->assertSessionHasErrors('username');$this->assertGuest();}
 public function test_upload_is_preview_only(): void {
  Storage::fake('local');$u=$this->user();$this->mock(StoPdfParser::class,fn($m)=>$m->shouldReceive('parse')->once()->andReturn(['rows'=>[$this->row()],'warnings'=>[]]));
  $this->actingAs($u)->post('/sto/upload',['pdf'=>UploadedFile::fake()->createWithContent('test.pdf',"%PDF-1.4\n%%EOF")])->assertRedirect();$this->assertDatabaseCount('stos',0);$this->assertDatabaseCount('import_batches',1);
 }
 public function test_duplicate_commit_and_repeat_submission(): void {
  $u=$this->user();$batch=$this->batch($u);$this->actingAs($u)->get(route('sto.preview',$batch))->assertOk()->assertSee('Duplikat');
  $this->post(route('sto.commit',$batch))->assertRedirect(route('sto.index'));$this->assertDatabaseCount('stos',1);$this->assertDatabaseCount('sto_items',1);
  $this->post(route('sto.commit',$batch))->assertStatus(410);$batch2=$this->batch($u);$this->post(route('sto.commit',$batch2))->assertRedirect();$this->assertDatabaseCount('stos',1);
 }
 public function test_cannot_commit_another_users_preview(): void {$owner=$this->user();$batch=$this->batch($owner);$this->actingAs($this->user('other'))->post(route('sto.commit',$batch))->assertForbidden();}
 public function test_expired_preview_cannot_be_saved(): void {$u=$this->user();$batch=$this->batch($u);$batch->update(['expires_at'=>now()->subMinute()]);$this->actingAs($u)->post(route('sto.commit',$batch))->assertStatus(410);$this->assertDatabaseCount('stos',0);}
 public function test_dashboard_list_and_details_render(): void {$u=$this->user();$b=$this->batch($u);$this->actingAs($u)->post(route('sto.commit',$b));$this->get('/')->assertOk()->assertSee('1234567890');$this->get('/sto')->assertOk()->assertSee('Urea');$this->get(route('sto.show',Sto::first()))->assertRedirect(route('sto.index',['open'=>Sto::first()->id]));$this->get(route('sto.index',['open'=>Sto::first()->id]))->assertOk()->assertSee('id="sto-'.Sto::first()->id.'"',false);}
 public function test_cancel_removes_private_pdf(): void {Storage::fake('local');$u=$this->user();$b=$this->batch($u);Storage::put($b->path,'pdf');$this->actingAs($u)->delete(route('sto.cancel',$b))->assertRedirect();Storage::assertMissing($b->path);$this->assertDatabaseCount('import_batches',0);}
 public function test_preview_is_rendered_as_modal_on_master_page(): void {
  $u=$this->user();$batch=$this->batch($u);
  $this->actingAs($u)->get(route('sto.preview',$batch))->assertOk()->assertSee('id="preview-modal"',false)->assertSee('Simpan 1 STO')->assertSee('Master STO');
  $this->get(route('sto.index'))->assertOk()->assertDontSee('id="preview-modal"',false);
 }

 private function payload(array $override=[]): array {
  return array_replace([
   'number'=>'5320099001','document_date'=>'2026-10-05','activity'=>'MUAT','origin'=>"GD PKT PADIMAS 1\nGresik",'destination'=>"GD PKT LAMPUNG\nLampung",
   'soj'=>'','mmj'=>'',
   'items'=>[['name'=>'NPK 14-12-16','quantity'=>'84.6','unit'=>'ton','rate'=>'13100'],['name'=>'Urea','quantity'=>'10.125','unit'=>'TON','rate'=>'1000.50']],
  ],$override);
 }
 private function commitedSto(): array {$u=$this->user();$b=$this->batch($u);$this->actingAs($u)->post(route('sto.commit',$b));return [$u,Sto::firstOrFail()];}
 public function test_no_verification_or_usage_columns_remain(): void {
  $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('stos','used'));
  $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('stos','verified_at'));
  $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('stos','verified_by'));
  $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('stos','soj'));$this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('stos','mmj'));
  $this->assertFalse(\Illuminate\Support\Facades\Route::has('sto.verify'));$this->assertFalse(\Illuminate\Support\Facades\Route::has('sto.usage'));
 }
 public function test_imported_sto_has_empty_soj_mmj_and_modal_warns(): void {
  [$u,$sto]=$this->commitedSto();$this->assertNull($sto->soj);$this->assertNull($sto->mmj);
  $this->get(route('sto.index'))->assertOk()->assertSee('SOJ dan MMJ masih kosong.')->assertSee('1 STO belum lengkap.')->assertDontSee('Verifikasi')->assertDontSee('Pemakaian');
  $this->get('/')->assertOk()->assertSee('1 STO belum lengkap.')->assertDontSee('Verifikasi')->assertDontSee('Pemakaian');
 }
 public function test_soj_and_mmj_can_be_filled_and_warning_updates(): void {
  [$u,$sto]=$this->commitedSto();
  $this->patch(route('sto.references',$sto),['soj'=>'SOJ-001','mmj'=>'','_modal'=>'sto-'.$sto->id,'_form'=>'references'])->assertRedirect()->assertSessionHas('reopen','sto-'.$sto->id);
  $this->assertSame('SOJ-001',$sto->fresh()->soj);$this->assertNull($sto->fresh()->mmj);
  $this->get(route('sto.index'))->assertSee('MMJ masih kosong.')->assertDontSee('SOJ dan MMJ masih kosong.');
  $this->patch(route('sto.references',$sto),['soj'=>' SOJ-001 ','mmj'=>'MMJ-77'])->assertRedirect();
  $this->assertSame('MMJ-77',$sto->fresh()->mmj);$this->assertSame([],$sto->fresh()->missingReferences());
  $this->get(route('sto.index'))->assertDontSee('1 STO belum lengkap.');
 }
 public function test_references_reject_too_long_values_in_sto_bag(): void {
  [$u,$sto]=$this->commitedSto();
  $this->patch(route('sto.references',$sto),['soj'=>str_repeat('A',101)])->assertSessionHasErrors('soj',null,'sto');$this->assertNull($sto->fresh()->soj);
 }
 public function test_sto_can_be_created_manually_with_computed_totals(): void {
  $u=$this->user();
  $this->actingAs($u)->post(route('sto.store'),$this->payload())->assertRedirect(route('sto.index'));
  $sto=Sto::with('items')->firstOrFail();
  $this->assertSame('5320099001',$sto->number);$this->assertNull($sto->batch);$this->assertNull($sto->source_page);$this->assertNull($sto->soj);
  $this->assertCount(2,$sto->items);$this->assertSame('TON',$sto->items[0]->unit);
  $this->assertSame('1108260.00',(string)$sto->items[0]->amount);   // 84,6 x 13.100
  $this->assertSame('10130.06',(string)$sto->items[1]->amount);     // 10,125 x 1.000,50 = 10.130,0625 dibulatkan
  $this->assertSame('1118390.06',(string)$sto->total);
  $this->get(route('sto.index'))->assertOk()->assertSee('5320099001')->assertSee('Input manual');
 }
 public function test_create_validation_errors_use_sto_bag_and_keep_input(): void {
  $u=$this->user();
  $this->actingAs($u)->from(route('sto.index'))->post(route('sto.store'),$this->payload(['number'=>'','activity'=>'X','items'=>[['name'=>'','quantity'=>'0','unit'=>'','rate'=>'-1']],'_modal'=>'create-sto','_form'=>'create']))
   ->assertRedirect(route('sto.index'))->assertSessionHasErrors(['number','activity','items.0.name','items.0.quantity','items.0.unit','items.0.rate'],null,'sto')->assertSessionHasInput('_modal','create-sto');
  $this->assertDatabaseCount('stos',0);
  $this->post(route('sto.store'),$this->payload(['items'=>[]]))->assertSessionHasErrors('items',null,'sto');
  $this->post(route('sto.store'),$this->payload(['items'=>[['name'=>'A','quantity'=>'1.2345','unit'=>'TON','rate'=>'1']]]))->assertSessionHasErrors('items.0.quantity',null,'sto');
  $this->post(route('sto.store'),$this->payload(['number'=>'bad number!']))->assertSessionHasErrors('number',null,'sto');
  $this->post(route('sto.store'),$this->payload(['document_date'=>'31/12/2026']))->assertSessionHasErrors('document_date',null,'sto');
 }
 public function test_duplicate_number_is_rejected_on_create_and_update_but_own_number_is_allowed(): void {
  $u=$this->user();$this->actingAs($u)->post(route('sto.store'),$this->payload());
  $this->post(route('sto.store'),$this->payload())->assertSessionHasErrors('number',null,'sto');$this->assertDatabaseCount('stos',1);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320099002']));
  $first=Sto::where('number','5320099001')->firstOrFail();
  $this->put(route('sto.update',$first),$this->payload(['number'=>'5320099002']))->assertSessionHasErrors('number',null,'sto');
  $this->put(route('sto.update',$first),$this->payload(['activity'=>'BONGKAR']))->assertSessionHasNoErrors();$this->assertSame('BONGKAR',$first->fresh()->activity);
 }
 public function test_sto_can_be_edited_including_items_and_soj_mmj(): void {
  [$u,$sto]=$this->commitedSto();
  $this->put(route('sto.update',$sto),$this->payload(['number'=>'5320099555','soj'=>'S-1','mmj'=>'M-1','items'=>[['name'=>'Pupuk Z','quantity'=>'2','unit'=>'kg','rate'=>'250.25']]]))->assertRedirect()->assertSessionHas('reopen','sto-'.$sto->id);
  $sto=$sto->fresh()->load('items');
  $this->assertSame('5320099555',$sto->number);$this->assertSame('S-1',$sto->soj);$this->assertSame('M-1',$sto->mmj);
  $this->assertCount(1,$sto->items);$this->assertSame(1,$sto->items[0]->line);$this->assertSame('KG',$sto->items[0]->unit);
  $this->assertSame('500.50',(string)$sto->items[0]->amount);$this->assertSame('500.50',(string)$sto->total);
  $this->assertNotNull($sto->import_batch_id);$this->assertSame(1,(int)$sto->source_page); // asal PDF tetap
  $this->assertDatabaseCount('sto_items',1);
 }
 public function test_sto_can_be_deleted_with_items_cascade(): void {
  [$u,$sto]=$this->commitedSto();
  $this->delete(route('sto.destroy',$sto))->assertRedirect(route('sto.index'));
  $this->assertDatabaseCount('stos',0);$this->assertDatabaseCount('sto_items',0);$this->assertDatabaseCount('import_batches',1);
 }
 public function test_search_and_completeness_filters(): void {
  $u=$this->user();$this->actingAs($u);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000001','soj'=>'SOJ-AAA','mmj'=>'MMJ-AAA']));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000002','soj'=>'SOJ-BBB']));
  $this->get(route('sto.index')); // habiskan flash "reopen" seperti redirect di browser
  $this->get(route('sto.index',['completeness'=>'complete']))->assertSee('5320000001')->assertDontSee('5320000002');
  $this->get(route('sto.index',['completeness'=>'incomplete']))->assertSee('5320000002')->assertDontSee('5320000001');
  $this->get(route('sto.index',['q'=>'SOJ-BBB']))->assertSee('5320000002')->assertDontSee('5320000001');
 }
 public function test_csv_export_has_soj_mmj_and_no_status_columns(): void {
  $u=$this->user();$this->actingAs($u)->post(route('sto.store'),$this->payload(['soj'=>'SOJ-X','mmj'=>'MMJ-Y']));
  $csv=$this->get(route('sto.export'))->assertOk()->streamedContent();
  $this->assertStringContainsString('SOJ;MMJ',$csv);$this->assertStringContainsString('SOJ-X;MMJ-Y',$csv);
  $this->assertStringNotContainsString('Verifikasi',$csv);$this->assertStringNotContainsString('Pemakaian',$csv);
 }
 public function test_manual_sto_has_no_pdf_and_guests_cannot_write(): void {
  $u=$this->user();$this->actingAs($u)->post(route('sto.store'),$this->payload());$sto=Sto::firstOrFail();
  $this->get(route('sto.pdf',$sto))->assertNotFound();
  auth()->logout();
  $this->post(route('sto.store'),$this->payload())->assertRedirect('/login');
  $this->put(route('sto.update',$sto),$this->payload())->assertRedirect('/login');
  $this->patch(route('sto.references',$sto),['soj'=>'x'])->assertRedirect('/login');
  $this->delete(route('sto.destroy',$sto))->assertRedirect('/login');
 }
 public function test_messages_are_in_indonesian(): void {
  $u=$this->user();
  $this->post('/login',['username'=>'','password'=>''])->assertSessionHasErrors(['username'=>'Username wajib diisi.']);
  $this->actingAs($u)->post('/sto/upload',[])->assertSessionHasErrors(['pdf'=>'Pilih file PDF terlebih dahulu.']);
  $this->put(route('password.update'),['current_password'=>'salah','password'=>'pendek','password_confirmation'=>'lain'])
   ->assertSessionHasErrors(['current_password'=>'Password saat ini salah.']);
  $this->put(route('password.update'),['current_password'=>'SecureExample123!','password'=>'pendek','password_confirmation'=>'pendek'])
   ->assertSessionHasErrors(['password'=>'Password baru minimal 12 karakter.']);
 }
 public function test_commit_message_omits_zero_duplicates(): void {
  $u=$this->user();$b=ImportBatch::create(['user_id'=>$u->id,'original_name'=>'t.pdf','path'=>'sto/t.pdf','sha256'=>str_repeat('a',64),'warnings'=>[],'payload'=>[$this->row()],'expires_at'=>now()->addDay()]);
  $this->actingAs($u)->post(route('sto.commit',$b))->assertSessionHas('success','1 STO disimpan.');
 }
}
```


## tests/TestCase.php

```text
<?php
namespace Tests;
abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase {}

```


## tests/Unit/StoPdfParserTest.php

```text
<?php
namespace Tests\Unit;
use App\Services\StoPdfParser;
use PHPUnit\Framework\TestCase;
class StoPdfParserTest extends TestCase {
 public function test_actual_uploaded_pdf_extracts_62_orders_and_correct_decimal_values(): void {
  $result=(new StoPdfParser())->parse(__DIR__.'/../Fixtures/STO.pdf');
  $this->assertCount(62,$result['rows']);$this->assertSame([],$result['warnings']);
  $first=$result['rows'][0];$this->assertSame('MUAT',$first['activity']);$this->assertSame('5520078169',$first['number']);$this->assertSame('2026-01-05',$first['document_date']);$this->assertSame('Urea SUB @50 KG',$first['items'][0]['name']);$this->assertSame('185.000',$first['items'][0]['quantity']);$this->assertSame('2617750.00',$first['total']);
  $count=array_count_values(array_column($result['rows'],'activity'));$this->assertSame(52,$count['MUAT']);$this->assertSame(10,$count['BONGKAR']);
  $this->assertSame('5520078285',$result['rows'][11]['number']);$this->assertSame('BONGKAR',$result['rows'][11]['activity']);
  $third=$result['rows'][2];$this->assertSame('172.400',$third['items'][0]['quantity']);$this->assertSame('14150.00',$third['items'][0]['rate']);$this->assertSame(3,$third['source_page']);
 }
 public function test_one_order_with_multiple_items(): void {
  $r=(new StoPdfParser())->parseText($this->text());
  $this->assertCount(1,$r['rows']);$this->assertCount(2,$r['rows'][0]['items']);$this->assertSame('MUAT',$r['rows'][0]['activity']);$this->assertSame('800.00',$r['rows'][0]['total']);
 }
 public function test_two_orders_on_same_page(): void {
  $r=(new StoPdfParser())->parseText($this->text().str_replace('1234567890','1234567891',$this->text()));$this->assertCount(2,$r['rows']);
 }
 public function test_scan_or_unknown_format_is_rejected(): void {
  $this->expectException(\RuntimeException::class);(new StoPdfParser())->parseText('');
 }
 public function test_invalid_date_is_rejected(): void {
  $this->expectException(\RuntimeException::class);(new StoPdfParser())->parseText(str_replace('01.02.2026','31.02.2026',$this->text()));
 }
 public function test_activity_is_detected_per_order_from_the_document_sentence(): void {
  $bongkar=str_replace(['1234567890','Mohon dilaksanakan pemuatan barang berikut :'],['1234567891','Mohon dibongkar barang berikut :'],$this->text());
  $r=(new StoPdfParser())->parseText($this->text()."\n".$bongkar);
  $this->assertSame(['MUAT','BONGKAR'],array_column($r['rows'],'activity'));
 }
 public function test_order_without_activity_sentence_is_skipped_with_warning(): void {
  $unknown=str_replace(['1234567890','Mohon dilaksanakan pemuatan barang berikut :'],['1234567891','Mohon diproses barang berikut :'],$this->text());
  $r=(new StoPdfParser())->parseText($this->text()."\n".$unknown);
  $this->assertCount(1,$r['rows']);$this->assertSame('1234567890',$r['rows'][0]['number']);
  $this->assertCount(1,$r['warnings']);$this->assertStringContainsString('1234567891',$r['warnings'][0]);$this->assertStringContainsString('tidak ditemukan',$r['warnings'][0]);
 }
 public function test_sentence_with_both_words_is_rejected_as_ambiguous(): void {
  $this->expectException(\RuntimeException::class);
  (new StoPdfParser())->parseText(str_replace('Mohon dilaksanakan pemuatan barang berikut :','Mohon dibongkar dan dimuat barang berikut :',$this->text()));
 }
 public function test_indonesian_numbers(): void {
  $p=new StoPdfParser();$this->assertSame('420.750',$p->decimal('420,750',3));$this->assertSame('6942375.00',$p->decimal('6.942.375',2));
 }
 private function text(): string {return "[[PAGE:1]]\nNo. Po : 1234567890\nTanggal : 01.02.2026\nMohon dilaksanakan pemuatan barang berikut :\nAsal Barang : Pengelola Barang Asal :\nGudang Gresik\nTujuan Barang : Pengelola Barang Tujuan :\nGudang Malang\nModa : Truck\nNo. Nama Barang Qty Satuan Tarif Biaya\n1 Urea SUB @50 KG 2 TON 100 200\n2 NPK 3 TON 200 600\nTotal 800";}
}

```


