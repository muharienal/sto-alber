<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Kelola pengguna (khusus admin). Pengguna tidak dihapus karena namanya tercatat di
 * riwayat STO dan impor; cukup dinonaktifkan agar tidak bisa masuk.
 */
class UserController extends Controller
{
    public function index()
    {
        return view('users.index', [
            'users' => User::orderByDesc('is_active')->orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validateWithBag('user', $this->rules() + [
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $user = User::create($data + ['is_active' => true]);

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} ditambahkan.");
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validateWithBag('user', $this->rules($user) + ['is_active' => 'required|boolean']);
        $data['is_active'] = (bool) $data['is_active'];

        $this->guardLastAdmin($r, $user, $data['role'], $data['is_active']);
        $user->update($data);

        return redirect()->route('users.index')->with('success', "Data {$user->name} disimpan.");
    }

    public function password(Request $r, User $user)
    {
        $data = $r->validateWithBag('user', ['password' => ['required', 'confirmed', Password::min(12)]], [], ['password' => 'Password baru']);
        $user->forceFill(['password' => $data['password'], 'remember_token' => null])->save();

        return redirect()->route('users.index')->with('success', "Password {$user->name} direset. Berikan password baru kepadanya.");
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => 'required|string|max:100',
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ];
    }

    /** Cegah admin mengunci dirinya sendiri atau menghilangkan admin aktif terakhir. */
    private function guardLastAdmin(Request $r, User $user, string $role, bool $active): void
    {
        $losesAdmin = $user->isAdmin() && $user->is_active && ($role !== 'admin' || ! $active);
        if (! $losesAdmin) {
            return;
        }
        if ($user->is($r->user())) {
            throw ValidationException::withMessages(['role' => 'Anda tidak bisa menurunkan peran atau menonaktifkan akun Anda sendiri.'])->errorBag('user');
        }
        if (User::where('role', 'admin')->where('is_active', true)->whereKeyNot($user->id)->doesntExist()) {
            throw ValidationException::withMessages(['role' => 'Harus tetap ada minimal satu admin aktif.'])->errorBag('user');
        }
    }
}
