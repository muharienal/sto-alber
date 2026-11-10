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
