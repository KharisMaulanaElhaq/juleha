<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAnonymizationService
{
    /**
     * Jalankan alur anonimisasi akun dan penghapusan data pribadi sesuai UU PDP.
     * Menghapus seluruh data pribadi mentah (Nama, NIK, WhatsApp, Domisili, Email, Avatar)
     * dan berkas fisik PDF sertifikat di penyimpanan privat.
     * Mempertahankan catatan teknis minimal sertifikat tanpa identitas.
     */
    public function anonymize(User $user, string $reason, ?int $actorId = null): void
    {
        $oldId = $user->id;

        // 1. Anonimkan data tabel users
        $user->update([
            'name' => 'Pengguna Dihapus #'.$oldId,
            'email' => 'anonymized_'.$oldId.'_'.time().'@julehabrebes.org',
            'avatar' => null,
            'google_id' => null,
            'password' => Hash::make(Str::random(32)),
            'is_active' => false,
            'suspended_at' => now(),
            'suspended_reason' => 'Akun dianonimkan atas permintaan privasi data: '.$reason,
            'anonymized_at' => now(),
        ]);

        // 2. Anonimkan profil peserta jika ada
        if ($user->profile) {
            $user->profile->update([
                'full_name_ktp' => null,
                'nik' => null,
                'nik_hash' => null,
                'phone' => null,
                'district' => null,
                'village' => null,
                'institution' => null,
                'status' => null,
                'consent' => false,
            ]);
        }

        // 3. Anonimkan berkas sertifikat (Hapus file fisik PDF, pertahankan catatan minimal verifikasi)
        foreach ($user->certificates as $cert) {
            if ($cert->file_path && Storage::disk('private')->exists($cert->file_path)) {
                Storage::disk('private')->delete($cert->file_path);
            }
            $cert->update([
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_sha256' => null,
                'holder_data_erased_at' => now(),
            ]);
        }

        // 4. Hapus token sesi login
        DB::table('sessions')->where('user_id', $oldId)->delete();
        $user->forceFill(['remember_token' => null])->saveQuietly();

        // 5. Soft delete akun pengguna
        $user->delete();

        // 6. Catat riwayat ke audit log tanpa memuat NIK mentah
        AuditLog::logUserActivity('anonymized', $oldId, [
            'reason' => $reason,
        ], $actorId);
    }

    public function anonymizeUser(User $user, string $reason, ?int $actorId = null): bool
    {
        $this->anonymize($user, $reason, $actorId);

        return true;
    }
}
