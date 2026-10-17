<?php

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\EventSession;
use App\Models\User;

class EventSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    public function view(User $user, EventSession $session): bool
    {
        if ($user->isAdminKegiatan()) {
            return true;
        }

        return $user->isParticipant() && $user->enrollments()
            ->where('event_id', $session->event_id)
            ->where('status', EnrollmentStatus::Aktif)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    public function update(User $user, EventSession $session): bool
    {
        return $user->isAdminKegiatan();
    }

    public function delete(User $user, EventSession $session): bool
    {
        return $user->isAdminKegiatan();
    }
}
