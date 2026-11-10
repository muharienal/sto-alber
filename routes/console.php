<?php
use Illuminate\Support\Facades\{Artisan,DB,Storage,Schedule};
use App\Models\ImportBatch;
Artisan::command('sto:cleanup',function(){
 $count=0;ImportBatch::where('status','preview')->where('expires_at','<',now())->pluck('id')->each(function($id)use(&$count){DB::transaction(function()use($id,&$count){$b=ImportBatch::whereKey($id)->lockForUpdate()->first();if($b&&$b->status==='preview'&&$b->expires_at->isPast()){Storage::delete($b->path);$b->delete();$count++;}});});
 $this->info("$count pratinjau kedaluwarsa dibersihkan.");
})->purpose('Hapus PDF sementara yang tidak disimpan setelah 24 jam.');
Schedule::command('sto:cleanup')->daily();
