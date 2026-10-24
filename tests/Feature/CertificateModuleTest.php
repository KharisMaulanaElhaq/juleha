<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Mail\CertificateExpiryReminderMail;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\ParticipantProfile;
use App\Models\Setting;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CertificateModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Mail::fake();

        Setting::set('site_name', 'DPD JULEHA Kabupaten Brebes');
        Setting::set('cert_issuer_name', 'LSP Pertanian RI');
        Setting::set('cert_issuer_license', 'BNSP-LSP-021-ID');
        Setting::set('cert_validity_years', '3');
        Setting::set('cert_reminder_enabled', '1');
    }

    private function createRealPdfFile(string $filename = 'sertifikat-asli.pdf', int $sizeInKb = 100): UploadedFile
    {
        // Konten PDF asli diawali magic bytes '%PDF-'
        $content = "%PDF-1.4\n%âãÏÓ\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000015 00000 n \n0000000060 00000 n \n0000000111 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF\n";
        $padding = str_repeat(' ', max(0, ($sizeInKb * 1024) - strlen($content)));

        return UploadedFile::fake()->createWithContent($filename, $content.$padding);
    }

    private function createFakePdfFile(string $filename = 'fake.pdf'): UploadedFile
    {
        // File berekstensi .pdf tapi bukan PDF asli (tidak ada %PDF- di awal)
        return UploadedFile::fake()->createWithContent($filename, "INI BUKAN FILE PDF ASLI\nHANYA TEKS BIASA");
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Uji Kompetensi Juleha Brebes Angkatan '.rand(1, 99999),
            'short_title' => 'Sertifikasi BNSP',
            'slug' => 'sertifikasi-bnsp-'.rand(1000, 999999),
            'type' => EventType::SertifikasiKompetensi,
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(6),
            'registration_deadline' => now()->addDays(4),
            'location' => 'RPH Brebes',
            'quota' => 30,
            'fee' => 0,
            'status' => 'open',
        ], $overrides));
    }

    public function test_full_flow_record_competent_upload_pdf_download_and_public_verification(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => 'Kharis Maulana Elhaq',
            'email' => 'kharis@julehabrebes.org',
        ]);
        ParticipantProfile::create([
            'user_id' => $participant->id,
            'full_name_ktp' => 'Kharis Maulana Elhaq',
            'phone' => '082324448886',
            'nik' => '3329012345670001',
            'nik_hash' => hash_hmac('sha256', '3329012345670001', env('NIK_HASH_KEY', 'test_key')),
            'district' => 'Wanasari',
            'village' => 'Pesantunan',
            'consent' => true,
            'consent_version' => ParticipantProfile::CURRENT_CONSENT_VERSION,
        ]);

        $event = $this->createEvent(['title' => 'Uji Kompetensi Juleha Brebes Angkatan 1']);

        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // 1. Admin mencatat hasil Kompeten
        $data = [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal Level 2',
            'certificate_number' => '001/LSP-P/BNSP/2026',
            'issued_at' => '2026-10-01',
            'internal_note' => 'Lulus seluruh unit uji kompetensi.',
        ];

        $certificate = CertificateService::recordResult($enrollment, $data, $superAdmin->id);

        $this->assertNotNull($certificate);
        $this->assertEquals('kompeten', $certificate->result);
        $this->assertEquals('001/LSP-P/BNSP/2026', $certificate->certificate_number);
        $this->assertEquals('LSP Pertanian RI', $certificate->issuer_name);
        $this->assertEquals('2026-10-01', $certificate->issued_at->format('Y-m-d'));
        // Masa berlaku otomatis 3 tahun sejak issued_at
        $this->assertEquals('2029-10-01', $certificate->valid_until->format('Y-m-d'));
        $this->assertNotNull($certificate->public_code);
        $this->assertEquals(12, strlen($certificate->public_code));

        // 2. Admin mengunggah berkas PDF asli dari LSP
        $pdfFile = $this->createRealPdfFile('sertifikat-kharis.pdf', 50);
        $certificate = CertificateService::uploadPdf($certificate, $pdfFile, $superAdmin->id);

        $this->assertNotNull($certificate->file_path);
        Storage::disk('private')->assertExists($certificate->file_path);
        $this->assertNotEmpty($certificate->file_sha256);
        $this->assertEquals(64, strlen($certificate->file_sha256));

        // Enrollment otomatis ditandai Selesai
        $this->assertEquals(EnrollmentStatus::Selesai, $enrollment->fresh()->status);

        // Log audit tercatat
        $this->assertDatabaseHas('audit_logs', [
            'target_type' => 'Certificate',
            'target_id' => $certificate->id,
            'action' => 'file_uploaded',
        ]);

        // 3. Peserta melihat sertifikat di portal
        $this->actingAs($participant)
            ->get(route('participant.certificates'))
            ->assertOk()
            ->assertSee('Sertifikat Saya')
            ->assertSee('Juru Sembelih Halal Level 2')
            ->assertSee('001/LSP-P/BNSP/2026')
            ->assertSee('LSP Pertanian RI')
            ->assertSee('Unduh sertifikat (PDF)');

        // 4. Peserta mengunduh sertifikat via route terkontrol
        $downloadResponse = $this->actingAs($participant)
            ->get(route('participant.certificates.download', $certificate));

        $downloadResponse->assertOk();
        $this->assertEquals('application/pdf', $downloadResponse->headers->get('content-type'));
        $this->assertStringContainsString('no-cache', $downloadResponse->headers->get('cache-control'));
        $this->assertStringContainsString('Sertifikat-juru-sembelih-halal-level-2-kharis-maulana-elhaq.pdf', $downloadResponse->headers->get('content-disposition'));

        // 5. Verifikasi publik
        $verifyResponse = $this->get(route('certificate.verify.code', $certificate->public_code));
        $verifyResponse->assertOk()
            ->assertSee('Verifikasi Sertifikat Kompetensi')
            ->assertSee('Kharis M. E.') // Nama disamarkan!
            ->assertSee('001/LSP-P/BNSP/2026')
            ->assertSee('Juru Sembelih Halal Level 2')
            ->assertSee('LSP Pertanian RI')
            ->assertSee('BNSP-LSP-021-ID')
            ->assertDontSee('3329012345670001') // NIK TIDAK MUNCUL
            ->assertDontSee('082324448886') // WA TIDAK MUNCUL
            ->assertDontSee('kharis@julehabrebes.org') // Email TIDAK MUNCUL
            ->assertDontSee('Pesantunan'); // Desa TIDAK MUNCUL

        // Verifikasi pencarian nomor via form oleh pengunjung umum (tidak menampilkan nama pemegang)
        auth()->logout();
        $searchResponse = $this->get(route('certificate.verify', ['q' => '001/LSP-P/BNSP/2026']));
        $searchResponse->assertOk()
            ->assertSee('001/LSP-P/BNSP/2026')
            ->assertSee('Juru Sembelih Halal Level 2')
            ->assertDontSee('Kharis Maulana Elhaq')
            ->assertDontSee('Kharis M. E.')
            ->assertDontSee('Nama Pemegang (Tersamar UU PDP)');
    }

    public function test_valid_until_respects_cert_validity_years_setting(): void
    {
        Setting::set('cert_validity_years', '5');

        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $certificate = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'CERT-VALID-5YRS',
            'issued_at' => '2026-06-15',
        ]);

        $this->assertEquals('2031-06-15', $certificate->valid_until->format('Y-m-d'));
    }

    public function test_derived_statuses_calculation(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        // 1. Status Aktif (> 90 hari tersisa)
        $event1 = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $certAktif = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event1->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $participant->id,
            'event_id' => $event1->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema A',
            'certificate_number' => 'NUM-AKTIF',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYear(),
            'valid_until' => now()->addDays(200),
        ]);
        $this->assertEquals(Certificate::STATUS_AKTIF, $certAktif->getComputedStatus());
        $this->assertEquals('Aktif', $certAktif->status_label);

        // 2. Status Segera Berakhir (<= 90 hari tersisa)
        $event2 = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $certExpiring = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event2->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $participant->id,
            'event_id' => $event2->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema B',
            'certificate_number' => 'NUM-EXPIRING',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYears(3),
            'valid_until' => now()->addDays(45),
        ]);
        $this->assertEquals(Certificate::STATUS_SEGERA_BERAKHIR, $certExpiring->getComputedStatus());
        $this->assertEquals('Segera Berakhir', $certExpiring->status_label);

        // 3. Status Kedaluwarsa (valid_until telah lewat)
        $event3 = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $certExpired = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event3->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $participant->id,
            'event_id' => $event3->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema C',
            'certificate_number' => 'NUM-EXPIRED',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYears(4),
            'valid_until' => now()->subDays(10),
        ]);
        $this->assertEquals(Certificate::STATUS_KEDALUWARSA, $certExpired->getComputedStatus());
        $this->assertEquals('Kedaluwarsa', $certExpired->status_label);

        // 4. Status Dicabut
        $event4 = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $certRevoked = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event4->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $participant->id,
            'event_id' => $event4->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema D',
            'certificate_number' => 'NUM-REVOKED',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYear(),
            'valid_until' => now()->addYears(2),
            'revoked_at' => now(),
            'revoked_reason' => 'Pelanggaran kode etik profesi',
        ]);
        $this->assertEquals(Certificate::STATUS_DICABUT, $certRevoked->getComputedStatus());
        $this->assertEquals('Dicabut', $certRevoked->status_label);

        // 5. Status Belum Kompeten
        $event5 = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $certNotCompetent = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event5->id, 'status' => EnrollmentStatus::Aktif])->id,
            'user_id' => $participant->id,
            'event_id' => $event5->id,
            'result' => Certificate::RESULT_BELUM_KOMPETEN,
            'scheme_name' => 'Skema E',
        ]);
        $this->assertEquals(Certificate::STATUS_BELUM_KOMPETEN, $certNotCompetent->getComputedStatus());
        $this->assertEquals('Belum Kompeten', $certNotCompetent->status_label);
    }

    public function test_download_authorization_and_restrictions(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $otherParticipant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR_KONTEN]);

        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $enrollment = Enrollment::create([
            'user_id' => $owner->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $certificate = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema Sembelih Halal',
            'certificate_number' => 'BNSP-TEST-009',
            'issued_at' => '2026-10-10',
        ]);

        $pdf = $this->createRealPdfFile('cert-test.pdf');
        CertificateService::uploadPdf($certificate, $pdf);

        // 1. Pemilik sah berhasil mengunduh
        $this->actingAs($owner)
            ->get(route('participant.certificates.download', $certificate))
            ->assertOk();

        // 2. Peserta lain mendapat 403
        $this->actingAs($otherParticipant)
            ->get(route('participant.certificates.download', $certificate))
            ->assertForbidden();

        // 3. Tamu / belum login diarahkan ke login
        auth()->logout();
        $this->get(route('participant.certificates.download', $certificate))
            ->assertRedirect(route('login'));

        // 4. Editor konten diarahkan ke /admin atau 403 (EnsureParticipant middleware)
        $this->actingAs($editor)
            ->get(route('participant.certificates.download', $certificate))
            ->assertRedirect('/admin');

        // 5. Sertifikat yang dicabut ditolak pengunduhannya (403) bahkan oleh pemilik
        CertificateService::revoke($certificate, 'Pembatalan sementara');
        $this->actingAs($owner)
            ->get(route('participant.certificates.download', $certificate))
            ->assertForbidden();
    }

    public function test_public_verification_security_and_privacy(): void
    {
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => 'Ahmad Dahlan Brebes',
        ]);
        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);

        // Sertifikat Kompeten
        $cert = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal RPH',
            'certificate_number' => 'CERT-888-BNSP',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => '2026-01-10',
            'valid_until' => '2029-01-10',
            'public_code' => 'PUBCODE12345',
        ]);

        // Sertifikat Belum Kompeten
        $event2 = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $certFail = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $participant->id, 'event_id' => $event2->id, 'status' => EnrollmentStatus::Aktif])->id,
            'user_id' => $participant->id,
            'event_id' => $event2->id,
            'result' => Certificate::RESULT_BELUM_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal RPH',
            'public_code' => 'PUBFAIL99999',
        ]);

        // 1. Nomor salah dan nomor tidak ada menghasilkan respons dan pesan yang SAMA persis
        $expectedNotFound = 'Catatan sertifikat tidak ditemukan dalam basis data DPD JULEHA Kabupaten Brebes.';

        $resWrong = $this->get(route('certificate.verify', ['q' => 'NOMOR-SALAH-123']));
        $resWrong->assertOk()->assertSee($expectedNotFound);

        $resNonExistent = $this->get(route('certificate.verify', ['q' => '999/TIDAK/ADA']));
        $resNonExistent->assertOk()->assertSee($expectedNotFound);

        // 2. Kode public_code salah mengembalikan pesan tidak ditemukan yang sama
        $resCodeWrong = $this->get(route('certificate.verify.code', 'INVALIDCODE99'));
        $resCodeWrong->assertOk()->assertSee($expectedNotFound);

        // 3. Sertifikat belum_kompeten TIDAK PERNAH tampil di verifikasi publik
        $resFail = $this->get(route('certificate.verify.code', $certFail->public_code));
        $resFail->assertOk()->assertSee($expectedNotFound)->assertDontSee('Belum Kompeten');

        // 4. Verifikasi publik memuat meta robots noindex, nofollow
        $resValid = $this->get(route('certificate.verify.code', $cert->public_code));
        $resValid->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('Ahmad D. B.') // Masked
            ->assertSee('CERT-888-BNSP');
    }

    public function test_validation_rules_reject_invalid_inputs(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // 1. Kompeten tanpa nomor sertifikat ditolak
        $this->expectException(ValidationException::class);
        CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema Juleha',
            'certificate_number' => '', // Kosong
            'issued_at' => '2026-10-10',
        ]);
    }

    public function test_validation_rejects_duplicate_certificate_number(): void
    {
        $p1 = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $p2 = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);

        $e1 = Enrollment::create(['user_id' => $p1->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Aktif]);
        $e2 = Enrollment::create(['user_id' => $p2->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Aktif]);

        CertificateService::recordResult($e1, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema 1',
            'certificate_number' => 'DUPLICATE-NUMBER-001',
            'issued_at' => '2026-10-10',
        ]);

        // Peserta kedua dengan nomor yang sama ditolak
        $this->expectException(ValidationException::class);
        CertificateService::recordResult($e2, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema 2',
            'certificate_number' => 'DUPLICATE-NUMBER-001',
            'issued_at' => '2026-10-10',
        ]);
    }

    public function test_validation_rejects_fake_and_non_pdf_files(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $enrollment = Enrollment::create(['user_id' => $participant->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Aktif]);

        $certificate = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema 1',
            'certificate_number' => 'CERT-VALID-001',
            'issued_at' => '2026-10-10',
        ]);

        // File palsu (ekstensi pdf tapi bukan magic bytes %PDF-)
        $fakePdf = $this->createFakePdfFile('fake-cert.pdf');

        $this->expectException(ValidationException::class);
        CertificateService::uploadPdf($certificate, $fakePdf);
    }

    public function test_automated_expiry_reminders_and_queue(): void
    {
        $user180 = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'email' => 'user180@juleha.org']);
        $user90 = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'email' => 'user90@juleha.org']);
        $userExpired = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'email' => 'expired@juleha.org']);

        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);

        // Sertifikat 1: 180 hari lagi
        $cert180 = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $user180->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $user180->id,
            'event_id' => $event->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema 180',
            'certificate_number' => 'CERT-180',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYears(2)->subDays(185),
            'valid_until' => now()->addDays(180),
        ]);

        // Sertifikat 2: 90 hari lagi
        $cert90 = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $user90->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $user90->id,
            'event_id' => $event->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema 90',
            'certificate_number' => 'CERT-90',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYears(2)->subDays(275),
            'valid_until' => now()->addDays(90),
        ]);

        // Sertifikat 3: Telah kedaluwarsa
        $certExpired = Certificate::create([
            'enrollment_id' => Enrollment::create(['user_id' => $userExpired->id, 'event_id' => $event->id, 'status' => EnrollmentStatus::Selesai])->id,
            'user_id' => $userExpired->id,
            'event_id' => $event->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Skema Expired',
            'certificate_number' => 'CERT-EXP',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYears(3)->subDays(5),
            'valid_until' => now()->subDays(5),
        ]);

        // Jalankan command
        $this->artisan('certificates:send-expiry-reminders')->assertSuccessful();

        // Email dimasukkan ke antrean
        Mail::assertQueued(CertificateExpiryReminderMail::class, 3);

        // Tahap tersimpan di reminders_sent
        $this->assertContains('180', $cert180->fresh()->reminders_sent);
        $this->assertContains('90', $cert90->fresh()->reminders_sent);
        $this->assertContains('expired', $certExpired->fresh()->reminders_sent);

        // Jalankan lagi command: tidak boleh mengirim ulang untuk tahap yang sama!
        Mail::fake();
        $this->artisan('certificates:send-expiry-reminders')->assertSuccessful();
        Mail::assertNothingQueued();
    }

    public function test_user_anonymization_option_a_purges_pdf_and_retains_minimal_verification(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => 'Ahmad Muzani',
            'email' => 'muzani@juleha.org',
        ]);
        ParticipantProfile::create([
            'user_id' => $participant->id,
            'full_name_ktp' => 'Ahmad Muzani',
            'phone' => '082324449999',
            'nik' => '3329019999990001',
            'nik_hash' => hash_hmac('sha256', '3329019999990001', env('NIK_HASH_KEY', 'test_key')),
            'district' => 'Brebes',
            'village' => 'Brebes',
        ]);

        $event = $this->createEvent(['type' => EventType::SertifikasiKompetensi]);
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        $cert = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'ANON-CERT-007',
            'issued_at' => '2026-02-01',
        ]);

        $pdf = $this->createRealPdfFile('cert-muzani.pdf');
        CertificateService::uploadPdf($cert, $pdf);

        $storedPath = $cert->fresh()->file_path;
        Storage::disk('private')->assertExists($storedPath);

        // Simulasikan aksi anonimisasi akun peserta (Opsi A)
        $oldId = $participant->id;
        $participant->update([
            'name' => 'Pengguna Dihapus #'.$oldId,
            'email' => 'anonymized_'.$oldId.'_'.time().'@julehabrebes.org',
            'anonymized_at' => now(),
        ]);
        $participant->profile->update([
            'full_name_ktp' => null,
            'nik' => null,
            'phone' => null,
            'district' => null,
            'village' => null,
        ]);

        foreach ($participant->certificates as $c) {
            if ($c->file_path && Storage::disk('private')->exists($c->file_path)) {
                Storage::disk('private')->delete($c->file_path);
            }
            $c->update([
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_sha256' => null,
                'holder_data_erased_at' => now(),
            ]);
        }
        $participant->delete();

        // Verifikasi: Berkas fisik PDF telah dihapus dari private storage
        Storage::disk('private')->assertMissing($storedPath);
        $this->assertNull($cert->fresh()->file_path);

        // Verifikasi status model: Status masa berlaku tetap dihitung (Aktif) dan TIDAK dicabut
        $freshCert = $cert->fresh();
        $this->assertTrue($freshCert->isHolderDataErased());
        $this->assertFalse($freshCert->isRevoked());
        $this->assertEquals(Certificate::STATUS_AKTIF, $freshCert->computed_status);
        $this->assertEquals('Aktif', $freshCert->status_label);
        $this->assertEquals('Data pemegang telah dihapus', $freshCert->holder_data_status_label);

        // Verifikasi publik: Nomor sertifikat tetap ada, status masa berlaku 'Berlaku' tetap tampil, nama tampil "Data pemegang telah dihapus"
        $verifyRes = $this->get(route('certificate.verify.code', $cert->public_code));
        $verifyRes->assertOk()
            ->assertSee('ANON-CERT-007')
            ->assertSee('Juru Sembelih Halal')
            ->assertSee('Berlaku')
            ->assertSee('Data pemegang telah dihapus')
            ->assertDontSee('Pengguna D.')
            ->assertDontSee('Dicabut')
            ->assertDontSee('Ahmad Muzani')
            ->assertDontSee('3329019999990001');
    }

    public function test_force_delete_user_with_certificates_is_forbidden(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'FORCE-DEL-CHECK',
            'issued_at' => '2026-03-01',
        ]);

        // Policy harus menolak force delete untuk akun yang punya sertifikat
        $policy = new UserPolicy;
        $this->assertFalse($policy->forceDelete($superAdmin, $participant));

        // Eloquent deleting hook harus melempar LogicException jika dipaksa forceDelete
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Akun yang memiliki riwayat sertifikat tidak boleh dihapus permanen');

        $participant->forceDelete();
    }

    public function test_certificate_retention_command_prunes_only_when_setting_is_present(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        // Buat sertifikat yang sudah kedaluwarsa 4 tahun lalu (valid_until = 4 tahun lalu)
        $oldCert = Certificate::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'RETENTION-EXPIRED-001',
            'issued_at' => now()->subYears(7)->toDateString(),
            'valid_until' => now()->subYears(4)->toDateString(),
        ]);

        // A. Saat cert_record_retention_years kosong / belum ditetapkan: tidak boleh menghapus apapun
        Setting::set('cert_record_retention_years', '');
        $this->artisan('certificates:prune-expired-records')
            ->expectsOutputToContain('belum ditetapkan')
            ->assertSuccessful();

        $this->assertDatabaseHas('certificates', ['id' => $oldCert->id]);

        // B. Saat cert_record_retention_years diatur (misal 3 tahun):
        // Batas retensi: valid_until <= 3 tahun lalu. Sertifikat ini kedaluwarsa 4 tahun lalu, sehingga wajib dibersihkan!
        Setting::set('cert_record_retention_years', '3');
        $this->artisan('certificates:prune-expired-records')
            ->expectsOutputToContain('Berhasil membersihkan 1 catatan sertifikat')
            ->assertSuccessful();

        $this->assertDatabaseMissing('certificates', ['id' => $oldCert->id]);
    }

    public function test_revoked_certificate_verification_displays_revoked_without_name_or_internal_reason(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => 'Syaifuddin Zuhri',
        ]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        $cert = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'REVOKED-TEST-99',
            'issued_at' => '2026-01-01',
        ]);

        // Cabut sertifikat dengan alasan internal rahasia
        CertificateService::revoke($cert, 'Pelanggaran Disiplin Internal Organisasi Berat', $superAdmin->id);

        $response = $this->get(route('certificate.verify.code', $cert->public_code));
        $response->assertOk()
            ->assertSee('Dicabut')
            ->assertSee('Sertifikat dengan nomor ini telah dicabut')
            ->assertDontSee('Syaifuddin Zuhri')
            ->assertDontSee('Syaifuddin Z.')
            ->assertDontSee('Pelanggaran Disiplin Internal Organisasi Berat'); // Alasan internal tidak boleh bocor
    }

    public function test_search_by_number_does_not_display_holder_name(): void
    {
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => 'Bambang Trihatmodjo',
        ]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        $cert = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'SEARCH-NO-NAME-123',
            'issued_at' => '2026-01-10',
        ]);

        // Cari via nomor sertifikat (/verifikasi?q=...)
        $response = $this->get(route('certificate.verify', ['q' => 'SEARCH-NO-NAME-123']));
        $response->assertOk()
            ->assertSee('SEARCH-NO-NAME-123')
            ->assertSee('Juru Sembelih Halal')
            ->assertSee('Status Masa Berlaku: Aktif')
            ->assertDontSee('Bambang Trihatmodjo')
            ->assertDontSee('Bambang T.'); // Nama pemegang TIDAK boleh tampil pada pencarian nomor
    }

    public function test_verification_not_found_and_belum_kompeten_return_neutral_response(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        // Catat hasil belum kompeten
        CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_BELUM_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'BELUM-KOMPETEN-001',
        ]);

        // A. Pencarian nomor yang tidak ada: respon netral
        $responseNotFound = $this->get(route('certificate.verify', ['q' => 'NOMOR-TIDAK-ADA-999']));
        $responseNotFound->assertOk()
            ->assertSee('Catatan sertifikat tidak ditemukan dalam basis data DPD JULEHA Kabupaten Brebes.');

        // B. Pencarian nomor belum kompeten: tetap respon netral seragam (tidak pernah tampil)
        $responseBelum = $this->get(route('certificate.verify', ['q' => 'BELUM-KOMPETEN-001']));
        $responseBelum->assertOk()
            ->assertSee('Catatan sertifikat tidak ditemukan dalam basis data DPD JULEHA Kabupaten Brebes.');

        // C. Tautan public code acak yang tidak ada: respon netral seragam
        $responseInvalidCode = $this->get(route('certificate.verify.code', 'KODEACAK9999'));
        $responseInvalidCode->assertOk()
            ->assertSee('Catatan sertifikat tidak ditemukan dalam basis data DPD JULEHA Kabupaten Brebes.');
    }

    public function test_download_response_has_required_security_headers(): void
    {
        $participant = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => 'Mochamad Ridwan',
        ]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        $cert = CertificateService::recordResult($enrollment, [
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'HEADER-TEST-001',
            'issued_at' => '2026-03-01',
        ]);

        $pdf = $this->createRealPdfFile('cert-ridwan.pdf');
        CertificateService::uploadPdf($cert, $pdf);

        $response = $this->actingAs($participant)->get(route('participant.certificates.download', $cert));
        $response->assertOk();

        // Verifikasi seluruh header keamanan unduhan yang diwajibkan
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_status_berlaku_remains_active_when_holder_data_is_erased(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $event = $this->createEvent();
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        // 1. Sertifikat masih berlaku (valid_until di masa depan) dengan data pemegang dihapus
        $certActiveErased = Certificate::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $participant->id,
            'event_id' => $event->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'PDP-ACTIVE-001',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subMonths(6),
            'valid_until' => now()->addYears(2),
            'holder_data_erased_at' => now(),
        ]);

        $this->assertEquals(Certificate::STATUS_AKTIF, $certActiveErased->computed_status);
        $this->assertEquals('Aktif', $certActiveErased->status_label);
        $this->assertTrue($certActiveErased->isHolderDataErased());

        $resActive = $this->get(route('certificate.verify.code', $certActiveErased->public_code));
        $resActive->assertOk()
            ->assertSee('Status Masa Berlaku: Aktif')
            ->assertSee('Data Pemegang Telah Dihapus')
            ->assertSee('Data pemegang telah dihapus');

        // 2. Sertifikat sudah kedaluwarsa dengan data pemegang dihapus
        $event2 = $this->createEvent();
        $enrollment2 = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event2->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        $certExpiredErased = Certificate::create([
            'enrollment_id' => $enrollment2->id,
            'user_id' => $participant->id,
            'event_id' => $event2->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'PDP-EXPIRED-001',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYears(4),
            'valid_until' => now()->subMonth(),
            'holder_data_erased_at' => now(),
        ]);

        $this->assertEquals(Certificate::STATUS_KEDALUWARSA, $certExpiredErased->computed_status);
        $this->assertEquals('Kedaluwarsa', $certExpiredErased->status_label);
        $this->assertTrue($certExpiredErased->isHolderDataErased());

        $resExpired = $this->get(route('certificate.verify.code', $certExpiredErased->public_code));
        $resExpired->assertOk()
            ->assertSee('Status Masa Berlaku: Kedaluwarsa')
            ->assertSee('Data Pemegang Telah Dihapus')
            ->assertSee('Data pemegang telah dihapus');

        // 3. Sertifikat dicabut dengan data pemegang dihapus
        $event3 = $this->createEvent();
        $enrollment3 = Enrollment::create([
            'user_id' => $participant->id,
            'event_id' => $event3->id,
            'status' => EnrollmentStatus::Selesai,
        ]);

        $certRevokedErased = Certificate::create([
            'enrollment_id' => $enrollment3->id,
            'user_id' => $participant->id,
            'event_id' => $event3->id,
            'result' => Certificate::RESULT_KOMPETEN,
            'scheme_name' => 'Juru Sembelih Halal',
            'certificate_number' => 'PDP-REVOKED-001',
            'issuer_name' => 'LSP Pertanian RI',
            'issued_at' => now()->subYear(),
            'valid_until' => now()->addYears(2),
            'revoked_at' => now(),
            'holder_data_erased_at' => now(),
        ]);

        $this->assertEquals(Certificate::STATUS_DICABUT, $certRevokedErased->computed_status);
        $this->assertEquals('Dicabut', $certRevokedErased->status_label);
        $this->assertTrue($certRevokedErased->isHolderDataErased());

        $resRevoked = $this->get(route('certificate.verify.code', $certRevokedErased->public_code));
        $resRevoked->assertOk()
            ->assertSee('Status Masa Berlaku: Dicabut')
            ->assertSee('Data Pemegang Telah Dihapus');
    }
}
