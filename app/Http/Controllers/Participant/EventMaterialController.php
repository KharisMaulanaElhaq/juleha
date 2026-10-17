<?php

namespace App\Http\Controllers\Participant;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Material;
use App\Models\MaterialDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventMaterialController extends Controller
{
    /**
     * Halaman daftar materi pembelajaran kelas kegiatan untuk peserta aktif.
     */
    public function index(Request $request, Event $event)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu untuk mengakses materi kegiatan.');
        }

        $user = Auth::user();

        // Verifikasi hak akses pendaftaran aktif
        $this->ensureActiveParticipant($user, $event);

        // Ambil materi yang telah diterbitkan (jika admin kegiatan, tampilkan semua)
        $materials = $user->isAdminKegiatan()
            ? $event->materials()->orderBy('sort_order')->orderBy('id')->get()
            : $event->materials()->published()->get();

        $downloadedMaterialIds = MaterialDownload::where('user_id', $user->id)
            ->whereIn('material_id', $materials->pluck('id'))
            ->pluck('material_id')
            ->unique()
            ->toArray();

        return view('pages.participant.materials', [
            'event' => $event,
            'materials' => $materials,
            'downloadedMaterialIds' => $downloadedMaterialIds,
            'user' => $user,
        ]);
    }

    /**
     * Unduh berkas materi secara terkontrol (privat, melalui otorisasi).
     */
    public function download(Request $request, Event $event, Material $material): StreamedResponse|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu untuk mengunduh materi.');
        }

        // Cegah id tampering / menebak id materi kegiatan lain
        if ($material->event_id !== $event->id) {
            abort(404, 'Materi tidak ditemukan pada kegiatan ini.');
        }

        $user = Auth::user();
        $this->ensureActiveParticipant($user, $event);

        if (! $user->isAdminKegiatan() && ! $material->is_published) {
            abort(404, 'Materi belum diterbitkan.');
        }

        // Jika materi berupa tautan eksternal
        if ($material->isLink()) {
            MaterialDownload::create([
                'material_id' => $material->id,
                'user_id' => $user->id,
                'downloaded_at' => now(),
            ]);

            return redirect()->away($material->path_or_url);
        }

        // Cari file di disk privat
        $disk = Storage::disk('private');
        $path = $material->path_or_url;

        if (! $disk->exists($path)) {
            // Fallback disk local
            if (Storage::disk('local')->exists($path)) {
                $disk = Storage::disk('local');
            } else {
                abort(404, 'Berkas materi tidak ditemukan di penyimpanan server.');
            }
        }

        // Catat unduhan
        MaterialDownload::create([
            'material_id' => $material->id,
            'user_id' => $user->id,
            'downloaded_at' => now(),
        ]);

        $safeFilename = Str::slug($material->title).'.'.$material->extension;

        return $disk->download($path, $safeFilename);
    }

    /**
     * Pratinjau dokumen PDF di browser (inline).
     */
    public function preview(Request $request, Event $event, Material $material): BinaryFileResponse|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu.');
        }

        if ($material->event_id !== $event->id) {
            abort(404, 'Materi tidak ditemukan pada kegiatan ini.');
        }

        $user = Auth::user();
        $this->ensureActiveParticipant($user, $event);

        if (! $user->isAdminKegiatan() && ! $material->is_published) {
            abort(404, 'Materi belum diterbitkan.');
        }

        if (! $material->isPdf()) {
            return redirect()->route('participant.material.download', [$event->slug, $material->id]);
        }

        $disk = Storage::disk('private');
        $path = $material->path_or_url;

        if (! $disk->exists($path)) {
            if (Storage::disk('local')->exists($path)) {
                $disk = Storage::disk('local');
            } else {
                abort(404, 'Berkas materi tidak ditemukan di penyimpanan server.');
            }
        }

        MaterialDownload::create([
            'material_id' => $material->id,
            'user_id' => $user->id,
            'downloaded_at' => now(),
        ]);

        $fullPath = $disk->path($path);
        $safeFilename = Str::slug($material->title).'.pdf';

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$safeFilename.'"',
        ]);
    }

    /**
     * Memastikan user adalah peserta dengan pendaftaran berstatus aktif atau pengurus/admin kegiatan.
     */
    protected function ensureActiveParticipant($user, Event $event): void
    {
        if ($user->isAdminKegiatan()) {
            return;
        }

        $enrollment = $user->enrollments()->where('event_id', $event->id)->first();

        if (! $enrollment) {
            abort(403, 'Akses ditolak. Anda belum terdaftar pada kegiatan ini.');
        }

        if ($enrollment->status === EnrollmentStatus::Menunggu) {
            abort(403, 'Pendaftaran Anda untuk kegiatan ini masih berstatus Menunggu Konfirmasi. Materi pembelajaran hanya dapat diakses setelah pendaftaran dikonfirmasi aktif oleh panitia DPD JULEHA Brebes.');
        }

        if ($enrollment->status === EnrollmentStatus::Ditolak) {
            abort(403, 'Pendaftaran Anda untuk kegiatan ini ditolak. Anda tidak memiliki akses ke materi pembelajaran.');
        }

        if ($enrollment->status !== EnrollmentStatus::Aktif && $enrollment->status !== EnrollmentStatus::Selesai) {
            abort(403, 'Akses ditolak. Status pendaftaran Anda tidak mengizinkan akses ke materi pembelajaran kegiatan ini.');
        }
    }
}
