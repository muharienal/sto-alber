<?php

namespace App\Support;

use App\Models\ImportBatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Membersihkan PDF pratinjau yang tidak pernah disimpan.
 *
 * Tidak bergantung pada scheduler Laravel (yang di Windows/Laragon harus dipasang
 * manual lewat Task Scheduler): pembersihan juga dipicu otomatis saat ada unggahan
 * dan saat halaman Master STO dibuka, paling sering sekali per jam.
 */
class ImportCleanup
{
    /** Jalankan bila belum dijalankan dalam satu jam terakhir. */
    public static function runIfDue(): void
    {
        if (Cache::add('sto-import-cleanup', now()->timestamp, now()->addHour())) {
            try {
                self::run();
            } catch (\Throwable $e) {
                report($e); // pembersihan tidak boleh menggagalkan halaman
            }
        }
    }

    /** @return array{expired:int, orphans:int} */
    public static function run(): array
    {
        $expired = 0;
        ImportBatch::where('status', 'preview')->where('expires_at', '<', now())->pluck('id')
            ->each(function ($id) use (&$expired) {
                DB::transaction(function () use ($id, &$expired) {
                    $batch = ImportBatch::whereKey($id)->lockForUpdate()->first();
                    if ($batch && $batch->status === 'preview' && $batch->expires_at->isPast()) {
                        Storage::delete($batch->path);
                        $batch->delete();
                        $expired++;
                    }
                });
            });

        // File yatim: ada di folder sto/ tetapi tidak tercatat di import_batches
        // (mis. proses terhenti di tengah unggahan). Hanya yang berumur > 1 hari.
        $known = ImportBatch::pluck('path')->flip();
        $orphans = 0;
        foreach (Storage::files('sto') as $file) {
            if (! $known->has($file) && Storage::lastModified($file) < now()->subDay()->timestamp) {
                Storage::delete($file);
                $orphans++;
            }
        }

        return ['expired' => $expired, 'orphans' => $orphans];
    }
}
