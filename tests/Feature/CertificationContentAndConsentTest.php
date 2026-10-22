<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\Faq;
use App\Models\ParticipantProfile;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificationContentAndConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * 1. Halaman program membaca nilai dari pengaturan situs (ubah pengaturan, tampilan ikut berubah).
     */
    public function test_program_page_reads_and_reflects_certification_settings(): void
    {
        Setting::set('cert_issuer_name', 'LSP Pertanian RI');
        Setting::set('cert_validity_years', '3');
        Setting::set('cert_form_note', 'digital');
        Setting::set('cert_issuer_license', '');
        Setting::clearCache();

        $response = $this->get(route('program.show', 'sertifikasi-kompetensi-bnsp'));
        $response->assertOk();
        $response->assertSee('LSP Pertanian RI');
        $response->assertSee('3 tahun');
        $response->assertSee('secara digital');
        $response->assertDontSee('Lisensi:');

        // Ubah pengaturan dan pastikan tampilan program ikut berubah secara dinamis
        Setting::set('cert_issuer_name', 'LSP Peternakan Unggul');
        Setting::set('cert_validity_years', '5');
        Setting::set('cert_form_note', 'elektronik');
        Setting::set('cert_issuer_license', 'BNSP-LSP-888-ID');
        Setting::clearCache();

        $response2 = $this->get(route('program.show', 'sertifikasi-kompetensi-bnsp'));
        $response2->assertOk();
        $response2->assertSee('LSP Peternakan Unggul');
        $response2->assertSee('5 tahun');
        $response2->assertSee('secara elektronik');
        $response2->assertSee('Lisensi: BNSP-LSP-888-ID');
    }

    /**
     * 2. Syarat peserta dan biaya menampilkan "Akan diumumkan oleh DPD" dengan tombol WhatsApp panitia.
     */
    public function test_program_page_displays_syarat_dan_biaya_akan_diumumkan(): void
    {
        $response = $this->get(route('program.show', 'sertifikasi-kompetensi-bnsp'));
        $response->assertOk();

        // Syarat Peserta & Biaya dengan status Akan diumumkan oleh DPD
        $response->assertSee('Syarat Peserta');
        $response->assertSee('Biaya');
        $response->assertSee('Akan diumumkan oleh DPD');

        // Tombol WhatsApp
        $response->assertSee('Tanya panitia via WhatsApp');

        // Penjelasan peran fasilitasi DPD vs penilaian LSP
        $response->assertSee('DPD JULEHA Kabupaten Brebes memfasilitasi pelaksanaan kegiatan, sedangkan penilaian kompetensi dan penerbitan sertifikat dilakukan oleh lembaga sertifikasi');
    }

    /**
     * 3. FAQ tentang penerbit sertifikat dan masa berlaku membaca nilai pengaturan secara dinamis.
     */
    public function test_faq_resolves_settings_dynamically(): void
    {
        Setting::set('cert_issuer_name', 'LSP Pertanian RI');
        Setting::set('cert_validity_years', '3');
        Setting::set('cert_form_note', 'digital');
        Setting::clearCache();

        $faqIssuer = Faq::where('question', 'Siapa yang menerbitkan sertifikat?')->first();
        $this->assertNotNull($faqIssuer);
        $this->assertStringContainsString('LSP Pertanian RI', $faqIssuer->answer);

        $faqValidity = Faq::where('question', 'Berapa lama sertifikat berlaku dan apa bentuknya?')->first();
        $this->assertNotNull($faqValidity);
        $this->assertStringContainsString('3 tahun', $faqValidity->answer);
        $this->assertStringContainsString('digital', $faqValidity->answer);

        $response = $this->get(route('faq'));
        $response->assertOk();
        $response->assertSee('LSP Pertanian RI');
        $response->assertSee('3 tahun');
    }

    /**
     * 4. Peserta yang belum menyetujui versi terbaru TIDAK bisa mendaftar kegiatan sertifikasi_kompetensi,
     * tetapi BISA mendaftar kegiatan bimtek tanpa hambatan.
     */
    public function test_participant_without_latest_consent_cannot_enroll_sertifikasi_but_can_enroll_bimtek(): void
    {
        $user = User::factory()->create([
            'role' => 'peserta',
        ]);

        // Profil lengkap NIK tetapi belum memiliki consent_version terbaru (peserta lama)
        ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '6281234567890',
            'full_name_ktp' => 'Ahmad Fauzi',
            'nik' => '3329010101900001',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_version' => null, // Versi lama / belum setuju versi terbaru
            'consent_at' => now()->subMonths(3),
        ]);

        $sertifikasiProgram = Program::where('slug', 'sertifikasi-kompetensi-bnsp')->first();
        $bimtekProgram = Program::where('slug', 'bimtek-pelatihan')->first();

        $sertifikasiEvent = Event::create([
            'title' => 'Uji Sertifikasi Kompetensi Juru Sembelih Halal',
            'slug' => 'uji-sertifikasi-bnsp-2026',
            'type' => EventType::SertifikasiKompetensi,
            'program_id' => $sertifikasiProgram?->id,
            'status' => 'open',
            'quota' => 20,
            'starts_at' => now()->addDays(15),
            'location' => 'RPH Brebes',
        ]);

        $bimtekEvent = Event::create([
            'title' => 'Bimtek Juleha Angkatan Terbuka',
            'slug' => 'bimtek-terbuka-2026',
            'type' => EventType::Bimtek,
            'program_id' => $bimtekProgram?->id,
            'status' => 'open',
            'quota' => 30,
            'starts_at' => now()->addDays(20),
            'location' => 'Aula Islamic Centre',
        ]);

        // A. Pendaftaran sertifikasi kompetensi dicegat & diarahkan untuk perbarui persetujuan
        $responseCreateSertifikasi = $this->actingAs($user)->get(route('enrollment.create', $sertifikasiEvent->slug));
        $responseCreateSertifikasi->assertRedirect(route('participant.profile', ['step' => 2]));

        $responseStoreSertifikasi = $this->actingAs($user)->post(route('enrollment.store', $sertifikasiEvent->slug));
        $responseStoreSertifikasi->assertSessionHas('error');
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'event_id' => $sertifikasiEvent->id,
        ]);

        // B. Pendaftaran bimtek TIDAK terganggu dan tetap berhasil
        $responseCreateBimtek = $this->actingAs($user)->get(route('enrollment.create', $bimtekEvent->slug));
        $responseCreateBimtek->assertOk();

        $responseStoreBimtek = $this->actingAs($user)->post(route('enrollment.store', $bimtekEvent->slug));
        $responseStoreBimtek->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'event_id' => $bimtekEvent->id,
            'status' => EnrollmentStatus::Menunggu->value,
        ]);
    }

    /**
     * 5. Peserta yang telah menyetujui versi terbaru dapat mendaftar kegiatan sertifikasi_kompetensi.
     */
    public function test_participant_with_latest_consent_can_enroll_in_sertifikasi_kompetensi(): void
    {
        $user = User::factory()->create([
            'role' => 'peserta',
        ]);

        ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '6281234567899',
            'full_name_ktp' => 'Bambang Supriyadi',
            'nik' => '3329020202880002',
            'district' => 'Bumiayu',
            'village' => 'Dukuhturi',
            'status' => 'juleha_aktif',
            'consent' => true,
            'consent_version' => ParticipantProfile::CURRENT_CONSENT_VERSION,
            'consent_at' => now(),
        ]);

        $sertifikasiProgram = Program::where('slug', 'sertifikasi-kompetensi-bnsp')->first();

        $sertifikasiEvent = Event::create([
            'title' => 'Uji Sertifikasi Kompetensi BNSP Angkatan Resmi',
            'slug' => 'uji-sertifikasi-bnsp-resmi',
            'type' => EventType::SertifikasiKompetensi,
            'program_id' => $sertifikasiProgram?->id,
            'status' => 'open',
            'quota' => 25,
            'starts_at' => now()->addDays(12),
            'location' => 'RPH Brebes',
        ]);

        $response = $this->actingAs($user)->get(route('enrollment.create', $sertifikasiEvent->slug));
        $response->assertOk();

        $responseStore = $this->actingAs($user)->post(route('enrollment.store', $sertifikasiEvent->slug));
        $responseStore->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'event_id' => $sertifikasiEvent->id,
            'status' => EnrollmentStatus::Menunggu->value,
        ]);
    }

    /**
     * 6. Dashboard menampilkan banner tipis "Perbarui persetujuan data" saat versi belum sesuai.
     */
    public function test_dashboard_displays_thin_banner_when_consent_needs_update(): void
    {
        $user = User::factory()->create([
            'role' => 'peserta',
        ]);

        $profile = ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '6281234567891',
            'full_name_ktp' => 'Imam Syafi\'i',
            'nik' => '3329030303850003',
            'district' => 'Songgom',
            'village' => 'Songgom Lor',
            'status' => 'pengurus_masjid',
            'consent' => true,
            'consent_version' => 'v1_lama',
            'consent_at' => now()->subMonths(4),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Perbarui persetujuan data');

        // Peserta menyetujui versi terbaru lewat simpan profil langkah 2
        $responseUpdate = $this->actingAs($user)->post(route('participant.profile.update'), [
            'step' => 2,
            'full_name_ktp' => 'Imam Syafi\'i',
            'consent' => 1,
        ]);
        $responseUpdate->assertRedirect(route('dashboard'));

        $profile->refresh();
        $this->assertTrue($profile->hasCurrentConsent());
        $this->assertEquals(ParticipantProfile::CURRENT_CONSENT_VERSION, $profile->consent_version);

        // Banner tidak lagi muncul setelah diperbarui
        $responseAfter = $this->actingAs($user->fresh())->get(route('dashboard'));
        $responseAfter->assertOk();
        $responseAfter->assertDontSee('Perbarui persetujuan data');
    }

    /**
     * 7. Halaman Kebijakan Privasi menjelaskan penerusan data ke LSP dan hak peserta dalam bahasa awam.
     */
    public function test_privacy_policy_page_displays_plain_language_consent_and_rights(): void
    {
        $response = $this->get(route('kebijakan-privasi'));
        $response->assertOk();

        $response->assertSee(setting('cert_issuer_name', 'LSP Pertanian RI'));
        $response->assertSee('Hak Akses');
        $response->assertSee('Hak Koreksi');
        $response->assertSee('Hak Penghapusan');
        $response->assertSee('verifikasi keabsahan juru sembelih di wilayah Kabupaten Brebes');
    }
}
