<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,DashboardController,RekapController,StoController,UserController};
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'login'])->name('login');Route::post('/login',[AuthController::class,'authenticate'])->name('login.submit')->middleware('throttle:20,1');});
Route::middleware('auth')->group(function(){
 Route::get('/',DashboardController::class)->name('dashboard');Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::view('/akun','auth.account')->name('account');Route::put('/akun/password',[AuthController::class,'password'])->name('password.update');
 Route::get('/sto',[StoController::class,'index'])->name('sto.index');Route::post('/sto',[StoController::class,'store'])->name('sto.store');
 Route::post('/sto/upload',[StoController::class,'upload'])->name('sto.upload')->middleware('throttle:10,1');
 Route::post('/sto/upload/lanjut',[StoController::class,'uploadAnyway'])->name('sto.upload.anyway')->middleware('throttle:10,1');
 Route::post('/sto/upload/batal',[StoController::class,'uploadDiscard'])->name('sto.upload.discard');
 Route::patch('/sto/referensi',[StoController::class,'bulkReferences'])->name('sto.references.bulk');
 Route::get('/sto/export',[StoController::class,'export'])->name('sto.export');
 // Khusus admin: hapus/pulihkan STO dan kelola pengguna.
 Route::middleware('can:admin')->group(function(){
  Route::get('/sto/sampah',[StoController::class,'trash'])->name('sto.trash');
  Route::delete('/sto/{sto}',[StoController::class,'destroy'])->name('sto.destroy');
  Route::post('/sto/{sto}/pulihkan',[StoController::class,'restore'])->name('sto.restore')->withTrashed();
  Route::delete('/sto/{sto}/permanen',[StoController::class,'forceDestroy'])->name('sto.force-destroy')->withTrashed();
  Route::get('/pengguna',[UserController::class,'index'])->name('users.index');
  Route::post('/pengguna',[UserController::class,'store'])->name('users.store');
  Route::put('/pengguna/{user}',[UserController::class,'update'])->name('users.update');
  Route::put('/pengguna/{user}/password',[UserController::class,'password'])->name('users.password');
 });
 Route::get('/rekap',RekapController::class)->name('rekap');
 Route::get('/impor/{batch}/preview',[StoController::class,'preview'])->name('sto.preview');Route::post('/impor/{batch}/simpan',[StoController::class,'commit'])->name('sto.commit');Route::delete('/impor/{batch}',[StoController::class,'cancel'])->name('sto.cancel');
 Route::get('/impor/{batch}/pdf',[StoController::class,'batchPdf'])->name('sto.batch-pdf');Route::post('/impor/{batch}/selesai',[StoController::class,'dismissFailed'])->name('sto.failed.dismiss');
 Route::get('/sto/{sto}',[StoController::class,'show'])->name('sto.show');Route::put('/sto/{sto}',[StoController::class,'update'])->name('sto.update');
 Route::patch('/sto/{sto}/referensi',[StoController::class,'references'])->name('sto.references');Route::get('/sto/{sto}/pdf',[StoController::class,'pdf'])->name('sto.pdf');
 Route::get('/sto/{sto}/panel',[StoController::class,'panel'])->name('sto.panel');
});
