<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\ParticipantProfile;
use App\Models\User;
use App\Support\CsvSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvFormulaInjectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_sanitizer_neutralizes_all_dangerous_prefixes(): void
    {
        // 1. Karakter berisiko: =, +, -, @, tab, carriage return
        $this->assertEquals("'=SUM(1+1)", CsvSanitizer::sanitize('=SUM(1+1)'));
        $this->assertEquals("'+123456", CsvSanitizer::sanitize('+123456'));
        $this->assertEquals("'-100", CsvSanitizer::sanitize('-100'));
        $this->assertEquals("'@cmd", CsvSanitizer::sanitize('@cmd'));
        $this->assertEquals("'\talert", CsvSanitizer::sanitize("\talert"));
        $this->assertEquals("'\ralert", CsvSanitizer::sanitize("\ralert"));

        // 2. Karakter aman tidak diubah
        $this->assertEquals('Ahmad Muzani', CsvSanitizer::sanitize('Ahmad Muzani'));
        $this->assertEquals('Juru Sembelih Halal', CsvSanitizer::sanitize('Juru Sembelih Halal'));
        $this->assertEquals('08123456789', CsvSanitizer::sanitize('08123456789'));
        $this->assertEquals('', CsvSanitizer::sanitize(''));

        // 3. Tipe data non-string tidak diubah
        $this->assertEquals(123, CsvSanitizer::sanitize(123));
        $this->assertEquals(45.67, CsvSanitizer::sanitize(45.67));
        $this->assertNull(CsvSanitizer::sanitize(null));
        $this->assertTrue(CsvSanitizer::sanitize(true));

        // 4. Baris CSV di-sanitize secara menyeluruh
        $rawRow = [
            1,
            '=cmd|/C calc!A0',
            '+62812345678',
            '-minus',
            '@mention',
            'Ahmad',
        ];

        $sanitized = CsvSanitizer::sanitizeRow($rawRow);

        $this->assertEquals([
            1,
            "'=cmd|/C calc!A0",
            "'+62812345678",
            "'-minus",
            "'@mention",
            'Ahmad',
        ], $sanitized);
    }

    public function test_lsp_export_neutralizes_formula_injection_and_records_audit(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $maliciousUser = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => '=2+5*cmd',
            'email' => '@evil@domain.com',
        ]);

        ParticipantProfile::create([
            'user_id' => $maliciousUser->id,
            'full_name_ktp' => '=Ahmad Exploit',
            'phone' => '08999999999',
            'nik' => '3329019999990001',
            'nik_hash' => hash_hmac('sha256', '3329019999990001', env('NIK_HASH_KEY', 'test_key')),
            'district' => '+Wanasari',
            'village' => "\tPesantunan",
            'consent' => true,
            'consent_version' => ParticipantProfile::CURRENT_CONSENT_VERSION,
        ]);

        $event = Event::create([
            'title' => 'Sertifikasi BNSP formula injection test',
            'slug' => 'sertifikasi-bnsp-inj-test',
            'type' => EventType::SertifikasiKompetensi,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(3),
            'registration_deadline' => now()->addDay(),
            'location' => 'RPH Brebes',
            'quota' => 20,
            'fee' => 0,
            'status' => 'open',
        ]);

        Enrollment::create([
            'user_id' => $maliciousUser->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Simulasikan unduhan ekspor LSP
        $enrollments = $event->enrollments()
            ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
            ->with(['user.profile'])
            ->get();

        $stream = fopen('php://memory', 'r+');
        foreach ($enrollments as $enrollment) {
            $profile = $enrollment->user?->profile;
            $row = CsvSanitizer::sanitizeRow([
                1,
                $profile?->full_name_ktp ?? $enrollment->user?->name ?? '-',
                $profile?->nik ?? '-',
                $profile?->phone ?? '-',
                $enrollment->user?->email ?? '-',
                $profile?->district ?? '-',
                $profile?->village ?? '-',
            ]);
            fputcsv($stream, $row);
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        // Verifikasi sel teks dengan prefix bahaya dinetralkan dengan petik tunggal
        $this->assertStringContainsString("'=Ahmad Exploit", $csvContent);
        $this->assertStringContainsString("'+Wanasari", $csvContent);
        $this->assertStringContainsString("'@evil@domain.com", $csvContent);
        $this->assertStringContainsString("'\tPesantunan", $csvContent);
    }

    public function test_attendance_rekap_export_neutralizes_dangerous_formulas_and_excludes_nik(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN]);

        $event = Event::create([
            'title' => 'Bimtek Juru Sembelih Halal Formula Injection Test',
            'slug' => 'bimtek-juleha-inj-test',
            'type' => EventType::Bimtek,
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(2),
            'registration_deadline' => now()->addHours(12),
            'location' => 'Islamic Center Brebes',
            'quota' => 30,
            'fee' => 0,
            'status' => 'open',
        ]);

        EventSession::create([
            'event_id' => $event->id,
            'title' => 'Materi Syariat Penyembelihan',
            'starts_at' => now()->addDays(1)->setHour(8),
            'ends_at' => now()->addDays(1)->setHour(12),
            'attendance_open' => true,
        ]);

        $user1 = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => '=HYPERLINK("http://evil.com","Click")',
        ]);
        ParticipantProfile::create([
            'user_id' => $user1->id,
            'full_name_ktp' => '=HYPERLINK("http://evil.com","Click")',
            'phone' => '081234567890',
            'nik' => '3329011111110001',
            'nik_hash' => hash_hmac('sha256', '3329011111110001', env('NIK_HASH_KEY', 'test_key')),
            'district' => 'Brebes',
            'village' => 'Brebes',
            'consent' => true,
            'consent_version' => ParticipantProfile::CURRENT_CONSENT_VERSION,
        ]);
        Enrollment::create([
            'user_id' => $user1->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'name' => '@SUM(1,2)',
        ]);
        ParticipantProfile::create([
            'user_id' => $user2->id,
            'full_name_ktp' => '@SUM(1,2)',
            'phone' => '081234567891',
            'nik' => '3329012222220002',
            'nik_hash' => hash_hmac('sha256', '3329012222220002', env('NIK_HASH_KEY', 'test_key')),
            'district' => 'Jatibarang',
            'village' => 'Jatibarang Kidul',
            'consent' => true,
            'consent_version' => ParticipantProfile::CURRENT_CONSENT_VERSION,
        ]);
        Enrollment::create([
            'user_id' => $user2->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.export', $event));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csvOutput = $response->streamedContent();

        // Formula injection dinetralkan dengan petik tunggal di awal
        $this->assertStringContainsString('\'=HYPERLINK', $csvOutput);
        $this->assertStringContainsString('evil.com', $csvOutput);
        $this->assertStringContainsString('\'@SUM(1,2)', $csvOutput);

        // Ekspor presensi TIDAK boleh memuat NIK baik di header maupun baris data
        $this->assertStringNotContainsString(',NIK,', $csvOutput);
        $this->assertStringNotContainsString('3329011111110001', $csvOutput);
        $this->assertStringNotContainsString('3329012222220002', $csvOutput);
    }
}
