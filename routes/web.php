<?php

use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\AttendanceScreenController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\ParticipantAuthController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\DataRequestController;
use App\Http\Controllers\LegalDocumentPublicController;
use App\Http\Controllers\Participant\AttendanceScanController;
use App\Http\Controllers\Participant\CertificateController;
use App\Http\Controllers\Participant\DashboardController;
use App\Http\Controllers\Participant\EnrollmentController;
use App\Http\Controllers\Participant\EventMaterialController;
use App\Http\Controllers\Participant\PrivacyDataController;
use App\Http\Controllers\Participant\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Models\BoardMember;
use App\Models\Event;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\Program;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes - DPD JULEHA Kabupaten Brebes (Dinamis dari Database)
|--------------------------------------------------------------------------
*/

// Beranda
Route::get('/', function () {
    $events = Event::notFinished()
        ->orderBy('starts_at')
        ->take(4)
        ->get()
        ->filter(fn (Event $e) => ! $e->isFinished())
        ->values();

    $hasOpenEvents = $events->filter(fn (Event $e) => $e->canRegister())->isNotEmpty();
    $posts = Post::published()->latest('published_at')->take(3)->get();
    $settings = Setting::all()->pluck('value', 'key');
    $faqs = Faq::active()->take(4)->get();
    $programs = Program::active()->take(6)->get();

    return view('pages.home', [
        'programs' => $programs,
        'events' => $events,
        'hasOpenEvents' => $hasOpenEvents,
        'posts' => $posts,
        'settings' => $settings,
        'faqs' => $faqs,
    ]);
})->name('home');

// Tentang Kami
Route::get('/tentang', function () {
    $settings = Setting::all()->pluck('value', 'key');
    $pelindungPembina = BoardMember::active()->pelindungPembina()->get();

    return view('pages.tentang', [
        'settings' => $settings,
        'pelindungPembina' => $pelindungPembina,
    ]);
})->name('tentang');

// Struktur Pengurus
Route::get('/pengurus', function () {
    $boardPeriod = Setting::get('board_period', '2024-2029');
    $boardSkNote = Setting::get('board_sk_note', '');

    $pelindungPembina = BoardMember::active()->pelindungPembina()->get();
    $pimpinanHarian = BoardMember::active()->pimpinanHarian()->get();
    $bidangMembers = BoardMember::active()->bidang()->get()->groupBy('division');

    return view('pages.pengurus', [
        'boardPeriod' => $boardPeriod,
        'boardSkNote' => $boardSkNote,
        'pelindungPembina' => $pelindungPembina,
        'pimpinanHarian' => $pimpinanHarian,
        'bidangMembers' => $bidangMembers,
        'members' => BoardMember::active()->get(),
    ]);
})->name('pengurus');

// Program Kerja
Route::get('/program', function () {
    $programs = Program::active()->get();
    $communityPrograms = $programs->where('category', 'Layanan Masyarakat')->values();
    $educationPrograms = $programs->where('category', 'Pendidikan & Sertifikasi')->values();

    return view('pages.program', [
        'programs' => $programs,
        'communityPrograms' => $communityPrograms,
        'educationPrograms' => $educationPrograms,
    ]);
})->name('program.index');

// Backward Compatibility / Redirects Program Lama
Route::redirect('/program/pelatihan-dasar', '/program/bimtek-pelatihan', 301);
Route::redirect('/program/binaan-masjid', '/program/syiar-edukasi', 301);
Route::redirect('/program/pengawasan-kurban', '/program/qurban', 301);
Route::redirect('/program/standardisasi-rph', '/program/syiar-edukasi', 301);
Route::redirect('/program/sosialisasi-asuh', '/program/syiar-edukasi', 301);
Route::redirect('/program/sertifikasi-bnsp', '/program/sertifikasi-kompetensi-bnsp', 301);
Route::redirect('/program/sertifikasi-kompetensi', '/program/sertifikasi-kompetensi-bnsp', 301);

