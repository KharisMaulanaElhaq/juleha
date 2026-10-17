<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\ParticipantProfile;
use App\Models\User;
use App\Services\AttendanceTokenService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class DynamicQrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected EventSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->event = Event::first() ?? Event::create([
            'title' => 'Bimtek Sembelih Halal Angkatan Brebes',
            'slug' => 'bimtek-sembelih-halal-brebes',
            'type' => EventType::Bimtek,
            'starts_at' => Carbon::now('Asia/Jakarta')->addDays(3),
            'location' => 'Islamic Centre Brebes',
            'quota' => 30,
            'fee' => 0,
            'status' => 'open',
        ]);

        $this->session = EventSession::create([
            'event_id' => $this->event->id,
            'title' => 'Sesi 1: Fiqih Ibadah Sembelih Syar\'i',
            'starts_at' => Carbon::now('Asia/Jakarta')->subMinutes(10),
            'ends_at' => Carbon::now('Asia/Jakarta')->addHours(2),
            'location' => 'Aula Utama',
            'attendance_open' => true,
        ]);
    }

    /**
     * 1. Tamu tidak login yang scan QR diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login_when_scanning_qr(): void
    {
        $tokenData = AttendanceTokenService::generateToken($this->session);

        $response = $this->get(route('attendance.scan', $tokenData['token']));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Peserta aktif berhasil scan QR, kehadiran tercatat dengan sumber qr dan waktu WIB.
     */
    public function test_active_participant_can_attend_via_valid_qr_token(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $tokenData = AttendanceTokenService::generateToken($this->session);

        $response = $this->actingAs($participant)
            ->get(route('attendance.scan', $tokenData['token']));

        $response->assertStatus(200);
        $response->assertSee('Absen Berhasil!');
        $response->assertSee($this->session->title);

        $this->assertDatabaseHas('attendances', [
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
            'status' => 'hadir',
            'source' => 'qr',
        ]);
    }

    /**
     * 3. Token kedaluwarsa atau token acak palsu ditolak.
     */
    public function test_expired_or_invalid_token_is_rejected(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Token palsu
        $responseFake = $this->actingAs($participant)
            ->get(route('attendance.scan', 'token_palsu_tidak_ada'));

        $responseFake->assertStatus(422);
        $responseFake->assertSee('Kode Sudah Kedaluwarsa');

        // Token kedaluwarsa sengaja di cache
        $expiredToken = 'token_expired_12345';
        Cache::put("attendance_token_{$expiredToken}", [
            'session_id' => $this->session->id,
            'expires_at' => Carbon::now('Asia/Jakarta')->subSeconds(10)->timestamp,
        ], 60);

        $responseExpired = $this->actingAs($participant)
            ->get(route('attendance.scan', $expiredToken));

        $responseExpired->assertStatus(422);
        $responseExpired->assertSee('Kode Sudah Kedaluwarsa');

        $this->assertDatabaseMissing('attendances', [
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
        ]);
    }

    /**
     * 4. Di luar jam sesi dan presensi ditutup ditolak.
     */
    public function test_session_outside_schedule_and_not_open_is_rejected(): void
    {
        $closedSession = EventSession::create([
            'event_id' => $this->event->id,
            'title' => 'Sesi Praktik Besok',
            'starts_at' => Carbon::now('Asia/Jakarta')->addDays(2)->setTime(8, 0),
            'ends_at' => Carbon::now('Asia/Jakarta')->addDays(2)->setTime(12, 0),
            'location' => 'RPH',
            'attendance_open' => false,
        ]);

        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $tokenData = AttendanceTokenService::generateToken($closedSession);

        $response = $this->actingAs($participant)
            ->get(route('attendance.scan', $tokenData['token']));

        $response->assertStatus(422);
        $response->assertSee('Absen Belum Dibuka');

        $this->assertDatabaseMissing('attendances', [
            'event_session_id' => $closedSession->id,
            'user_id' => $participant->id,
        ]);
    }

    /**
     * 5. Peserta belum terdaftar, berstatus menunggu, atau ditolak tidak bisa presensi (403).
     */
    public function test_unenrolled_or_pending_participant_is_forbidden_403(): void
    {
        $tokenData = AttendanceTokenService::generateToken($this->session);

        // Kasus A: User belum terdaftar
        $stranger = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $responseStranger = $this->actingAs($stranger)->get(route('attendance.scan', $tokenData['token']));
        $responseStranger->assertStatus(403);
        $responseStranger->assertSee('Belum Terdaftar');

        // Kasus B: User status Menunggu
        $pendingUser = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $pendingUser->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);
        $responsePending = $this->actingAs($pendingUser)->get(route('attendance.scan', $tokenData['token']));
        $responsePending->assertStatus(403);
        $responsePending->assertSee('Pendaftaran Menunggu Diperiksa');

        // Kasus C: User status Ditolak
        $rejectedUser = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $rejectedUser->id,
            'status' => EnrollmentStatus::Ditolak,
            'rejection_reason' => 'Kuota domisili penuh',
        ]);
        $responseRejected = $this->actingAs($rejectedUser)->get(route('attendance.scan', $tokenData['token']));
        $responseRejected->assertStatus(403);
        $responseRejected->assertSee('Pendaftaran Tidak Diterima');
    }

    /**
     * 6. Presensi ganda pada sesi yang sama ditolak (409 Conflict) dan tidak menduplikasi data.
     */
    public function test_duplicate_attendance_is_rejected_409(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $tokenData1 = AttendanceTokenService::generateToken($this->session);

        // Scan pertama -> Berhasil
        $this->actingAs($participant)
            ->get(route('attendance.scan', $tokenData1['token']))
            ->assertStatus(200);

        // Scan kedua dengan token baru
        $tokenData2 = AttendanceTokenService::generateToken($this->session);

        $responseDuplicate = $this->actingAs($participant)
            ->get(route('attendance.scan', $tokenData2['token']));

        $responseDuplicate->assertStatus(409);
        $responseDuplicate->assertSee('Anda Sudah Absen');

        $this->assertEquals(1, Attendance::where('event_session_id', $this->session->id)->where('user_id', $participant->id)->count());
    }

    /**
     * 7. Peserta aktif dapat absen lewat kode angka 6 digit.
     */
    public function test_active_participant_can_attend_via_valid_6_digit_numeric_code(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $tokenData = AttendanceTokenService::generateToken($this->session);
        $numericCode = $tokenData['numeric_code'];

        $response = $this->actingAs($participant)
            ->post(route('attendance.submit_code'), [
                'code' => $numericCode,
            ]);

        $response->assertStatus(200);
        $response->assertSee('Absen Berhasil!');

        $this->assertDatabaseHas('attendances', [
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
            'status' => 'hadir',
            'source' => 'code',
        ]);
    }

    /**
     * 8. Kode angka salah atau kedaluwarsa ditolak dengan pesan yang ramah.
     */
    public function test_wrong_or_expired_numeric_code_is_rejected(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Kode salah
        $responseWrong = $this->actingAs($participant)
            ->post(route('attendance.submit_code'), [
                'code' => '999999',
            ]);

        $responseWrong->assertStatus(422);
        $responseWrong->assertSee('Kode Angka Tidak Cocok');

        // Kode kedaluwarsa
        $expiredCode = '112233';
        Cache::put("attendance_code_{$expiredCode}", [
            'session_id' => $this->session->id,
            'expires_at' => Carbon::now('Asia/Jakarta')->subSeconds(10)->timestamp,
        ], 60);

        $responseExpired = $this->actingAs($participant)
            ->post(route('attendance.submit_code'), [
                'code' => $expiredCode,
            ]);

        $responseExpired->assertStatus(422);
        $responseExpired->assertSee('Kode Angka Tidak Cocok');
    }

    /**
     * 9. Rate limiting percobaan kode angka bekerja setelah 5 kali gagal.
     */
    public function test_numeric_code_rate_limiting_triggers_after_max_attempts(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        RateLimiter::clear('attendance_code_attempts:'.$participant->id);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($participant)
                ->post(route('attendance.submit_code'), ['code' => '000000']);
        }

        // Percobaan ke-6 harus ditolak 429 Rate Limited
        $responseBlocked = $this->actingAs($participant)
            ->post(route('attendance.submit_code'), ['code' => '000000']);

        $responseBlocked->assertStatus(429);
        $responseBlocked->assertSee('Percobaan Terlalu Banyak');
    }

    /**
     * 10. Dashboard menampilkan keempat kondisi kartu aksi dan NIK tidak tampil di dashboard.
     */
    public function test_dashboard_displays_all_four_action_card_states_and_hides_nik(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $profile = ParticipantProfile::create([
            'user_id' => $participant->id,
            'nik' => '3329123456789001',
            'full_name_ktp' => 'Budi Santoso',
            'phone' => '081234567890',
            'district' => 'Brebes',
            'village' => 'Gandasuli',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Kondisi A: Sesi hari ini & dibuka -> "ABSEN SEKARANG"
        $this->session->update(['attendance_open' => true]);
        $responseA = $this->actingAs($participant)->get(route('dashboard'));
        $responseA->assertStatus(200);
        $responseA->assertSee('ABSEN SEKARANG');
        $responseA->assertSee('Kamera tidak bisa? Isi kode angka');
        // NIK harus TIDAK tampil di dashboard
        $responseA->assertDontSee('3329123456789001');

        // Kondisi B: Sesi hari ini tapi belum dibuka
        $this->session->update([
            'attendance_open' => false,
            'starts_at' => Carbon::now('Asia/Jakarta')->addHours(2),
            'ends_at' => Carbon::now('Asia/Jakarta')->addHours(4),
        ]);
        $responseB = $this->actingAs($participant)->get(route('dashboard'));
        $responseB->assertStatus(200);
        $responseB->assertSee('Presensi belum dibuka, tunggu arahan panitia.');
        $responseB->assertDontSee('3329123456789001');

        // Kondisi D: Sudah absen hari ini
        Attendance::create([
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
            'status' => AttendanceStatus::Hadir,
            'source' => 'qr',
            'attended_at' => Carbon::now('Asia/Jakarta')->setTime(8, 30),
        ]);
        $responseD = $this->actingAs($participant)->get(route('dashboard'));
        $responseD->assertStatus(200);
        $responseD->assertSee('Anda sudah absen pukul');
        $responseD->assertDontSee('3329123456789001');

        // Kondisi C: Tidak ada sesi hari ini
        $this->session->delete();
        $responseC = $this->actingAs($participant)->get(route('dashboard'));
        $responseC->assertStatus(200);
        $responseC->assertSee('Belum ada kegiatan hari ini');
        $responseC->assertDontSee('3329123456789001');
    }

    /**
     * 11. Halaman /absen dapat dibuka oleh peserta aktif dengan opsi kamera dan kode angka.
     */
    public function test_participant_can_render_absen_page_with_camera_and_code_tabs(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $response = $this->actingAs($participant)->get(route('attendance.absen'));

        $response->assertStatus(200);
        $response->assertSee('Arahkan kamera ke layar di depan kelas');
        $response->assertSee('Ketik Kode Angka');
    }

    /**
     * 12. Admin dapat membuka layar proyektor presensi dan endpoint token/stats.
     */
    public function test_admin_can_access_attendance_screen_and_token_api(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN]);

        // Akses halaman layar proyektor
        $responseScreen = $this->actingAs($admin)
            ->get(route('admin.attendance.screen', $this->session->id));
        $responseScreen->assertStatus(200);
        $responseScreen->assertSee('LAYAR PROYEKTOR');
        $responseScreen->assertSee('KODE ABSEN 6 DIGIT');

        // Akses API refresh token
        $responseApi = $this->actingAs($admin)
            ->get(route('admin.attendance.screen.token', $this->session->id));
        $responseApi->assertStatus(200);
        $responseApi->assertJsonStructure([
            'success',
            'data' => ['token', 'numeric_code', 'formatted_code', 'url', 'refresh_in'],
        ]);

        // Peserta biasa dilarang membuka layar proyektor (403)
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $this->actingAs($participant)
            ->get(route('admin.attendance.screen', $this->session->id))
            ->assertStatus(403);
    }

    /**
     * 13. Presensi manual oleh admin mencatat sumber manual dan dicatat_oleh.
     */
    public function test_admin_can_record_manual_attendance(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $attendance = Attendance::create([
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
            'status' => AttendanceStatus::Izin,
            'source' => 'manual',
            'recorded_by' => $admin->id,
            'attended_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->assertDatabaseHas('attendances', [
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
            'status' => 'izin',
            'source' => 'manual',
            'recorded_by' => $admin->id,
        ]);
        $this->assertTrue($attendance->isManual());
    }

    /**
     * 14. Admin dapat melihat matriks rekapitulasi kehadiran dan ekspor file Excel (CSV).
     */
    public function test_admin_can_view_matrix_report_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'name' => 'Fauzi Juleha']);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        Attendance::create([
            'event_session_id' => $this->session->id,
            'user_id' => $participant->id,
            'status' => AttendanceStatus::Hadir,
            'source' => 'qr',
            'attended_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Matriks HTML view
        $matrixResponse = $this->actingAs($admin)
            ->get(route('admin.attendance.matrix', $this->event->id));
        $matrixResponse->assertStatus(200);
        $matrixResponse->assertSee('Matriks Kehadiran');
        $matrixResponse->assertSee('Fauzi Juleha');

        // Ekspor CSV
        $exportResponse = $this->actingAs($admin)
            ->get(route('admin.attendance.export', $this->event->id));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $exportResponse->headers->get('Content-Disposition'));
    }
}
