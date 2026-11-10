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
