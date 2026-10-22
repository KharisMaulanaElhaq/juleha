<?php

use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\AttendanceScreenController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\ParticipantAuthController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\Participant\AttendanceScanController;
use App\Http\Controllers\Participant\CertificateController;
use App\Http\Controllers\Participant\DashboardController;
use App\Http\Controllers\Participant\EnrollmentController;
use App\Http\Controllers\Participant\EventMaterialController;
use App\Http\Controllers\Participant\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Models\BoardMember;
use App\Models\Event;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\Program;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

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

    return view('pages.tentang', [
        'settings' => $settings,
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
});

// ==========================================
// VERIFIKASI PUBLIK CATATAN SERTIFIKAT DPD
// ==========================================
Route::get('/verifikasi', [CertificateVerificationController::class, 'index'])
    ->middleware('throttle:15,1')
    ->name('certificate.verify');
Route::get('/verifikasi/{public_code}', [CertificateVerificationController::class, 'show'])
    ->middleware('throttle:15,1')
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

// Kebijakan Privasi
Route::get('/kebijakan-privasi', function () {
    return view('pages.kebijakan-privasi');
})->name('kebijakan-privasi');

// Backward Compatibility / Redirects
$redirects = [
    'blog' => '/berita',
    'blog.html' => '/berita',
    'blog-detail.html' => '/berita',
    'faq.html' => '/faq',
    'privacy.html' => '/kebijakan-privasi',
    'privacy-policy' => '/kebijakan-privasi',
    'terms.html' => '/tentang',
    'terms-and-conditions' => '/tentang',
];

foreach ($redirects as $from => $to) {
    Route::redirect($from, $to, 301);
}
