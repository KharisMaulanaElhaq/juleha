<?php

namespace App\Http\Controllers\Participant;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\Enrollment;
use App\Models\Event;
use App\Services\EnrollmentService;
use DomainException;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    /**
     * Tampilkan halaman konfirmasi pendaftaran kegiatan.
     */
    public function create(Event $event): View|RedirectResponse
    {
        // 1. Cek status pendaftaran kegiatan (finished, closed, deadline_passed, full, open)
        if (! $event->canRegister()) {
            return redirect()->route('bimtek.show', $event->slug)->with('error', $event->registrationStateMessage());
        }

        if (! Auth::check()) {
            session(['url.intended' => route('enrollment.create', $event->slug)]);

            return redirect()->route('login')->with('info', 'Silakan masuk dengan akun Google terlebih dahulu untuk mendaftar kegiatan.');
        }

        $user = Auth::user();

        if ($user->isStaff()) {
            return redirect()->to('/admin')->with('warning', 'Pengurus tidak dapat mendaftar sebagai peserta kegiatan.');
        }

        $profile = $user->profile;

        // Validasi wajib lengkapi profil (termasuk NIK)
        if (! $profile || empty($profile->nik)) {
            session(['url.intended' => route('enrollment.create', $event->slug)]);

            return redirect()->route('participant.profile')->with('info', 'Nomor Induk Kependudukan (NIK) dan data profil wajib dilengkapi sebelum mendaftar kegiatan, karena digunakan untuk penerbitan sertifikat.');
        }

        // Validasi persetujuan pemrosesan akun & kegiatan
        if (! Consent::hasGranted($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY)) {
            return redirect()->route('participant.privacy.index')
                ->with('error', 'Persetujuan pemrosesan data akun dan kegiatan belum aktif atau telah Anda tarik. Silakan aktifkan kembali persetujuan tersebut untuk dapat mendaftar kegiatan.');
        }

        // Validasi pembaruan persetujuan data untuk sertifikasi kompetensi
        if ($event->type === EventType::SertifikasiKompetensi) {
            if (! $profile->hasCurrentConsent()) {
                session(['url.intended' => route('enrollment.create', $event->slug)]);

                return redirect()->route('participant.profile', ['step' => 2])
                    ->with('info', 'Pendaftaran uji sertifikasi kompetensi memerlukan pembaruan persetujuan data ke '.setting('cert_issuer_name', 'LSP Pertanian RI').'. Silakan perbarui persetujuan data Anda.');
            }

            if (! Consent::hasGranted($user->id, Consent::PURPOSE_NIK_CERTIFICATE) || ! Consent::hasGranted($user->id, Consent::PURPOSE_SHARE_TO_LSP)) {
                return redirect()->route('participant.privacy.index')
                    ->with('error', 'Pendaftaran uji sertifikasi memerlukan persetujuan penerusan data NIK dan data sertifikasi ke Lembaga Sertifikasi Profesi (LSP/BNSP). Silakan aktifkan persetujuan tersebut di bawah ini.');
            }
        }

        // Cek jika sudah terdaftar
        $existing = Enrollment::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah terdaftar pada kegiatan ini dengan status: '.$existing->status->label().'.');
        }

        return view('pages.participant.enrollment-confirm', [
            'event' => $event,
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Proses pengiriman pendaftaran dengan transaksi database dan kunci kuota via EnrollmentService.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if ($user->isStaff()) {
            return redirect()->to('/admin')->with('warning', 'Pengurus tidak dapat mendaftar sebagai peserta kegiatan.');
        }

        $profile = $user->profile;

        if (! $profile || empty($profile->nik)) {
            return redirect()->route('participant.profile')->with('error', 'Profil dan NIK wajib dilengkapi terlebih dahulu.');
        }

        try {
            $this->enrollmentService->enroll($user, $event);

            return redirect()->route('dashboard')->with('success', 'Pendaftaran kegiatan berhasil dikirim! Status saat ini: Menunggu Konfirmasi panitia DPD JULEHA Brebes.');
        } catch (DomainException|Exception $e) {
            return back(fallback: route('bimtek.show', $event->slug))->with('error', $e->getMessage());
        }
    }
}
