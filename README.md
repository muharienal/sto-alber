# STO Alber

## Instalasi

```bash
git clone https://github.com/muharienal/sto-alber.git
cd sto-alber
composer install
cp .env.example .env
php artisan key:generate
```

Buat database:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sto_alber CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Edit `.env`: sesuaikan `DB_USERNAME` dan `DB_PASSWORD`, lalu isi akun admin awal (password minimal 12 karakter):

```dotenv
ADMIN_USERNAME=admin
ADMIN_PASSWORD="PasswordKuatAnda123"
```

Jalankan:

```bash
php artisan migrate --seed
php artisan serve
```

Buka http://127.0.0.1:8000 dan masuk dengan username dan password di atas.

## Masalah login

**Lupa password.** Reset dari folder proyek (ganti `PasswordBaru123` dan `admin` sesuai kebutuhan):

```bash
php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); App\Models\User::where("username","admin")->firstOrFail()->update(["password" => "PasswordBaru123"]); echo "OK\n";'
```

Perintah `php artisan tinker` tidak tersedia di proyek ini, gunakan perintah di atas.

**"Terlalu banyak percobaan".** Login dikunci sementara setelah 5 kali salah. Tunggu 1 menit atau jalankan `php artisan cache:clear`.

**"Sesi berakhir" (419).** Muat ulang halaman login lalu coba lagi. Jika tetap terjadi, jalankan `php artisan config:clear` dan pastikan folder `storage/` bisa ditulis.

**"Username atau password salah" padahal sudah benar.** Kemungkinan akun admin belum dibuat. Pastikan `ADMIN_PASSWORD` di `.env` terisi, lalu jalankan `php artisan db:seed`.
