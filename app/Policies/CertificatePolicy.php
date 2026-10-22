<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar sertifikat di Filament.
     * Hanya super_admin dan admin_kegiatan yang diizinkan; editor_konten dan peserta 403.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail sertifikat.
     */
    public function view(User $user, Certificate $certificate): bool
    {
        if ($user->isAdminKegiatan()) {
            return true;
        }

        return $user->isParticipant() && $user->id === $certificate->user_id;
    }

    /**
     * Hanya admin kegiatan dan super_admin yang dapat mencatat hasil sertifikasi.
     */
    public function create(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Hanya admin kegiatan dan super_admin yang dapat mengubah data sertifikat.
     */
    public function update(User $user, Certificate $certificate): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Hanya super_admin yang berhak menghapus data sertifikat.
     */
    public function delete(User $user, Certificate $certificate): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Admin kegiatan dan super admin dapat memulihkan soft delete.
     */
    public function restore(User $user, Certificate $certificate): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Hanya super_admin yang dapat force delete.
     */
    public function forceDelete(User $user, Certificate $certificate): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Cabut atau pulihkan pencabutan sertifikat.
     */
    public function revoke(User $user, Certificate $certificate): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Unduh file fisik sertifikat dari panel admin.
     */
    public function downloadAdmin(User $user, Certificate $certificate): bool
    {
        return $user->isAdminKegiatan();
    }

    /**
     * Ekspor data peserta untuk LSP (dengan data sensitif KTP/NIK).
     * Khusus Super Admin sesuai spesifikasi keamanan.
     */
    public function exportLsp(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
