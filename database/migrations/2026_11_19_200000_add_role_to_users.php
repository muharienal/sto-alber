<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Pengguna & peran:
 *  - role: "admin" (semua akses, termasuk kelola pengguna dan hapus STO) atau "petugas" (input & lengkapi STO);
 *  - is_active: akun nonaktif tidak bisa masuk dan langsung keluar bila sedang masuk;
 *  - last_login_at: kapan terakhir masuk;
 *  - email boleh kosong (login memakai username).
 * Akun yang sudah ada saat migrasi dijadikan admin agar tidak ada yang kehilangan akses.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('petugas')->after('username');
            });
            DB::table('users')->update(['role' => 'admin']);
        }
        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('role');
            });
        }
        if (! Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            });
        }
        $email = collect(Schema::getColumns('users'))->firstWhere('name', 'email');
        if ($email && ! $email['nullable']) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['last_login_at', 'is_active', 'role'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
