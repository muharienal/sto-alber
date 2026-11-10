<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
 public function login(){return view('auth.login');}
 public function authenticate(Request $r){
  $data=$r->validate(['username'=>'required|string|max:255','password'=>'required|string|max:255'],['required'=>':attribute wajib diisi.','max'=>':attribute terlalu panjang.'],['username'=>'Username','password'=>'Password']);
  $key='login:'.hash('sha256',strtolower($data['username']).'|'.$r->ip());
  if(RateLimiter::tooManyAttempts($key,5)) throw ValidationException::withMessages(['username'=>'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
  if(!Auth::attempt($data)){RateLimiter::hit($key,60);throw ValidationException::withMessages(['username'=>'Username atau password salah.']);}
  RateLimiter::clear($key);$r->session()->regenerate();return redirect()->intended(route('dashboard'));
 }
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
 public function password(Request $r){
  $data=$r->validate(
   ['current_password'=>['required','current_password'],'password'=>['required','confirmed',\Illuminate\Validation\Rules\Password::min(12)]],
   ['required'=>':attribute wajib diisi.','current_password'=>'Password saat ini salah.','confirmed'=>'Ulangan password baru tidak sama.','password.min'=>'Password baru minimal :min karakter.'],
   ['current_password'=>'Password saat ini','password'=>'Password baru']
  );
  $r->user()->update(['password'=>$data['password'],'remember_token'=>null]);$r->session()->regenerate();return back()->with('success','Password berhasil diubah.');
 }
}
