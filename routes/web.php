<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,DashboardController,StoController};
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'login'])->name('login');Route::post('/login',[AuthController::class,'authenticate'])->name('login.submit')->middleware('throttle:20,1');});
Route::middleware('auth')->group(function(){
 Route::get('/',DashboardController::class)->name('dashboard');Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::view('/akun','auth.account')->name('account');Route::put('/akun/password',[AuthController::class,'password'])->name('password.update');
 Route::get('/sto',[StoController::class,'index'])->name('sto.index');Route::post('/sto',[StoController::class,'store'])->name('sto.store');
 Route::post('/sto/upload',[StoController::class,'upload'])->name('sto.upload')->middleware('throttle:10,1');
 Route::get('/sto/export',[StoController::class,'export'])->name('sto.export');
 Route::get('/impor/{batch}/preview',[StoController::class,'preview'])->name('sto.preview');Route::post('/impor/{batch}/simpan',[StoController::class,'commit'])->name('sto.commit');Route::delete('/impor/{batch}',[StoController::class,'cancel'])->name('sto.cancel');
 Route::get('/sto/{sto}',[StoController::class,'show'])->name('sto.show');Route::put('/sto/{sto}',[StoController::class,'update'])->name('sto.update');Route::delete('/sto/{sto}',[StoController::class,'destroy'])->name('sto.destroy');
 Route::patch('/sto/{sto}/referensi',[StoController::class,'references'])->name('sto.references');Route::get('/sto/{sto}/pdf',[StoController::class,'pdf'])->name('sto.pdf');
});
