<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Admin: kelola pengguna, hapus/pulihkan STO, Sampah. Petugas: input, impor, lengkapi SOJ/MMJ.
        Gate::define('admin', fn (User $user) => $user->isAdmin());
    }
}
