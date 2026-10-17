<?php

namespace App\Http\Controllers\Participant;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\EventSession;
use App\Services\AttendanceTokenService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AttendanceScanController extends Controller
{
    /**
     * Tampilkan halaman pemindai kamera & input kode angka (/absen).
     */
    public function showAbsenForm(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->isStaff()) {
            return redirect()->to('/admin')->with('warning', 'Pengurus tidak dapat melakukan presensi sebagai peserta.');
        }

        // Cari sesi hari ini dari kegiatan yang diikuti peserta
        $activeEnrollments = $user->enrollments()
            ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
            ->pluck('event_id');

        $todaySession = EventSession::whereIn('event_id', $activeEnrollments)
            ->whereDate('starts_at', Carbon::today('Asia/Jakarta'))
            ->with('event')
            ->orderBy('starts_at')
            ->first();

        // Cek apakah sudah absen hari ini
        $alreadyAttended = false;
        $attendedTime = null;
        if ($todaySession) {
            $att = Attendance::where('event_session_id', $todaySession->id)
                ->where('user_id', $user->id)
                ->first();
            if ($att) {
                $alreadyAttended = true;
                $attendedTime = $att->attended_at?->timezone('Asia/Jakarta')->format('H.i');
            }
        }

        $activeTab = $request->query('tab') === 'kode' ? 'kode' : 'kamera';

        return view('pages.participant.absen', [
            'todaySession' => $todaySession,
            'alreadyAttended' => $alreadyAttended,
            'attendedTime' => $attendedTime,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Memproses verifikasi presensi peserta melalui pemindaian QR dinamis.
     */
    public function scan(Request $request, string $token)
    {
        // 1. Validasi token QR
        $session = AttendanceTokenService::validateToken($token);

        if (! $session) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Kode Sudah Kedaluwarsa',
                'message' => 'Kode QR sudah kedaluwarsa. Arahkan kamera lagi ke layar di depan kelas untuk membaca kode terbaru.',
                'code' => 'token_expired',
                'session' => null,
                'attendance' => null,
            ], 422);
        }

        return $this->recordAttendance($request, $session, 'qr');
    }

    /**
     * Memproses verifikasi presensi peserta melalui kode angka 6 digit.
     */
    public function submitCode(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ], [
            'code.required' => 'Masukkan 6 digit kode angka yang tampil di layar proyektor.',
        ]);

        $rawCode = (string) $request->input('code');
        $cleanCode = preg_replace('/[^\d]/', '', $rawCode);

        // Rate Limiter: Maksimal 5 percobaan salah per 2 menit per peserta
        $rateLimitKey = 'attendance_code_attempts:'.$user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Percobaan Terlalu Banyak',
                'message' => 'Terlalu banyak percobaan kode angka yang salah. Harap tunggu '.ceil($seconds / 60).' menit lagi atau tunjukkan nama Anda ke panitia untuk dibantu absen manual.',
                'code' => 'rate_limited',
                'session' => null,
                'attendance' => null,
            ], 429);
        }

        // Validasi kode angka
        $session = AttendanceTokenService::validateNumericCode($cleanCode);

        if (! $session) {
            RateLimiter::hit($rateLimitKey, 120);

            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Kode Angka Tidak Cocok',
                'message' => 'Kode angka salah atau sudah berganti. Silakan periksa kembali kode 6 digit terbaru yang tampil di layar proyektor.',
                'code' => 'code_invalid',
                'session' => null,
                'attendance' => null,
            ], 422);
        }

        // Bersihkan hitungan rate limiter jika kode benar
        RateLimiter::clear($rateLimitKey);

        return $this->recordAttendance($request, $session, 'code');
    }

    /**
     * Logika utama pencatatan kehadiran & pengecekan syarat.
     */
    protected function recordAttendance(Request $request, EventSession $session, string $source)
    {
        $user = Auth::user();

        if ($user && $user->isStaff()) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Pengurus tidak dapat melakukan presensi sebagai peserta.',
                'code' => 'admin_restricted',
                'session' => $session,
                'attendance' => null,
            ], 403);
        }

        $event = $session->event;

        // 1. Periksa status kepesertaan
        $enrollment = $user->enrollments()->where('event_id', $session->event_id)->first();

        if (! $enrollment) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Belum Terdaftar',
                'message' => 'Anda belum terdaftar di kegiatan "'.($event?->title ?? 'Kegiatan ini').'". Silakan hubungi panitia di ruangan untuk bantuan pendaftaran.',
                'code' => 'not_enrolled',
                'session' => $session,
                'attendance' => null,
            ], 403);
        }

        if ($enrollment->status === EnrollmentStatus::Menunggu) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Pendaftaran Menunggu Diperiksa',
                'message' => 'Pendaftaran Anda pada kegiatan ini masih menunggu diperiksa panitia. Silakan beri tahu panitia di ruangan agar pendaftaran Anda disetujui.',
                'code' => 'status_pending',
                'session' => $session,
                'attendance' => null,
            ], 403);
        }

        if ($enrollment->status === EnrollmentStatus::Ditolak) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Pendaftaran Tidak Diterima',
                'message' => 'Pendaftaran Anda pada kegiatan ini tidak diterima panitia: '.($enrollment->rejection_reason ?: 'Hubungi panitia untuk informasi selengkapnya.'),
                'code' => 'status_rejected',
                'session' => $session,
                'attendance' => null,
            ], 403);
        }

        if ($enrollment->status !== EnrollmentStatus::Aktif && $enrollment->status !== EnrollmentStatus::Selesai) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Status Pendaftaran Belum Aktif',
                'message' => 'Status pendaftaran Anda belum aktif untuk mengikuti absen pada kegiatan ini.',
                'code' => 'status_inactive',
                'session' => $session,
                'attendance' => null,
            ], 403);
        }

        // 2. Periksa apakah sesi sedang dibuka
        if (! $session->isOpenForAttendance()) {
            return response()->view('pages.participant.attendance-result', [
                'status' => 'error',
                'title' => 'Absen Belum Dibuka',
                'message' => 'Absen untuk sesi "'.$session->title.'" belum dibuka oleh panitia. Mohon tunggu arahan instruktur di depan kelas.',
                'code' => 'session_closed',
                'session' => $session,
                'attendance' => null,
            ], 422);
        }

        // 3. Periksa apakah sudah pernah absen pada sesi ini
        $existing = Attendance::where('event_session_id', $session->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $timeFormatted = $existing->attended_at?->timezone('Asia/Jakarta')->format('H.i') ?? '-';

            return response()->view('pages.participant.attendance-result', [
                'status' => 'already',
                'title' => 'Anda Sudah Absen',
                'message' => 'Anda sudah absen untuk sesi ini pukul '.$timeFormatted.' WIB.',
                'code' => 'already_attended',
                'session' => $session,
                'attendance' => $existing,
            ], 409);
        }

        // 4. Catat Kehadiran Baru
        $attendance = Attendance::create([
            'event_session_id' => $session->id,
            'user_id' => $user->id,
            'status' => AttendanceStatus::Hadir,
            'source' => $source,
            'recorded_by' => null,
            'attended_at' => Carbon::now('Asia/Jakarta'),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        // Audit Log
        AuditLog::log(
            'attendance_'.$source,
            'event_sessions',
            $session->id,
            [
                'attendance_id' => $attendance->id,
                'source' => $source,
                'session_title' => $session->title,
                'user_name' => $user->name,
                'ip' => $request->ip(),
            ]
        );

        $timeString = $attendance->attended_at->timezone('Asia/Jakarta')->format('H.i');

        return response()->view('pages.participant.attendance-result', [
            'status' => 'success',
            'title' => 'Absen Berhasil!',
            'message' => 'Absen berhasil, '.$user->name.'. Pukul '.$timeString.' WIB.',
            'code' => 'success',
            'session' => $session,
            'attendance' => $attendance,
        ], 200);
    }
}
