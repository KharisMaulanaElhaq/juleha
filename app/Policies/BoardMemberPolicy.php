<?php

namespace App\Policies;

use App\Models\BoardMember;
use App\Models\User;

class BoardMemberPolicy
{
    /**
     * Tentukan apakah pengguna boleh melihat daftar pengurus di admin.
     * Hanya super_admin, admin_kegiatan, dan editor_konten.
     */
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Tentukan apakah pengguna boleh melihat detail pengurus.
     */
    public function view(User $user, BoardMember $boardMember): bool
    {
        return $this->canManage($user);
    }

    /**
     * Tentukan apakah pengguna boleh menambah pengurus baru.
     */
    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Tentukan apakah pengguna boleh mengubah data pengurus.
     */
    public function update(User $user, BoardMember $boardMember): bool
    {
        return $this->canManage($user);
    }

    /**
     * Tentukan apakah pengguna boleh menghapus pengurus.
     */
    public function delete(User $user, BoardMember $boardMember): bool
    {
        return $this->canManage($user);
    }

    /**
     * Tentukan apakah pengguna boleh menghapus massal pengurus.
     */
    public function deleteAny(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Hanya peran super_admin, admin_kegiatan, dan editor_konten yang diizinkan.
     */
    protected function canManage(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return in_array($user->role, [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_KEGIATAN,
            'event_admin',
            User::ROLE_EDITOR_KONTEN,
            'content_editor',
        ], true);
    }
}
