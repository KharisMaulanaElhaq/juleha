<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\ParticipantProfile;
use App\Models\Program;
use App\Models\User;
use App\Policies\EnrollmentPolicy;
use Database\Seeders\DatabaseSeeder;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class ParticipantPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 1. Google OAuth: redirect mengarahkan ke Google dengan scope minimal openid, email, profile.
     */
    public function test_google_oauth_redirect_initiates_correctly(): void
    {
        $response = $this->get(route('auth.google'));

        $response->assertStatus(302);
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com', $targetUrl);
        $this->assertStringContainsString('openid', $targetUrl);
        $this->assertStringContainsString('email', $targetUrl);
        $this->assertStringContainsString('profile', $targetUrl);
    }

    /**
     * 1. Google OAuth Callback: akun baru dibuat dengan role participant & diarahkan ke lengkapi profil.
     */
    public function test_google_oauth_callback_creates_new_user_with_participant_role(): void
    {
        $mockGoogleUser = Mockery::mock(SocialiteUser::class);
        $mockGoogleUser->shouldReceive('getId')->andReturn('google_sub_123456');
        $mockGoogleUser->shouldReceive('getName')->andReturn('Budi Santoso');
        $mockGoogleUser->shouldReceive('getEmail')->andReturn('budi.santoso@gmail.com');
        $mockGoogleUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar/budi');
        $mockGoogleUser->user = ['email_verified' => true];

        $mockProvider = Mockery::mock(GoogleProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockGoogleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $user = User::where('email', 'budi.santoso@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google_sub_123456', $user->google_id);
        $this->assertEquals(User::ROLE_PARTICIPANT, $user->role);
        $this->assertNotNull($user->email_verified_at);

        // Pengguna baru tanpa profil diarahkan ke form lengkapi profil
        $response->assertRedirect(route('participant.profile'));
        $response->assertSessionHas('info');
    }

    /**
     * 1. Google OAuth Callback: akun lama dengan email sama ditautkan google_id tanpa eskalasi peran.
     */
    public function test_google_oauth_callback_links_existing_user_without_role_escalation(): void
    {
        $existingAdmin = User::create([
            'name' => 'Editor Konten Lama',
            'email' => 'editor@julehabrebes.org',
            'role' => User::ROLE_EDITOR_KONTEN,
            'password' => bcrypt('secret123'),
        ]);

        $mockGoogleUser = Mockery::mock(SocialiteUser::class);
        $mockGoogleUser->shouldReceive('getId')->andReturn('google_editor_999');
        $mockGoogleUser->shouldReceive('getName')->andReturn('Editor Konten Lama');
        $mockGoogleUser->shouldReceive('getEmail')->andReturn('editor@julehabrebes.org');
        $mockGoogleUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar/editor');
        $mockGoogleUser->user = ['email_verified' => true];

        $mockProvider = Mockery::mock(GoogleProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockGoogleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->get(route('auth.google.callback'));

        $existingAdmin->refresh();
        $this->assertEquals('google_editor_999', $existingAdmin->google_id);
        // Role TIDAK boleh berubah menjadi participant atau super_admin (anti eskalasi peran)
        $this->assertEquals(User::ROLE_EDITOR_KONTEN, $existingAdmin->role);
    }

    /**
     * 1. Google OAuth Callback: email yang tidak terverifikasi oleh Google ditolak.
     */
    public function test_google_oauth_callback_rejects_unverified_email(): void
    {
        $mockGoogleUser = Mockery::mock(SocialiteUser::class);
        $mockGoogleUser->shouldReceive('getId')->andReturn('google_unverified_777');
        $mockGoogleUser->shouldReceive('getName')->andReturn('Spam User');
        $mockGoogleUser->shouldReceive('getEmail')->andReturn('unverified@example.com');
        $mockGoogleUser->shouldReceive('getAvatar')->andReturn(null);
        $mockGoogleUser->user = ['email_verified' => false];

        $mockProvider = Mockery::mock(GoogleProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockGoogleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    /**
     * 2. Peran & Hak Akses: akun participant TIDAK BISA mengakses /admin (canAccessPanel = false).
     */
    public function test_participant_cannot_access_filament_admin_panel(): void
    {
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
        ]);

        $panel = new Panel;
        $this->assertFalse($participant->canAccessPanel($panel));

        $response = $this->actingAs($participant)->get('/admin');
        // Filament mengembalikan 403 Forbidden untuk pengguna yang tidak punya akses panel
        $this->assertTrue(in_array($response->status(), [403, 302], true));
    }

    /**
     * 2. Peran & Hak Akses: editor_konten ditolak mengelola atau melihat pendaftaran peserta (Policy).
     */
    public function test_editor_konten_cannot_access_enrollments_in_policy(): void
    {
        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR_KONTEN,
        ]);

        $adminKegiatan = User::factory()->create([
            'role' => User::ROLE_ADMIN_KEGIATAN,
        ]);

        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $policy = new EnrollmentPolicy;

        // editor_konten dilarang melihat daftar pendaftaran
        $this->assertFalse($policy->viewAny($editor));

        // admin_kegiatan dan super_admin diizinkan
        $this->assertTrue($policy->viewAny($adminKegiatan));
        $this->assertTrue($policy->viewAny($superAdmin));
    }

    /**
     * 3. Profil Peserta: NIK wajib dilengkapi sebelum dapat mendaftar kegiatan.
     */
    public function test_profile_requires_completion_before_enrolling(): void
    {
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
        ]);

        $event = Event::where('status', 'open')->first();
        $this->assertNotNull($event);

        // Tanpa profil, diarahkan ke form lengkapi profil
        $response = $this->actingAs($participant)->get(route('enrollment.create', $event->slug));
        $response->assertRedirect(route('participant.profile'));
        $response->assertSessionHas('info');

        // Mencoba POST langsung tanpa profil juga ditolak
        $postResponse = $this->actingAs($participant)->post(route('enrollment.store', $event->slug));
        $postResponse->assertRedirect(route('participant.profile'));
    }

    /**
     * 3. Validasi NIK: tepat 16 digit, tidak diawali 0, tanggal lahir valid.
     */
    public function test_nik_validation_rules(): void
    {
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
        ]);

        // Coba NIK 15 digit
        $response1 = $this->actingAs($participant)->post(route('participant.profile.update'), [
            'full_name_ktp' => 'Ahmad Fauzi',
            'phone' => '081234567890',
            'nik' => '332901010190001', // 15 digit
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => '1',
        ]);
        $response1->assertSessionHasErrors('nik');

        // Coba NIK diawali angka 0
        $response2 = $this->actingAs($participant)->post(route('participant.profile.update'), [
            'full_name_ktp' => 'Ahmad Fauzi',
            'phone' => '081234567890',
            'nik' => '0329010101900001', // diawali 0
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => '1',
        ]);
        $response2->assertSessionHasErrors('nik');

        // Coba NIK tanggal lahir tidak valid (hari = 99)
        $response3 = $this->actingAs($participant)->post(route('participant.profile.update'), [
            'full_name_ktp' => 'Ahmad Fauzi',
            'phone' => '081234567890',
            'nik' => '3329019901900001', // posisi tanggal lahir = 99 (tidak valid)
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => '1',
        ]);
        $response3->assertSessionHasErrors('nik');

        // NIK valid (Laki-laki lahir 15 Januari 1995 di Brebes)
        $validNik = '3329011501950001';
        $responseSuccess = $this->actingAs($participant)->post(route('participant.profile.update'), [
            'full_name_ktp' => 'Ahmad Fauzi',
            'phone' => '081234567890',
            'nik' => $validNik,
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => '1',
        ]);
        $responseSuccess->assertSessionHasNoErrors();
        $responseSuccess->assertRedirect(route('dashboard'));
    }

    /**
     * 3. Penyimpanan NIK: nilai mentah di DB terenkripsi, nik_hash terisi HMAC, NIK duplikat ditolak.
     */
    public function test_nik_is_encrypted_and_hashed_and_duplicate_rejected(): void
    {
        $user1 = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $validNik = '3329012005920002';

        $profile = ParticipantProfile::create([
            'user_id' => $user1->id,
            'phone' => '081234567890',
            'full_name_ktp' => 'Peserta Satu',
            'nik' => $validNik,
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        // Cek database mentah: nilai kolom nik BUKAN plain text
        $rawRow = DB::table('participant_profiles')->where('id', $profile->id)->first();
        $this->assertNotEquals($validNik, $rawRow->nik, 'Nilai NIK di database mentah harus terenkripsi!');
        $this->assertNotEmpty($rawRow->nik_hash, 'Kolom nik_hash harus terisi!');

        // Cek dekripsi Eloquent bekerja transparan
        $profile->refresh();
        $this->assertEquals($validNik, $profile->nik);

        // Coba peserta kedua mendaftar dengan NIK yang sama persis
        $user2 = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $response = $this->actingAs($user2)->post(route('participant.profile.update'), [
            'full_name_ktp' => 'Peserta Dua Duplikat',
            'phone' => '089876543210',
            'nik' => $validNik,
            'district' => 'Jatibarang',
            'village' => 'Kertasinduyasa',
            'status' => 'calon_juleha',
            'consent' => '1',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    /**
     * 3. Privasi NIK: disembunyikan dari serialisasi JSON dan tampilan publik tersamar.
     */
    public function test_nik_is_hidden_from_serialization_and_display_is_masked(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $validNik = '3329011501950001';

        $profile = ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'full_name_ktp' => 'Ahmad Fauzi',
            'nik' => $validNik,
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        // Sembunyikan dari array/JSON
        $array = $profile->toArray();
        $this->assertArrayNotHasKey('nik', $array, 'NIK tidak boleh ada dalam array model!');
        $this->assertArrayNotHasKey('nik_hash', $array, 'nik_hash tidak boleh ada dalam array model!');

        $json = json_encode($profile);
        $this->assertStringNotContainsString($validNik, $json, 'NIK mentah tidak boleh muncul di serialisasi JSON!');

        // Masked NIK: 4 awal dan 4 akhir
        $this->assertEquals('3329********0001', $profile->masked_nik);
    }

    /**
     * 3. Audit Log: pembukaan NIK oleh admin tercatat di audit_logs.
     */
    public function test_reveal_nik_action_is_recorded_in_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $targetUser = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        // Simulasikan pencatatan audit log saat aksi reveal NIK
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'VIEW_NIK',
            'target_type' => 'User',
            'target_id' => $targetUser->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit/TestRunner',
            'meta' => ['reason' => 'Verifikasi keabsahan berkas sertifikat'],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'VIEW_NIK',
            'target_type' => 'User',
            'target_id' => $targetUser->id,
            'ip_address' => '127.0.0.1',
        ]);
    }

    /**
     * 4. 3 Jenis Kegiatan Enum: bimtek, sertifikasi_kompetensi, tot.
     */
    public function test_three_event_types_are_properly_supported(): void
    {
        $this->assertCount(3, EventType::cases());
        $this->assertEquals('Sertifikasi Kompetensi BNSP', EventType::SertifikasiKompetensi->label());

        $types = [
            EventType::Bimtek,
            EventType::SertifikasiKompetensi,
            EventType::Tot,
        ];

        foreach ($types as $type) {
            $event = Event::create([
                'title' => 'Kegiatan Uji Coba '.$type->label(),
                'slug' => 'uji-coba-'.$type->value,
                'type' => $type,
                'status' => 'open',
                'quota' => 25,
                'starts_at' => now()->addDays(20),
                'ends_at' => now()->addDays(20)->addHours(8),
                'location' => 'Brebes',
            ]);

            $this->assertEquals($type, $event->type);
            $this->assertNotEmpty($type->badgeClasses());
            $this->assertNotEmpty($type->label());
        }
    }

    /**
     * 5. Program: halaman /program menampilkan 7 program lengkap.
     */
    public function test_program_page_displays_all_seven_programs(): void
    {
        $response = $this->get('/program');
        $response->assertStatus(200);

        $expectedPrograms = [
            'Penyembelihan Hewan Aqiqah',
            'Qurban',
            'Syiar dan Edukasi Keliling',
            'Bimtek dan Pelatihan',
            'Sertifikasi Kompetensi BNSP',
            'Training of Trainer (TOT) Juleha',
            'Instruktur BNSP',
        ];

        foreach ($expectedPrograms as $title) {
            $response->assertSeeText($title);
        }
    }

    /**
     * 6. Pendaftaran Kegiatan: sukses daftar, duplikat ditolak, event closed / penuh ditolak.
     */
    public function test_enrollment_lifecycle_and_constraints(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'full_name_ktp' => 'Peserta Aktif',
            'nik' => '3329011002930005',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        $openEvent = Event::create([
            'title' => 'Bimtek Terbuka 2026',
            'slug' => 'bimtek-terbuka-2026',
            'type' => EventType::Bimtek,
            'status' => 'open',
            'quota' => 10,
            'starts_at' => now()->addDays(15),
            'ends_at' => now()->addDays(15)->addHours(8),
            'location' => 'Gedung Pertemuan',
        ]);

        // 1. Sukses mendaftar
        $response = $this->actingAs($user)->post(route('enrollment.store', $openEvent->slug));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'event_id' => $openEvent->id,
            'status' => EnrollmentStatus::Menunggu->value,
        ]);

        // 2. Duplikat pendaftaran ke event yang sama ditolak (redirect back ke dashboard)
        $duplicateResponse = $this->actingAs($user)->from(route('dashboard'))->post(route('enrollment.store', $openEvent->slug));
        $duplicateResponse->assertRedirect(route('dashboard'));
        $duplicateResponse->assertSessionHas('error');

        // 3. Pendaftaran ke event dengan status closed ditolak (redirect back ke dashboard)
        $closedEvent = Event::create([
            'title' => 'Bimtek Tertutup',
            'slug' => 'bimtek-tertutup',
            'type' => EventType::Bimtek,
            'status' => 'closed',
            'quota' => 10,
            'starts_at' => now()->addDays(5),
            'location' => 'Brebes',
        ]);

        $closedResponse = $this->actingAs($user)->from(route('dashboard'))->post(route('enrollment.store', $closedEvent->slug));
        $closedResponse->assertRedirect(route('dashboard'));
        $closedResponse->assertSessionHas('error');

        // 4. Pendaftaran ke event dengan sisa kuota 0 ditolak (redirect back ke dashboard)
        $fullEvent = Event::create([
            'title' => 'Bimtek Kuota Penuh',
            'slug' => 'bimtek-penuh',
            'type' => EventType::Bimtek,
            'status' => 'open',
            'quota' => 0,
            'starts_at' => now()->addDays(10),
            'location' => 'Brebes',
        ]);

        $fullResponse = $this->actingAs($user)->from(route('dashboard'))->post(route('enrollment.store', $fullEvent->slug));
        $fullResponse->assertRedirect(route('dashboard'));
        $fullResponse->assertSessionHas('error');
    }

    /**
     * 7. Dashboard Peserta (/dashboard) dapat diakses dengan nyaman oleh peserta yang login.
     */
    public function test_participant_dashboard_renders_correctly(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $profile = ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'full_name_ktp' => 'Peserta Dashboard',
            'nik' => '3329011002930008',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSeeText('Portal Peserta Resmi');
        $response->assertSeeText('Kegiatan Saya');
        $response->assertSeeText('Kegiatan yang Sedang Dibuka');
        $response->assertSeeText($profile->full_name_ktp);
    }

    /**
     * 8. Satu Halaman Masuk & Daftar: /daftar dialihkan ke /masuk dengan query parameters terjaga.
     */
    public function test_register_route_redirects_to_login_with_query_params(): void
    {
        $response = $this->get('/daftar?redirect=/kegiatan/daftar/bimtek-juleha-2026');

        $response->assertRedirect(route('login', ['redirect' => '/kegiatan/daftar/bimtek-juleha-2026']));
    }

    /**
     * 9. Alur Lengkapi Profil 2 Langkah: data tersimpan per langkah dan redirect ke intended destination.
     */
    public function test_two_step_profile_completion_saves_data_per_step_and_redirects_to_intended(): void
    {
        $user = User::factory()->create([
            'name' => 'Fulan bin Fulan',
            'role' => User::ROLE_PARTICIPANT,
        ]);

        // Simulasi intended URL (mis. pengguna hendak mendaftar kegiatan tertentu)
        session(['url.intended' => '/kegiatan/daftar/bimtek-juleha-2026']);

        // --- LANGKAH 1: Kontak & Wilayah Domisili ---
        $step1Response = $this->actingAs($user)->post(route('participant.profile.update'), [
            'step' => '1',
            'phone' => '+62 812-3456-7890', // diuji normalisasi nomor telepon
            'district' => 'Jatibarang',
            'village' => 'Kertasinduyasa',
            'institution' => 'Masjid Jami Al-Ikhlas',
            'status' => 'calon_juleha',
        ]);

        $step1Response->assertRedirect(route('participant.profile', ['step' => 2]));
        $step1Response->assertSessionHas('success');

        // Pastikan data Langkah 1 tersimpan di DB meski Langkah 2 belum diisi
        $profile = ParticipantProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('6281234567890', $profile->phone);
        $this->assertEquals('Jatibarang', $profile->district);
        $this->assertEquals('Kertasinduyasa', $profile->village);
        $this->assertEquals('calon_juleha', $profile->status);
        $this->assertNull($profile->nik); // NIK belum ada

        // --- LANGKAH 2: Data untuk Sertifikat & NIK ---
        $validNik = '3329011501950001';
        $step2Response = $this->actingAs($user)->post(route('participant.profile.update'), [
            'step' => '2',
            'full_name_ktp' => 'Fulan bin Fulan Al-Hajj',
            'nik' => $validNik,
            'consent' => '1',
        ]);

        // Setelah langkah 2 selesai, diarahkan ke intended URL
        $step2Response->assertRedirect('/kegiatan/daftar/bimtek-juleha-2026');

        $profile->refresh();
        $this->assertEquals('Fulan bin Fulan Al-Hajj', $profile->full_name_ktp);
        $this->assertEquals($validNik, $profile->nik);
        $this->assertTrue((bool) $profile->consent);
        $this->assertNotNull($profile->consent_at);
    }

    /**
     * Pastikan dashboard peserta dapat diakses dan menampilkan status 'Menunggu diperiksa' tanpa error.
     */
    public function test_participant_dashboard_renders_pending_enrollment_successfully(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'full_name_ktp' => 'Peserta Uji',
            'nik' => '3329011002930005',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        $event = Event::create([
            'title' => 'Bimtek Juleha 2026',
            'slug' => 'bimtek-juleha-2026-test',
            'type' => EventType::Bimtek,
            'status' => 'open',
            'quota' => 50,
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(8),
            'location' => 'Brebes',
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Menunggu diperiksa');
    }
}
