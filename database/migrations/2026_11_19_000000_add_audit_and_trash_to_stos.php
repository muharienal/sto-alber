<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Jejak perubahan dan Sampah untuk STO:
 *  - stos.created_by / updated_by / deleted_by: siapa yang menambah, mengubah terakhir, dan menghapus;
 *  - stos.deleted_at: hapus tidak lagi permanen, STO masuk Sampah dan bisa dipulihkan;
 *  - sto_activities: riwayat setiap kejadian (impor, tambah, ubah, isi SOJ/MMJ, hapus, pulihkan, hapus permanen).
 * Semua langkah diperiksa dulu sehingga aman dijalankan ulang.
 */
return new class extends Migration {
    public function up(): void
    {
        foreach (['created_by', 'updated_by', 'deleted_by'] as $column) {
            if (! Schema::hasColumn('stos', $column)) {
                Schema::table('stos', function (Blueprint $table) use ($column) {
                    $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete();
                });
            }
        }
        if (! Schema::hasColumn('stos', 'deleted_at')) {
            Schema::table('stos', function (Blueprint $table) {
                $table->softDeletes()->index();
            });
        }

        if (! Schema::hasTable('sto_activities')) {
            Schema::create('sto_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sto_id')->nullable()->constrained('stos')->nullOnDelete();
                $table->string('sto_number', 40)->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_name')->nullable();   // disimpan agar tetap terbaca bila akun dihapus
                $table->string('action', 20);              // imported, created, updated, references, deleted, restored, purged
                $table->json('changes')->nullable();       // {"soj": ["lama", "baru"], ...}
                $table->string('note')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        // STO hasil impor yang sudah ada: pembuatnya adalah pengunggah PDF.
        DB::table('stos')->whereNull('created_by')->whereNotNull('import_batch_id')->update([
            'created_by' => DB::raw('(select user_id from import_batches where import_batches.id = stos.import_batch_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sto_activities');
        foreach (['created_by', 'updated_by', 'deleted_by'] as $column) {
            if (Schema::hasColumn('stos', $column)) {
                Schema::table('stos', function (Blueprint $table) use ($column) {
                    $table->dropConstrainedForeignId($column);
                });
            }
        }
        if (Schema::hasColumn('stos', 'deleted_at')) {
            Schema::table('stos', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
                $table->dropSoftDeletes();
            });
        }
    }
};
