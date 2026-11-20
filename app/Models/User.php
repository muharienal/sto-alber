<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public const ROLES = [
        'admin' => 'Admin',
        'petugas' => 'Petugas input',
    ];

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean', 'last_login_at' => 'datetime'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
