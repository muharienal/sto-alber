<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  $password=env('ADMIN_PASSWORD');
  if (!$password || strlen($password)<12) throw new \RuntimeException('Isi ADMIN_PASSWORD minimal 12 karakter di .env sebelum seeding.');
  User::firstOrCreate(['username'=>env('ADMIN_USERNAME','admin')],['name'=>env('ADMIN_NAME','Superadmin'),'email'=>env('ADMIN_EMAIL','admin@example.com'),'password'=>$password]);
 }
}
