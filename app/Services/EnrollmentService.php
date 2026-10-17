<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\EventRegistrationState;
use App\Mail\NewEnrollmentAdminNotification;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use DomainException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EnrollmentService
{
    /**
     * Daftarkan peserta ke kegiatan dengan transaksi DB dan kunci baris event (concurrency-safe).
     *
     * @throws DomainException
     */
    public function enroll(User $user, Event $event): Enrollment
    {
        // 1. Validasi peran: hanya participant yang boleh mendaftar
        if ($user->isStaff()) {
            throw new DomainException('Pengurus tidak dapat mendaftar sebagai peserta kegiatan.');
        }

        // 2. Validasi profil dan NIK
        $profile = $user->profile;
        if (! $profile || empty($profile->nik)) {
            throw new DomainException('Nomor Induk Kependudukan (NIK) dan data profil wajib dilengkapi terlebih dahulu.');
        }

        return DB::transaction(function () use ($user, $event) {
            // Kunci baris event untuk menghindari race condition
            /** @var Event $lockedEvent */
            $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->firstOrFail();

            // Cek status pendaftaran kegiatan (finished, closed, deadline_passed, full, open)
            $state = $lockedEvent->registrationState();
            if ($state !== EventRegistrationState::Open) {
                throw new DomainException($state->rejectionMessage());
            }

            // Cek duplikasi pendaftaran user di event yang sama
            $alreadyEnrolled = Enrollment::where('user_id', $user->id)
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyEnrolled) {
                throw new DomainException('Anda sudah terdaftar pada kegiatan ini.');
            }

            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'event_id' => $lockedEvent->id,
                'status' => EnrollmentStatus::Menunggu,
            ]);

            $this->notifyAdmin($enrollment);

            return $enrollment;
        });
    }

    /**
     * Tambah peserta manual oleh pengurus (lewati kuota kegiatan).
     * Khusus untuk Super Admin dan Admin Kegiatan.
     */
    public function manualEnrollBypassingQuota(
        User $user,
        Event $event,
        ?string $adminNotes = null,
        ?int $actorId = null
    ): Enrollment {
        return DB::transaction(function () use ($user, $event, $adminNotes, $actorId) {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->firstOrFail();

            $existing = Enrollment::where('user_id', $user->id)
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new DomainException("Peserta {$user->name} sudah terdaftar pada kegiatan ini.");
            }

            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'event_id' => $lockedEvent->id,
                'status' => EnrollmentStatus::Aktif,
                'admin_notes' => $adminNotes ?? 'Ditambahkan langsung oleh pengurus lewat kuota khusus.',
            ]);

            // Catat di audit logs
            AuditLog::logUserActivity(
                'manual_enroll_quota_bypass',
                $user->id,
                [
                    'event_id' => $lockedEvent->id,
                    'event_title' => $lockedEvent->title,
                    'quota' => $lockedEvent->quota,
                    'used_seats_now' => $lockedEvent->usedSeats(),
                    'admin_notes' => $adminNotes,
                ],
                $actorId ?? auth()->id()
            );

            return $enrollment;
        });
    }

    protected function notifyAdmin(Enrollment $enrollment): void
    {
        $adminEmail = Setting::get('admin_email');
        if (filled($adminEmail)) {
            try {
                Mail::to($adminEmail)->queue(new NewEnrollmentAdminNotification($enrollment));
            } catch (Exception) {
                // Abaikan jika mailer offline
            }
        }
    }
}
