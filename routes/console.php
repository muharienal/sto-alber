<?php
use Illuminate\Support\Facades\{Artisan,Schedule};
use App\Support\ImportCleanup;
Artisan::command('sto:cleanup',function(){
 $r=ImportCleanup::run();
 $this->info("{$r['expired']} pratinjau kedaluwarsa dan {$r['orphans']} file yatim dibersihkan.");
})->purpose('Hapus PDF sementara yang tidak disimpan (pratinjau > 24 jam dan file yatim).');
// Opsional: pembersihan juga berjalan otomatis saat unggah PDF dan saat Master STO dibuka (maks. 1x per jam).
Schedule::command('sto:cleanup')->daily();
