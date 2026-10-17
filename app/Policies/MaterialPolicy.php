<?php

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    public function view(User $user, Material $material): bool
    {
        if ($user->isAdminKegiatan()) {
            return true;
        }

        // Materi harus sudah terbit agar dapat dilihat oleh peserta
        if (! $material->is_published) {
            return false;
        }

        // Peserta hanya dapat melihat materi jika memiliki pendaftaran aktif di kegiatan tersebut
        return $user->isParticipant() && $user->enrollments()
            ->where('event_id', $material->event_id)
            ->where('status', EnrollmentStatus::Aktif)
            ->exists();
    }

    /**
     * Tentukan apakah user berhak mengunduh file materi.
     */
    public function download(User $user, Material $material): bool
    {
        return $this->view($user, $material);
    }

    public function create(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    public function update(User $user, Material $material): bool
    {
        return $user->isAdminKegiatan();
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->isAdminKegiatan();
    }
}