Route::get('/program/{slug}', function (string $slug) {
    $program = Program::where('slug', strtolower(trim($slug)))
        ->where('is_active', true)
        ->firstOrFail();

    $otherPrograms = Program::active()
        ->where('id', '!=', $program->id)
        ->take(3)
        ->get();

    return view('pages.program-detail', [
        'program' => $program,
        'otherPrograms' => $otherPrograms,
    ]);
})->name('program.show');

// Bimbingan Teknis (Bimtek)
Route::get('/bimtek', function () {
    // Agenda yang sudah selesai tidak ditampilkan di tampilan website
    $allEvents = Event::notFinished()
        ->orderBy('starts_at')
        ->get()
        ->filter(fn (Event $e) => ! $e->isFinished())
        ->values();

    $openEvents = $allEvents->filter(fn (Event $e) => $e->canRegister())->values();
    $otherEvents = $allEvents->filter(fn (Event $e) => ! $e->canRegister())->values();

    return view('pages.bimtek', [
        'events' => $allEvents,
        'openEvents' => $openEvents,
        'otherEvents' => $otherEvents,
    ]);
})->name('bimtek.index');

Route::get('/bimtek/{slug}', function (string $slug) {
    $event = Event::where('slug', $slug)->first();

    if (! $event) {
        abort(404);
    }

    if ($event->isFinished()) {
        return redirect()->route('bimtek.index')
            ->with('info', 'Kegiatan "'.$event->title.'" telah selesai diselenggarakan dan tidak lagi ditampilkan di website.');
    }

    return view('pages.bimtek-detail', [
        'slug' => $slug,
        'event' => $event,
    ]);
})->name('bimtek.show');

// ==========================================
// AUTENTIKASI PESERTA & GOOGLE OAUTH
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [ParticipantAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/masuk', [ParticipantAuthController::class, 'login'])->middleware('throttle:6,1')->name('login.post');

    Route::get('/daftar', [ParticipantAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/daftar', [ParticipantAuthController::class, 'register'])->middleware('throttle:6,1')->name('register.post');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:10,1')->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:10,1')->name('auth.google.callback');
});

Route::post('/keluar', [ParticipantAuthController::class, 'logout'])->middleware('auth')->name('logout');

