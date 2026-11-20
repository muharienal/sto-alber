<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * import_batches.failed: STO yang gagal dibaca dari PDF beserta bagian yang sempat terbaca
 * (nomor, halaman, tanggal, rute, barang). Dipakai untuk tombol "Tambah manual" yang
 * membuka form sudah terisi sebagian. Diisi kosong (null) saat pengguna menandai selesai.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('import_batches', 'failed')) {
            Schema::table('import_batches', function (Blueprint $table) {
                $table->json('failed')->nullable();
            });
        }
        if (! Schema::hasIndex('import_batches', ['sha256'])) {
            Schema::table('import_batches', function (Blueprint $table) {
                $table->index('sha256');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('import_batches', 'failed')) {
            Schema::table('import_batches', function (Blueprint $table) {
                $table->dropColumn('failed');
            });
        }
        if (Schema::hasIndex('import_batches', ['sha256'])) {
            Schema::table('import_batches', function (Blueprint $table) {
                $table->dropIndex(['sha256']);
            });
        }
    }
};
