<?php

namespace App\Http\Controllers\Participant;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard portal peserta.
     */
    public function index(): View
    {
        $user = Auth::user();
        $profile = $user->profile;

        $myEnrollments = $user->enrollments()
            ->with(['event.program', 'event.sessions'])
            ->latest()
            ->get();

        $openEvents = Event::where('status', 'open')
            ->with('program')
            ->orderBy('starts_at')
            ->get()
            ->filter(fn (Event $event) => $event->canRegister())
            ->groupBy(fn (Event $event) => $event->type->value);

        $upcomingActiveEnrollment = $myEnrollments
            ->where('status', EnrollmentStatus::Aktif)
            ->filter(fn ($en) => $en->event && $en->event->starts_at && $en->event->starts_at->isFuture())
            ->sortBy(fn ($en) => $en->event->starts_at)
            ->first();

        $isProfileComplete = $profile && filled($profile->nik) && filled($profile->phone);

        // 1. Data Presensi: Sesi Hari Ini untuk kegiatan yang aktif diikuti
        $activeEventIds = $myEnrollments
            ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
            ->pluck('event_id')
            ->toArray();

        $today = Carbon::today('Asia/Jakarta');
        $todaySessions = EventSession::whereIn('event_id', $activeEventIds)
            ->where(function ($query) use ($today) {
                $query->whereDate('starts_at', $today)
                    ->orWhere('attendance_open', true);
            })
            ->with('event')
            ->orderBy('starts_at')
            ->get();

        // 2. Riwayat Kehadiran User
        $myAttendances = Attendance::where('user_id', $user->id)
            ->with(['session.event'])
            ->latest('attended_at')
            ->get();

        // Kumpulan ID sesi yang sudah dihadiri user
        $attendedSessionIds = $myAttendances->pluck('event_session_id')->toArray();

        // 3. Persentase kehadiran per kegiatan
        $attendanceStats = [];
        $totalAllSessions = 0;
        $totalAllHadir = 0;

        foreach ($myEnrollments as $en) {
            if ($en->event) {
                $sessionIds = $en->event->sessions->pluck('id');
                $totalSessions = $sessionIds->count();
                $hadirCount = $myAttendances->whereIn('event_session_id', $sessionIds)
                    ->where('status', AttendanceStatus::Hadir)
                    ->count();

                $pct = $totalSessions > 0 ? round(($hadirCount / $totalSessions) * 100) : 0;

                $attendanceStats[$en->event_id] = [
                    'total_sessions' => $totalSessions,
                    'hadir_count' => $hadirCount,
                    'percentage' => $pct,
                ];

                $totalAllSessions += $totalSessions;
                $totalAllHadir += $hadirCount;
            }
        }

        $overallPercentage = $totalAllSessions > 0 ? round(($totalAllHadir / $totalAllSessions) * 100) : 0;

        // Cek kondisi sesi hari ini untuk satu kartu aksi utama
        $primarySession = $todaySessions->first();
        $primarySessionAttended = false;
        $primarySessionAttendedTime = null;

        if ($primarySession) {
            $att = $myAttendances->firstWhere('event_session_id', $primarySession->id);
            if ($att) {
                $primarySessionAttended = true;
                $primarySessionAttendedTime = $att->attended_at?->timezone('Asia/Jakarta')->format('H.i');
            }
        }

        // Cek kolom profil yang belum lengkap untuk banner
        $missingProfileFields = [];
        if (! $profile || empty($profile->full_name_ktp)) {
            $missingProfileFields[] = 'Nama Lengkap Sesuai KTP';
        }
        if (! $profile || empty($profile->nik)) {
            $missingProfileFields[] = 'Nomor Induk Kependudukan (NIK)';
        }
        if (! $profile || empty($profile->phone)) {
            $missingProfileFields[] = 'Nomor WhatsApp';
        }
        if (! $profile || empty($profile->subdistrict)) {
            $missingProfileFields[] = 'Kecamatan / Domisili';
        }

        return view('pages.participant.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'isProfileComplete' => $isProfileComplete && empty($missingProfileFields),
            'missingProfileFields' => $missingProfileFields,
            'myEnrollments' => $myEnrollments,
            'openEvents' => $openEvents,
            'upcomingActiveEnrollment' => $upcomingActiveEnrollment,
            'eventTypes' => EventType::cases(),
            'todaySessions' => $todaySessions,
            'primarySession' => $primarySession,
            'primarySessionAttended' => $primarySessionAttended,
            'primarySessionAttendedTime' => $primarySessionAttendedTime,
            'myAttendances' => $myAttendances,
            'attendedSessionIds' => $attendedSessionIds,
            'attendanceStats' => $attendanceStats,
            'totalAllSessions' => $totalAllSessions,
            'totalAllHadir' => $totalAllHadir,
            'overallPercentage' => $overallPercentage,
        ]);
    }
}