// ==========================================
// PORTAL PESERTA (PROTECTED - KHUSUS PESERTA)
// ==========================================
Route::middleware(['auth', 'participant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/profil', [ProfileController::class, 'edit'])->name('participant.profile');
    Route::post('/dashboard/profil', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('participant.profile.update');

    Route::get('/kegiatan/daftar/{event:slug}', [EnrollmentController::class, 'create'])->name('enrollment.create');
    Route::post('/kegiatan/daftar/{event:slug}', [EnrollmentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('enrollment.store');

    // Materi Pembelajaran Kelas Peserta (Terkontrol & Privat)
    Route::get('/dashboard/kegiatan/{event:slug}/materi', [EventMaterialController::class, 'index'])
        ->name('participant.event.materials');
    Route::get('/dashboard/kegiatan/{event:slug}/materi/{material}/download', [EventMaterialController::class, 'download'])
        ->name('participant.material.download');
    Route::get('/dashboard/kegiatan/{event:slug}/materi/{material}/preview', [EventMaterialController::class, 'preview'])
        ->name('participant.material.preview');

    // Alur Absen Peserta (Kamera Langsung & Kode Angka)
    Route::get('/absen', [AttendanceScanController::class, 'showAbsenForm'])
        ->name('attendance.absen');
    Route::post('/absen/kode', [AttendanceScanController::class, 'submitCode'])
        ->middleware('throttle:15,1')
        ->name('attendance.submit_code');

    // Presensi Peserta via Scan Kamera Bawaan HP / Tautan QR Dinamis
    Route::get('/presensi/{token}', [AttendanceScanController::class, 'scan'])
        ->middleware('throttle:30,1')
        ->name('attendance.scan');

    // Sertifikat Peserta
    Route::get('/dashboard/sertifikat', [CertificateController::class, 'index'])
        ->name('participant.certificates');
    Route::get('/dashboard/sertifikat/{certificate}/unduh', [CertificateController::class, 'download'])
        ->name('participant.certificates.download');

    // Privasi dan Hak Data Peserta
    Route::get('/dashboard/privasi-data', [PrivacyDataController::class, 'index'])->name('participant.privacy.index');
    Route::post('/dashboard/privasi-data/persetujuan/berikan', [PrivacyDataController::class, 'grant'])->name('participant.privacy.grant');
    Route::post('/dashboard/privasi-data/persetujuan/tarik', [PrivacyDataController::class, 'withdraw'])->name('participant.privacy.withdraw');
    Route::get('/dashboard/privasi-data/unduh-json', [PrivacyDataController::class, 'downloadJson'])->name('participant.privacy.download');
    Route::get('/dashboard/hak-data', [DataRequestController::class, 'portalCreate'])->name('data-rights.portal');
});

// ==========================================
// VERIFIKASI PUBLIK CATATAN SERTIFIKAT DPD
// ==========================================
Route::get('/verifikasi', [CertificateVerificationController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('certificate.verify');
Route::get('/verifikasi/{public_code}', [CertificateVerificationController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('certificate.verify.code');

// ==========================================
// ADMIN PRESENSI (PROTECTED - KHUSUS ADMIN)
// ==========================================
Route::middleware('auth')->group(function () {
    // Admin Presensi: Layar Proyektor & Polling
    Route::get('/admin/presensi/{session}/layar', [AttendanceScreenController::class, 'show'])
        ->name('admin.attendance.screen');
    Route::get('/admin/presensi/{session}/token', [AttendanceScreenController::class, 'token'])
        ->name('admin.attendance.screen.token');
    Route::get('/admin/presensi/{session}/stats', [AttendanceScreenController::class, 'stats'])
        ->name('admin.attendance.screen.stats');
    Route::post('/admin/presensi/{session}/toggle', [AttendanceScreenController::class, 'toggleOpen'])
        ->name('admin.attendance.screen.toggle');

    // Admin Presensi: Rekapitulasi Matriks & Ekspor Excel
    Route::get('/admin/kegiatan/{event}/rekap-presensi', [AttendanceReportController::class, 'matrix'])
        ->name('admin.attendance.matrix');
    Route::get('/admin/kegiatan/{event}/rekap-presensi/ekspor', [AttendanceReportController::class, 'export'])
        ->name('admin.attendance.export');
});

// Pendaftaran Bimtek Publik Lama (Dialihkan ke Portal Pendaftaran Resmi)
Route::get('/bimtek/{slug}/daftar', function (string $slug) {
    return redirect()->route('enrollment.create', $slug);
})->name('bimtek.daftar');
Route::post('/bimtek/{slug}/daftar', [RegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bimtek.daftar.store');

Route::get('/pendaftaran/sukses', [RegistrationController::class, 'success'])->name('registration.success');

// Berita & Edukasi
Route::get('/berita', function () {
    $posts = Post::published()->latest('published_at')->get();

    return view('pages.berita', [
        'posts' => $posts,
    ]);
})->name('berita.index');

Route::get('/berita/{slug}', function (string $slug) {
    $post = Post::where('slug', $slug)->first();

    if (! $post) {
        $first = Post::published()->first();
        if ($first) {
            $post = $first;
        } else {
            abort(404);
        }
    }

    $related = Post::published()
        ->where('id', '!=', $post->id)
        ->latest('published_at')
        ->take(3)
        ->get();

    return view('pages.berita-detail', [
        'slug' => $slug,
        'post' => $post,
        'related' => $related,
    ]);
})->name('berita.show');

// Galeri Dokumentasi
Route::get('/galeri', function () {
    $items = GalleryItem::orderBy('sort_order')->latest()->get();

    return view('pages.galeri', [
        'items' => $items,
    ]);
})->name('galeri');

// Tanya Jawab (FAQ)
Route::get('/faq', function () {
    $faqs = Faq::active()->get()->groupBy('category');

    return view('pages.faq', [
        'faqs' => $faqs,
    ]);
})->name('faq');

// Kontak & Sekretariat
Route::get('/kontak', function () {
    $settings = Setting::all()->pluck('value', 'key');

    return view('pages.kontak', [
        'settings' => $settings,
    ]);
})->name('kontak');

// Dokumen Hukum Publik & Riwayat Versi
Route::get('/kebijakan-privasi', [LegalDocumentPublicController::class, 'kebijakanPrivasi'])->name('kebijakan-privasi');
Route::get('/syarat-ketentuan', [LegalDocumentPublicController::class, 'syaratKetentuan'])->name('syarat-ketentuan');
Route::get('/penyimpanan-perangkat', [LegalDocumentPublicController::class, 'penyimpananPerangkat'])->name('penyimpanan-perangkat');
Route::get('/disclaimer', [LegalDocumentPublicController::class, 'disclaimer'])->name('disclaimer');
Route::get('/hak-cipta', [LegalDocumentPublicController::class, 'hakCipta'])->name('hak-cipta');
Route::get('/hukum/{slug}', [LegalDocumentPublicController::class, 'show'])->name('legal.show');
Route::get('/hukum/{slug}/riwayat', [LegalDocumentPublicController::class, 'history'])->name('legal.history');

// Saluran Hak Data Pribadi Publik
Route::get('/hak-data', [DataRequestController::class, 'create'])->name('data-rights.create');
Route::post('/hak-data', [DataRequestController::class, 'store'])->middleware('throttle:5,60')->name('data-rights.store');

// Eksekusi Migrasi Database & Pembersihan Cache
Route::get('/jalankan-migrasi', function () {
    Artisan::call('migrate', ['--force' => true]);
    $out = Artisan::output();

    $storageLink = 'Lewati';
    try {
        Artisan::call('storage:link');
        $storageLink = Artisan::output();
    } catch (Throwable $e) {
        $storageLink = 'Galat: '.$e->getMessage();
    }

    Artisan::call('optimize:clear');
    $clear = Artisan::output();

    $opcacheStatus = 'Tidak Aktif / Tidak Didukung';
    if (function_exists('opcache_reset')) {
        $resetResult = @opcache_reset();
        $opcacheStatus = $resetResult ? 'Berhasil Direset (OPcache Cleared)' : 'Gagal Direset';
    }

    $boardFormFile = app_path('Filament/Resources/BoardMembers/Schemas/BoardMemberForm.php');
    $boardFormExists = file_exists($boardFormFile);
    $boardFormSize = $boardFormExists ? filesize($boardFormFile) : 0;
    $boardFormMtime = $boardFormExists ? date('Y-m-d H:i:s', filemtime($boardFormFile)) : '-';

    $hasGoogleDrive = false;
    if ($boardFormExists) {
        $content = file_get_contents($boardFormFile);
        $hasGoogleDrive = str_contains($content, 'board-member-photo-picker');
    }

    $checkBadge = $hasGoogleDrive
        ? "<span style='background:#059669;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;'>SIAP: Fitur Google Picker & Dua Tombol Ditemukan ($boardFormSize bytes, $boardFormMtime)</span>"
        : "<span style='background:#dc2626;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;'>BELUM: File BoardMemberForm masih versi lama ($boardFormSize bytes, $boardFormMtime)</span>";

    $postsDebug = Post::latest('id')->take(3)->get(['id', 'title', 'slug', 'cover'])->toArray();

    return "<div style='font-family:sans-serif;max-width:680px;margin:40px auto;padding:28px;background:#ffffff;border:1px solid #cbd5e1;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.05);'>"
        ."<h2 style='color:#059669;margin-top:0;'>DPD JULEHA Brebes - Status Sistem & Migrasi</h2>"
        ."<div style='margin-bottom:18px;padding:12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;'>"
        .'<strong>Status Kode BoardMemberForm:</strong><br><br>'.$checkBadge.'<br><br>'
        .'<strong>Status PHP OPcache:</strong> '.htmlspecialchars($opcacheStatus).'<br>'
        .'<strong>Tautan Penyimpanan (Storage Link):</strong> '.htmlspecialchars($storageLink).'<br>'
        .'</div>'
        ."<h4 style='color:#334155;margin-bottom:6px;'>Data 3 Berita Terakhir di Database:</h4>"
        ."<pre style='background:#0f172a;color:#fbbf24;padding:16px;border-radius:10px;font-size:13px;overflow-x:auto;'>".htmlspecialchars(json_encode($postsDebug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)).'</pre>'
        ."<h4 style='color:#334155;margin-bottom:6px;'>Hasil Migrasi:</h4>"
        ."<pre style='background:#0f172a;color:#38bdf8;padding:16px;border-radius:10px;font-size:13px;'>".htmlspecialchars($out ?: 'Database sudah versi terbaru.').'</pre>'
        ."<h4 style='color:#334155;margin-bottom:6px;'>Pembersihan Cache:</h4>"
        ."<pre style='background:#0f172a;color:#4ade80;padding:16px;border-radius:10px;font-size:13px;'>".htmlspecialchars($clear).'</pre>'
        ."<p style='color:#059669;font-weight:bold;margin-top:20px;'><a href='/admin/board-members' style='color:#4f46e5;text-decoration:none;'>&larr; Buka Admin Board Members</a></p>"
        .'</div>';
})->name('artisan.migrate');

// Melayani berkas storage publik jika symlink public/storage belum ada di server cPanel
Route::get('storage/{path}', function (string $path) {
    if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
        abort(404);
    }

    $disk = Storage::disk('public');
    if ($disk->exists($path)) {
        $content = $disk->get($path);
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
    } else {
        $localDisk = Storage::disk('local');
        if ($localDisk->exists($path)) {
            $content = $localDisk->get($path);
            $mime = $localDisk->mimeType($path) ?: 'application/octet-stream';
        } else {
            abort(404);
        }
    }

    return response($content, 200, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*')->name('storage.fallback');

// Melayani berkas assets/lucide.min.js jika berkas fisik belum terunggah ke public/assets
Route::get('assets/lucide.min.js', function () {
    $path = public_path('assets/lucide.min.js');
    if (file_exists($path)) {
        if (is_readable($path)) {
            try {
                return response()->file($path, [
                    'Content-Type' => 'application/javascript',
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            } catch (Throwable) {
                // Lanjutkan ke fallback file_get_contents
            }
        }

        $content = @file_get_contents($path);
        if ($content !== false) {
            return response($content, 200, [
                'Content-Type' => 'application/javascript',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }
    }

    return redirect('https://unpkg.com/lucide@latest/dist/umd/lucide.min.js', 302);
});

// Backward Compatibility / Redirects
$redirects = [
    'blog' => '/berita',
    'blog.html' => '/berita',
    'blog-detail.html' => '/berita',
    'faq.html' => '/faq',
    'privacy.html' => '/kebijakan-privasi',
    'privacy-policy' => '/kebijakan-privasi',
    'terms.html' => '/syarat-ketentuan',
    'terms-and-conditions' => '/syarat-ketentuan',
    'portal/privasi-data' => '/dashboard/privasi-data',
];

foreach ($redirects as $from => $to) {
    Route::redirect($from, $to, 301);
}
