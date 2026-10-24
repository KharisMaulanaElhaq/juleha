<?php

namespace Tests\Feature;

use App\Enums\EventType;
use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use App\Mail\DataRequestSubmittedMail;
use App\Models\AuditLog;
use App\Models\Consent;
use App\Models\DataRequest;
use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\LegalDocumentVersion;
use App\Models\ParticipantProfile;
use App\Models\Program;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\UserAnonymizationService;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LegalPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_published_document_returns_200_and_shows_version_and_date(): void
    {
        $response = $this->get('/kebijakan-privasi');

        $response->assertStatus(200);
        $response->assertSee('Kebijakan Privasi');
        $response->assertSee('Versi 1.0');
        $response->assertSee('rancangan keterbukaan publik');
    }

    public function test_draft_document_returns_404_publicly(): void
    {
        $draftDoc = LegalDocument::where('slug', LegalDocument::SLUG_SYARAT_KETENTUAN)->first();
        $this->assertNotNull($draftDoc);
        $this->assertEquals(LegalDocument::STATUS_DRAFT, $draftDoc->status);

        $response = $this->get('/syarat-ketentuan');
        $response->assertStatus(404);

        $responseDirect = $this->get('/hukum/syarat-ketentuan');
        $responseDirect->assertStatus(404);
    }

    public function test_publishing_draft_document_makes_it_accessible_and_records_version(): void
    {
        $doc = LegalDocument::where('slug', LegalDocument::SLUG_SYARAT_KETENTUAN)->first();

        // Update status menjadi published dan simpan versi
        LegalDocumentVersion::recordVersion(
            $doc,
            'Versi awal resmi diterbitkan setelah ditinjau pengurus.',
            null
        );

        $doc->update([
            'status' => LegalDocument::STATUS_PUBLISHED,
            'published_at' => now(),
            'version' => '1.0',
        ]);

        $response = $this->get('/syarat-ketentuan');
        $response->assertStatus(200);
        $response->assertSee('Syarat dan Ketentuan Layanan');
        $response->assertSee('Versi 1.0');

        $this->assertDatabaseHas('legal_document_versions', [
            'legal_document_id' => $doc->id,
            'version' => '1.0',
        ]);
    }

    public function test_super_admin_policy_guard_for_legal_documents(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $adminKegiatan = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN]);
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR_KONTEN]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        $this->actingAs($superAdmin);
        $this->assertTrue(LegalDocumentResource::canViewAny());

        $this->actingAs($adminKegiatan);
        $this->assertFalse(LegalDocumentResource::canViewAny());

        $this->actingAs($editor);
        $this->assertFalse(LegalDocumentResource::canViewAny());

        $this->actingAs($participant);
        $this->assertFalse(LegalDocumentResource::canViewAny());
    }

    public function test_participant_consent_per_purpose_storage(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Consent::grantPurpose($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY, '1.0');
        Consent::grantPurpose($user->id, Consent::PURPOSE_COMMUNICATION_ANNOUNCEMENTS, '1.0');

        $this->assertTrue(Consent::hasGranted($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY));
        $this->assertTrue(Consent::hasGranted($user->id, Consent::PURPOSE_COMMUNICATION_ANNOUNCEMENTS));
        $this->assertFalse(Consent::hasGranted($user->id, Consent::PURPOSE_PHOTO_DOCUMENTATION));

        Consent::withdrawPurpose($user->id, Consent::PURPOSE_COMMUNICATION_ANNOUNCEMENTS);

        $this->assertTrue(Consent::hasGranted($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY));
        $this->assertFalse(Consent::hasGranted($user->id, Consent::PURPOSE_COMMUNICATION_ANNOUNCEMENTS));
    }

    public function test_mandatory_consent_withdrawal_blocks_enrollment(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user->id,
            'nik' => '3329123456780001',
            'phone' => '081234567890',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'institution' => 'Masjid Agung Brebes',
        ]);

        $program = Program::first();
        $event = Event::create([
            'program_id' => $program->id,
            'title' => 'Bimtek Juleha Brebes Angkatan 1',
            'slug' => 'bimtek-juleha-angkatan-1',
            'type' => EventType::Bimtek,
            'status' => 'open',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(6),
            'registration_deadline' => now()->addDays(3),
            'location' => 'Brebes',
            'quota' => 50,
            'fee' => 0,
        ]);

        // Berikan persetujuan wajib akun & kegiatan
        Consent::grantPurpose($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY, '1.0');

        $enrollmentService = app(EnrollmentService::class);
        $enrollment = $enrollmentService->enroll($user, $event);
        $this->assertNotNull($enrollment);

        // Buat event kedua
        $event2 = Event::create([
            'program_id' => $program->id,
            'title' => 'Bimtek Juleha Brebes Angkatan 2',
            'slug' => 'bimtek-juleha-angkatan-2',
            'type' => EventType::Bimtek,
            'status' => 'open',
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(11),
            'registration_deadline' => now()->addDays(8),
            'location' => 'Brebes',
            'quota' => 50,
            'fee' => 0,
        ]);

        // Tarik persetujuan wajib
        Consent::withdrawPurpose($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY);

        // Percobaan mendaftar harus melempar DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Persetujuan pemrosesan data akun dan kegiatan belum aktif atau telah Anda tarik');

        $enrollmentService->enroll($user, $event2);
    }

    public function test_optional_consent_withdrawal_does_not_block_enrollment(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user->id,
            'nik' => '3329123456780002',
            'phone' => '081234567891',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'institution' => 'Masjid Al-Ittihad',
        ]);

        $program = Program::first();
        $event = Event::create([
            'program_id' => $program->id,
            'title' => 'Bimtek Juleha Brebes Opsional Test',
            'slug' => 'bimtek-opsional-test',
            'type' => EventType::Bimtek,
            'status' => 'open',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(6),
            'registration_deadline' => now()->addDays(3),
            'location' => 'Brebes',
            'quota' => 50,
            'fee' => 0,
        ]);

        // Wajib aktif, tapi opsional ditarik
        Consent::grantPurpose($user->id, Consent::PURPOSE_ACCOUNT_AND_ACTIVITY, '1.0');
        Consent::withdrawPurpose($user->id, Consent::PURPOSE_COMMUNICATION_ANNOUNCEMENTS);
        Consent::withdrawPurpose($user->id, Consent::PURPOSE_PHOTO_DOCUMENTATION);

        $enrollmentService = app(EnrollmentService::class);
        $enrollment = $enrollmentService->enroll($user, $event);

        $this->assertNotNull($enrollment);
        $this->assertEquals($user->id, $enrollment->user_id);
    }

    public function test_data_request_public_form_honeypot_and_sla(): void
    {
        Mail::fake();

        // 1. Honeypot check: jika website_url diisi bot, permintaan tidak disimpan di DB
        $botResponse = $this->post('/hak-data', [
            'name' => 'Bot Spammer',
            'contact' => 'bot@spammer.org',
            'request_type' => DataRequest::TYPE_ACCESS,
            'description' => 'Minta akses data bot spambot',
            'website_url' => 'http://spam-link.com',
        ]);
        $botResponse->assertRedirect();
        $this->assertDatabaseMissing('data_requests', [
            'name' => 'Bot Spammer',
        ]);

        // 2. Submission valid
        $validResponse = $this->post('/hak-data', [
            'name' => 'Ahmad Juleha',
            'contact' => 'ahmad@example.test',
            'request_type' => DataRequest::TYPE_ACCESS,
            'description' => 'Saya ingin meminta informasi riwayat data pelatihan saya.',
            'website_url' => '',
        ]);

        $validResponse->assertRedirect();
        $validResponse->assertSessionHas('success');

        $this->assertDatabaseHas('data_requests', [
            'name' => 'Ahmad Juleha',
            'contact' => 'ahmad@example.test',
            'request_type' => DataRequest::TYPE_ACCESS,
            'status' => DataRequest::STATUS_NEW,
        ]);

        $createdRequest = DataRequest::where('contact', 'ahmad@example.test')->first();
        $this->assertNotNull($createdRequest->due_at);
        $this->assertStringStartsWith('REQ-', $createdRequest->request_number);

        Mail::assertQueued(DataRequestSubmittedMail::class, function ($mail) use ($createdRequest) {
            return $mail->hasTo('ahmad@example.test') && $mail->dataRequest->id === $createdRequest->id;
        });
    }

    public function test_participant_privacy_json_download_contains_only_own_data_with_masked_nik(): void
    {
        $user1 = User::factory()->create(['name' => 'Budi Santoso', 'role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user1->id,
            'nik' => '3329123456789999',
            'phone' => '081234567890',
            'district' => 'Jatibarang',
            'village' => 'Jatibarang Kidul',
            'institution' => 'Masjid Jami',
        ]);

        $user2 = User::factory()->create(['name' => 'Cahyo Wibowo', 'role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user2->id,
            'nik' => '3329987654321111',
            'phone' => '089876543210',
            'district' => 'Bumiayu',
            'village' => 'Kalierang',
            'institution' => 'RPH Brebes',
        ]);

        $response = $this->actingAs($user1)->get('/dashboard/privasi-data/unduh-json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');

        $data = $response->json();

        $this->assertEquals('Budi Santoso', $data['akun']['nama_akun']);
        $this->assertEquals($user1->email, $data['akun']['email']);
        $this->assertEquals('3329********9999', $data['profil_peserta']['nik_tersamar']);

        // NIK asli tidak boleh muncul di JSON
        $this->assertStringNotContainsString('3329123456789999', $response->getContent());

        // Data user2 tidak boleh ada
        $this->assertStringNotContainsString('Cahyo Wibowo', $response->getContent());
        $this->assertStringNotContainsString('3329987654321111', $response->getContent());
    }

    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNull($response->headers->get('X-Powered-By'));
    }

    public function test_anonymization_service_removes_personal_data_and_logs_without_raw_nik(): void
    {
        $user = User::factory()->create(['name' => 'Fulan bin Fulan', 'email' => 'fulan@example.test', 'role' => User::ROLE_PARTICIPANT]);
        $profile = ParticipantProfile::create([
            'user_id' => $user->id,
            'nik' => '3329555566667777',
            'phone' => '085555666677',
            'district' => 'Wanasari',
            'village' => 'Pesantunan',
            'institution' => 'DKM Pesantunan',
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $service = app(UserAnonymizationService::class);
        $success = $service->anonymizeUser($user, 'REQ-20261024-TEST', $admin->id);

        $this->assertTrue($success);

        $user->refresh();
        $profile->refresh();

        $this->assertStringContainsString('Pengguna Dihapus', $user->name);
        $this->assertStringContainsString('@julehabrebes.org', $user->email);
        $this->assertNotNull($user->deleted_at);

        $this->assertNull($profile->nik);
        $this->assertNull($profile->nik_hash);
        $this->assertNull($profile->phone);

        // Periksa audit log: tidak boleh memuat NIK mentah atau email lama
        $latestAudit = AuditLog::where('action', 'anonymized')->latest('id')->first();
        $this->assertNotNull($latestAudit);
        $this->assertStringNotContainsString('3329555566667777', json_encode($latestAudit->details));
        $this->assertStringNotContainsString('fulan@example.test', json_encode($latestAudit->details));
    }
}
