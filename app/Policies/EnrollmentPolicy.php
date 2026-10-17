<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar pendaftaran di panel admin.
     * Hanya super_admin dan admin_kegiatan yang berhak; editor_konten ditolak.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Tentukan apakah user dapat melihat detail pendaftaran.
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($user->isAdminKegiatan()) {
            return true;
        }

        return $user->isParticipant() && $user->id === $enrollment->user_id;
    }

    /**
     * Peserta dapat membuat pendaftaran baru; admin juga dapat mendaftarkan.
     */
    public function create(User $user): bool
    {
        return $user->isParticipant() || $user->isAdminKegiatan();
    }

    /**
     * Hanya admin (super_admin & admin_kegiatan) yang dapat mengonfirmasi/menolak pendaftaran.
     */
    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Hanya super_admin yang dapat menghapus pendaftaran.
     */
    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $user->isSuperAdmin();
    }
}
