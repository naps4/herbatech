// app/Policies/UserPolicy.php

<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $model)
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $model)
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $model)
    {
        // Super admin tidak boleh menghapus diri sendiri
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }

    public function restore(User $user, User $model)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, User $model)
    {
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }
}