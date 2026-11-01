<?php

namespace Tests\Feature;

use App\Models\BoardMember;
use App\Models\ParticipantProfile;
use App\Models\User;
use App\Services\BoardMemberPhotoService;
use Database\Seeders\BoardMemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BoardMemberPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_drive_file_id_extracted_from_various_url_formats(): void
    {
        $service = app(BoardMemberPhotoService::class);

        $id1 = $service->extractGoogleDriveFileId('https://drive.google.com/file/d/1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456/view?usp=sharing');
        $this->assertEquals('1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456', $id1);

        $id2 = $service->extractGoogleDriveFileId('https://drive.google.com/open?id=1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456');
        $this->assertEquals('1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456', $id2);

        $id3 = $service->extractGoogleDriveFileId('https://lh3.googleusercontent.com/d/1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456');
        $this->assertEquals('1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456', $id3);

        $id4 = $service->extractGoogleDriveFileId('1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456');
        $this->assertEquals('1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456', $id4);
    }

    public function test_normalize_member_name_removes_titles_numbers_and_punctuation(): void
    {
        $normalized1 = BoardMemberPhotoService::normalizeMemberName('H. Agus Riyanto, S.Pd.');
        $this->assertEquals('agus riyanto', $normalized1);

        $normalized2 = BoardMemberPhotoService::normalizeMemberName('01_Chasan_Mudhofar.jpg');
        $this->assertEquals('chasan mudhofar', $normalized2);

        $normalized3 = BoardMemberPhotoService::normalizeMemberName('K.H. Subhan Makmun (Pelindung)');
        $this->assertStringContainsString('subhan makmun', $normalized3);
    }

    public function test_board_member_photo_attributes_and_accessors(): void
    {
        $member = BoardMember::create([
            'kind' => BoardMember::KIND_PERSON,
            'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
            'name' => 'Fulan Testing',
            'position' => 'Wakil Ketua',
            'photo' => 'board-members/main/test-photo.webp',
            'photo_version' => 12345,
            'photo_consent_at' => now()->toDateString(),
            'photo_consent_note' => 'Izin lisan via WA',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertTrue($member->hasPhoto());
        $this->assertStringContainsString('storage/board-members/main/test-photo.webp?v=12345', $member->photo_url);
        $this->assertStringContainsString('storage/board-members/thumb/test-photo.webp?v=12345', $member->thumb_url);
        $this->assertStringContainsString('400w', $member->photo_srcset);
        $this->assertStringContainsString('800w', $member->photo_srcset);
    }

    public function test_photo_deletion_cleans_up_public_and_private_storage(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        $mainPath = 'board-members/main/dummy-sample.webp';
        $thumbPath = 'board-members/thumb/dummy-sample.webp';
        $originalPath = 'board-members-original/dummy-sample.webp';

        Storage::disk('public')->put($mainPath, 'dummy-main-content');
        Storage::disk('public')->put($thumbPath, 'dummy-thumb-content');
        Storage::disk('private')->put($originalPath, 'dummy-orig-content');

        $service = app(BoardMemberPhotoService::class);
        $service->deletePhotoFiles($mainPath);

        Storage::disk('public')->assertMissing($mainPath);
        Storage::disk('public')->assertMissing($thumbPath);
        Storage::disk('private')->assertMissing($originalPath);
    }

    public function test_participant_dashboard_does_not_show_false_missing_district_when_district_is_filled(): void
    {
        $user = User::factory()->create([
            'role' => 'participant',
        ]);

        ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '085712269079',
            'district' => 'Kersana',
            'village' => 'Jatibarang Lor',
            'institution' => 'RPH Jatibarang',
            'status' => 'Calon Juru Sembelih Halal (Pemula/Warga)',
            'full_name_ktp' => 'Budi Santoso',
            'nik' => '3329123456780001',
            'consent' => true,
            'consent_version' => ParticipantProfile::CURRENT_CONSENT_VERSION,
            'consent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('participant.dashboard'));
        $response->assertStatus(200);

        // Pastikan TIDAK ADA teks peringatan "Kecamatan" belum lengkap
        $response->assertDontSee('Data Anda <strong>tinggal 1 lagi</strong>: Kecamatan', false);
        $response->assertDontSee('Kecamatan / Domisili', false);
    }

    public function test_pengurus_page_renders_4_5_photo_aspect_and_proper_alt_attributes(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $ketua = BoardMember::where('name', 'Chasan Mudhofar')->first();
        $this->assertNotNull($ketua);

        $ketua->update([
            'photo' => 'board-members/main/chasan-sample.webp',
            'photo_version' => 9999,
            'photo_consent_at' => now()->toDateString(),
        ]);

        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);

        // Pastikan foto terpasang dengan atribut alt yang informatif dan aspect 4:5
        $response->assertSee('Chasan Mudhofar, Ketua DPD JULEHA Kabupaten Brebes');
        $response->assertSee('board-members/main/chasan-sample.webp?v=9999');
        $response->assertSee('aspect-[4/5]', false);
    }
}

