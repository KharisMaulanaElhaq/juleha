<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Hanya super_admin yang berhak melihat daftar pengguna di panel admin.
     * admin_kegiatan dan editor_konten ditolak (403).
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang berhak melihat detail pengguna.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang berhak membuat pengguna baru di panel admin.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang berhak mengubah data pengguna.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang berhak menghapus pengguna (soft delete).
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang berhak memulihkan akun yang dihapus.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang berhak menghapus permanen pengguna tanpa riwayat.
     * Akun yang memiliki sertifikat TIDAK BOLEH dihapus permanen (harus dianonimkan).
     */
    public function forceDelete(User $user, User $model): bool
    {
        if ($model->certificates()->exists() || $model->hasHistory()) {
            return false;
        }

        return $user->isActive() && $user->isSuperAdmin();
    }
}
