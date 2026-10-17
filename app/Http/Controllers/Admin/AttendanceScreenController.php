<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\EventSession;
use App\Services\AttendanceTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceScreenController extends Controller
{
    /**
     * Tampilkan layar proyektor presensi QR besar untuk admin/panitia.
     */
    public function show(Request $request, EventSession $session)
    {
        $this->ensureAdmin();

        $session->loadMissing('event');
        $tokenData = AttendanceTokenService::getOrGenerateToken($session);

        $totalActiveEnrollments = $session->event
            ? $session->event->enrollments()->where('status', EnrollmentStatus::Aktif)->count()
            : 0;

        return view('pages.admin.attendance-screen', [
            'session' => $session,
            'event' => $session->event,
            'initialToken' => $tokenData,
            'totalActiveEnrollments' => $totalActiveEnrollments,
        ]);
    }

    /**
     * Endpoint JSON untuk mengambil token QR baru (dipanggil tiap 45 detik).
     */
    public function token(Request $request, EventSession $session): JsonResponse
    {
        $this->ensureAdmin();

        $session->loadMissing('event');
        $tokenData = AttendanceTokenService::generateToken($session);

        return response()->json([
            'success' => true,
            'data' => $tokenData,
        ]);
    }

    /**
     * Endpoint polling statistik realtime jumlah peserta hadir.
     */
    public function stats(Request $request, EventSession $session): JsonResponse
    {
        $this->ensureAdmin();

        $totalActiveEnrollments = $session->event
            ? $session->event->enrollments()->where('status', EnrollmentStatus::Aktif)->count()
            : 0;

        $recentAttendances = $session->attendances()
            ->with('user:id,name,avatar')
            ->latest('attended_at')
            ->take(5)
            ->get()
            ->map(fn ($att) => [
                'name' => $att->user?->name ?? 'Peserta',
                'time' => $att->attended_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-',
                'status' => $att->status->label(),
                'source' => $att->source,
            ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'hadir_count' => $session->hadir_count,
            'total_enrollments' => $totalActiveEnrollments,
            'attendance_open' => $session->attendance_open,
            'is_open' => $session->isOpenForAttendance(),
            'recent' => $recentAttendances,
        ]);
    }

    /**
     * Toggle status buka/tutup presensi manual dari layar proyektor.
     */
    public function toggleOpen(Request $request, EventSession $session): JsonResponse
    {
        $this->ensureAdmin();

        $session->attendance_open = ! $session->attendance_open;
        $session->save();

        return response()->json([
            'success' => true,
            'attendance_open' => $session->attendance_open,
            'message' => $session->attendance_open
                ? 'Presensi sesi berhasil dibuka untuk peserta.'
                : 'Presensi sesi berhasil ditutup.',
        ]);
    }

    protected function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdminKegiatan()) {
            abort(403, 'Akses ditolak. Halaman layar presensi khusus untuk pengurus/admin kegiatan.');
        }
    }
}
