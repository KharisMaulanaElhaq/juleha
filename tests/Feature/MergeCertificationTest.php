<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Material;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * 1. Enum EventType hanya memiliki 3 nilai dan label yang tepat.
     */
    public function test_event_type_enum_has_exactly_three_values_and_proper_labels(): void
    {
        $cases = EventType::cases();
        $this->assertCount(3, $cases);

        $values = array_map(fn (EventType $type) => $type->value, $cases);
        $this->assertEquals(['bimtek', 'sertifikasi_kompetensi', 'tot'], $values);

        $this->assertEquals('Bimtek', EventType::Bimtek->label());
        $this->assertEquals('Sertifikasi Kompetensi BNSP', EventType::SertifikasiKompetensi->label());
        $this->assertEquals('Training of Trainer (TOT)', EventType::Tot->label());
    }

    /**
     * 2. Migrasi mempertahankan enrollments, presensi, materi, dan tidak meninggalkan event yatim.
     */
    public function test_migration_retains_enrollments_attendances_and_materials_without_orphans(): void
    {
        // Setup program lama tiruan jika diperlukan
        $survivingProgram = Program::where('slug', 'sertifikasi-kompetensi-bnsp')->first();
        $this->assertNotNull($survivingProgram);

        // Buat event bertipe sertifikasi_kompetensi
        $event = Event::create([
            'title' => 'Uji Sertifikasi Kompetensi BNSP Angkatan Khusus',
            'slug' => 'uji-sertifikasi-kompetensi-bnsp-khusus',
            'type' => EventType::SertifikasiKompetensi,
            'program_id' => $survivingProgram->id,
            'status' => 'open',
            'quota' => 20,
            'starts_at' => now()->addDays(10),
            'location' => 'Brebes',
        ]);

        $user = User::factory()->create([
            'role' => 'peserta',
        ]);

        // Enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Sesi & Presensi
        $session = EventSession::create([
            'event_id' => $event->id,
            'title' => 'Sesi Uji Teori & Wawancara Asesor',
            'starts_at' => now()->addDays(10)->setTime(9, 0),
            'ends_at' => now()->addDays(10)->setTime(12, 0),
            'attendance_open' => true,
        ]);

        $attendance = Attendance::create([
            'event_session_id' => $session->id,
            'user_id' => $user->id,
            'status' => AttendanceStatus::Hadir,
            'attended_at' => now(),
        ]);

        // Materi
        $material = Material::create([
            'event_id' => $event->id,
            'title' => 'Skema SKKNI Penyembelihan Halal',
            'kind' => 'file',
            'path_or_url' => 'materials/skkni-test.pdf',
            'file_path' => 'materials/skkni-test.pdf',
            'size' => 1024,
            'is_published' => true,
        ]);

        // Verifikasi semua relasi utuh
        $this->assertEquals($survivingProgram->id, $event->fresh()->program_id);
        $this->assertEquals($event->id, $enrollment->fresh()->event_id);
        $this->assertEquals($session->id, $attendance->fresh()->event_session_id);
        $this->assertEquals($event->id, $material->fresh()->event_id);

        // Pastikan tidak ada event yang program_id-nya tidak ada di tabel programs
        $orphanedCount = Event::whereNotNull('program_id')
            ->whereNotIn('program_id', Program::pluck('id'))
            ->count();
        $this->assertEquals(0, $orphanedCount);
    }

    /**
     * 3. Halaman /program menampilkan 7 program dalam dua kategori.
     */
    public function test_public_program_page_displays_seven_programs(): void
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

        // Pastikan tidak menampilkan judul yang dipisah lagi
        $response->assertDontSeeText('Sertifikasi BNSP & Kemitraan'); // judul lama program
        $this->assertCount(7, Program::where('is_active', true)->get());
    }

    /**
     * 4. Slug lama sertifikasi diarahkan 301 ke slug baru.
     */
    public function test_legacy_slugs_301_redirect(): void
    {
        $this->get('/program/sertifikasi-bnsp')
            ->assertStatus(301)
            ->assertRedirect('/program/sertifikasi-kompetensi-bnsp');

        $this->get('/program/sertifikasi-kompetensi')
            ->assertStatus(301)
            ->assertRedirect('/program/sertifikasi-kompetensi-bnsp');
    }

    /**
     * 5. Program Sertifikasi Kompetensi BNSP memiliki deskripsi netral, partner_name/lsp_name kosong, dan tombol WhatsApp.
     */
    public function test_unified_program_has_neutral_description_empty_lsp_and_whatsapp_cta(): void
    {
        $program = Program::where('slug', 'sertifikasi-kompetensi-bnsp')->first();
        $this->assertNotNull($program);

        // Kolom partner_name (lsp_name) tetap kosong/null
        $this->assertNull($program->lsp_name);

        // Deskripsi terkonfirmasi
        $this->assertStringContainsString(
            'Uji kompetensi juru sembelih halal yang difasilitasi DPD JULEHA Kabupaten Brebes',
            $program->summary
        );

        // CTA WhatsApp
        $this->assertEquals('whatsapp', $program->cta_type);
        $this->assertEquals('Tanya via WhatsApp', $program->getResolvedCtaText());

        // Halaman detail publik memuat deskripsi dan tombol WhatsApp
        $detailResponse = $this->get('/program/sertifikasi-kompetensi-bnsp');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSeeText('Sertifikasi Kompetensi BNSP');
        $detailResponse->assertSeeText('Uji kompetensi juru sembelih halal yang difasilitasi DPD JULEHA Kabupaten Brebes');
        $detailResponse->assertSee('wa.me/');
    }
}
