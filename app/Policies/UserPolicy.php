<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah user bisa melihat daftar pengguna.
     */
    public function viewAny(User $user)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Menentukan apakah user bisa melihat detail pengguna tertentu.
     */
    public function view(User $user, User $model)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Menentukan apakah user bisa membuat pengguna baru.
     */
    public function create(User $user)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Menentukan apakah user bisa memperbarui data pengguna.
     */
    public function update(User $user, User $model)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Menentukan apakah user bisa menghapus pengguna.
     */
    public function delete(User $user, User $model)
    {
        // Super admin bisa menghapus user lain, tapi TIDAK BOLEH menghapus dirinya sendiri
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }

    /**
     * Menentukan apakah user bisa memulihkan pengguna (jika menggunakan soft delete).
     */
    public function restore(User $user, User $model)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Menentukan apakah user bisa menghapus pengguna secara permanen.
     */
    public function forceDelete(User $user, User $model)
    {
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }
}