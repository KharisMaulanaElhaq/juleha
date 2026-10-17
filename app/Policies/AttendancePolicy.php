<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminKegiatan();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isAdminKegiatan()) {
            return true;
        }

        return $user->isParticipant() && $user->id === $attendance->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminKegiatan() || $user->isParticipant();
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->isAdminKegiatan();
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->isAdminKegiatan();
    }
}
