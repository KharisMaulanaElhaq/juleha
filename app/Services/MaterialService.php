<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Mail\NewMaterialNotification;
use App\Models\Material;
use Exception;
use Illuminate\Support\Facades\Mail;

class MaterialService
{
    /**
     * Kirim notifikasi email via antrean (queue) ke seluruh peserta aktif di kegiatan materi terkait.
     */
    public static function notifyActiveParticipants(Material $material): int
    {
        if (! $material->is_published) {
            return 0;
        }

        $event = $material->event;
        if (! $event) {
            return 0;
        }

        $activeUsers = $event->enrollments()
            ->where('status', EnrollmentStatus::Aktif)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn ($u) => filled($u?->email));

        $count = 0;
        foreach ($activeUsers as $user) {
            try {
                Mail::to($user->email)->queue(new NewMaterialNotification($material, $user));
                $count++;
            } catch (Exception $e) {
                // Abaikan jika mail transport offline di pengujian lokal
            }
        }

        return $count;
    }
}
