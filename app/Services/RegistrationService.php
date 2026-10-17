<?php

namespace App\Services;

use App\Enums\EventRegistrationState;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Setting;
use App\Notifications\NewRegistrationNotification;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RegistrationService
{
    /**
     * @param  array{
     *     full_name: string,
     *     phone: string,
     *     email?: ?string,
     *     district: string,
     *     village?: ?string,
     *     participant_type: string,
     *     notes?: ?string,
     *     consent?: bool
     * }  $data
     */
    public function register(Event $event, array $data): Registration
    {
        return DB::transaction(function () use ($event, $data) {
            // Lock event row for concurrency safety
            $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->firstOrFail();

            $state = $lockedEvent->registrationState();
            if ($state !== EventRegistrationState::Open) {
                throw new DomainException($state->rejectionMessage());
            }

            $normalizedPhone = Registration::normalizePhone($data['phone'] ?? '');

            // Check duplicate phone in transaction
            $isDuplicate = Registration::where('event_id', $lockedEvent->id)
                ->where('phone', $normalizedPhone)
                ->lockForUpdate()
                ->exists();

            if ($isDuplicate) {
                throw new DomainException('Nomor WhatsApp ini sudah terdaftar pada bimtek ini.');
            }

            $code = Registration::generateUniqueCode();

            $registration = Registration::create([
                'event_id' => $lockedEvent->id,
                'code' => $code,
                'full_name' => trim($data['full_name']),
                'phone' => $normalizedPhone,
                'email' => ! empty($data['email']) ? trim($data['email']) : null,
                'district' => trim($data['district']),
                'village' => ! empty($data['village']) ? trim($data['village']) : null,
                'participant_type' => $data['participant_type'],
                'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                'status' => 'pending',
                'consent' => true,
                'consent_at' => now(),
            ]);

            // Notify admin via queued notification
            $adminEmail = Setting::get('admin_email', config('mail.from.address'));
            if (! empty($adminEmail)) {
                Notification::route('mail', $adminEmail)->notify(new NewRegistrationNotification($registration));
            }

            return $registration;
        });
    }
}
