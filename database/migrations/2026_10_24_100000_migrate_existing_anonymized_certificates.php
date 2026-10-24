<?php

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrasikan sertifikat milik akun yang pernah dianonimkan sebelumnya:
     * isi holder_data_erased_at dan pastikan file serta metadata pribadi bersih.
     */
    public function up(): void
    {
        $anonymizedUserIds = User::withTrashed()
            ->where(function ($query) {
                $query->whereNotNull('anonymized_at')
                    ->orWhere('name', 'like', 'Pengguna Dihapus%');
            })
            ->pluck('id');

        if ($anonymizedUserIds->isEmpty()) {
            return;
        }

        $certificates = Certificate::withTrashed()
            ->whereIn('user_id', $anonymizedUserIds)
            ->whereNull('holder_data_erased_at')
            ->with(['user'])
            ->get();

        $disk = Storage::disk('private');
        $affectedCount = 0;

        foreach ($certificates as $cert) {
            if ($cert->file_path && $disk->exists($cert->file_path)) {
                $disk->delete($cert->file_path);
            }

            $cert->update([
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_sha256' => null,
                'holder_data_erased_at' => $cert->user?->anonymized_at ?? $cert->updated_at ?? now(),
            ]);

            $affectedCount++;
        }

        if ($affectedCount > 0) {
            Log::info("Migrasi data sertifikat: Berhasil memigrasikan {$affectedCount} catatan sertifikat milik akun teranonimkan.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data anonimisasi bersifat one-way per UU PDP
    }
};
