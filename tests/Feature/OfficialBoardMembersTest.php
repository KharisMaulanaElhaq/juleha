<?php

namespace Tests\Feature;

use App\Models\BoardMember;
use App\Models\Setting;
use App\Models\User;
use App\Policies\BoardMemberPolicy;
use Database\Seeders\BoardMemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialBoardMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_produces_exact_31_entries_with_correct_groups(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $this->assertEquals(31, BoardMember::count());
        $this->assertEquals(3, BoardMember::where('group', BoardMember::GROUP_PELINDUNG_PEMBINA)->count());
        $this->assertEquals(4, BoardMember::where('group', BoardMember::GROUP_PIMPINAN_HARIAN)->count());
        $this->assertEquals(24, BoardMember::where('group', BoardMember::GROUP_BIDANG)->count());

        // Pastikan kind lembaga dan perorangan benar
        $this->assertEquals(3, BoardMember::where('kind', BoardMember::KIND_INSTITUTION)->count());
        $this->assertEquals(28, BoardMember::where('kind', BoardMember::KIND_PERSON)->count());
    }

    public function test_two_agus_riyanto_are_stored_separately_with_internal_notes(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $agusRecords = BoardMember::where('name', 'like', 'Agus Riyanto%')->get();
        $this->assertCount(2, $agusRecords);

        // 1. Agus Riyanto, S.Pd. sebagai Bendahara II
        $bendahara = BoardMember::where('name', 'Agus Riyanto, S.Pd.')->first();
        $this->assertNotNull($bendahara);
        $this->assertEquals(BoardMember::GROUP_PIMPINAN_HARIAN, $bendahara->group);
        $this->assertEquals('Bendahara II', $bendahara->position);
        $this->assertEquals('Perlu dikonfirmasi: orang yang sama?', $bendahara->internal_note);

        // 2. Agus Riyanto di Bidang Usaha
        $bidangUsaha = BoardMember::where('name', 'Agus Riyanto')->first();
        $this->assertNotNull($bidangUsaha);
        $this->assertEquals(BoardMember::GROUP_BIDANG, $bidangUsaha->group);
        $this->assertEquals('Bidang Usaha', $bidangUsaha->division);
        $this->assertNull($bidangUsaha->position);
        $this->assertEquals('Perlu dikonfirmasi: orang yang sama?', $bidangUsaha->internal_note);
    }

    public function test_seeder_is_idempotent_and_safe_to_rerun(): void
    {
        $this->seed(BoardMemberSeeder::class);
        $this->assertEquals(31, BoardMember::count());

        // Jalankan seeder kedua kali
        $this->seed(BoardMemberSeeder::class);
        $this->assertEquals(31, BoardMember::count());
    }

    public function test_pengurus_page_displays_all_three_sections_and_official_data(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);

        // Header & Periode
        $response->assertSee('Susunan Pengurus');
        $response->assertSee('Masa bakti 2024-2029');

        // Bagian 1: Pelindung & Pembina (Lembaga)
        $response->assertSee('Pelindung');
        $response->assertSee('Bupati Brebes');
        $response->assertSee('Dewan Pembina');
        $response->assertSee('DPKH Kabupaten Brebes');
        $response->assertSee('Dewan Syariah');
        $response->assertSee('Kemenag Kabupaten Brebes');

        // Bagian 2: Pengurus Harian
        $response->assertSee('Chasan Mudhofar');
        $response->assertSee('Ujang TSM, SH.');
        $response->assertSee('Tarjono');
        $response->assertSee('Agus Riyanto, S.Pd.');

        // Bagian 3: Bidang-Bidang
        $response->assertSee('Bidang Pendidikan');
        $response->assertSee('Kusmanto, M.Pd.');
        $response->assertSee('dr. Dedy Iskandar Zulkarnaen, MM.');

        $response->assertSee("Bidang Da'wah");
        $response->assertSee("Ma'mur Roziqin, M.PdI.");
        $response->assertSee('Imam Djaelani');
        $response->assertSee('H. Sofro Rahman');

        $response->assertSee('Bidang Pengembangan Program');
        $response->assertSee('Budi Santosa, SPT, M.Si.');
        $response->assertSee('Yustar Khamidi, SH.');

        $response->assertSee('Bidang Tim Ahli');
        $response->assertSee('drh. Ismu Subroto, M.Si');
        $response->assertSee('drh. Wiwien Kusumawati');

        $response->assertSee('Bidang Usaha');
        $response->assertSee('Darmawan');
        $response->assertSee('Negus Hisyam Maula');
        $response->assertSee('Agus Riyanto');

        $response->assertSee('Bidang Humas');
        $response->assertSee('Sugiharto');
        $response->assertSee('S. Sapta Widiarso');
        $response->assertSee('Ahmad Yusdi');

        $response->assertSee('Bidang Adat');
        $response->assertSee('Bambang Sugito');
        $response->assertSee('Mulyanto');
        $response->assertSee('Asmawi');

        $response->assertSee('Bidang Perlengkapan/Umum');
        $response->assertSee('Hamim Basori');
        $response->assertSee('Taufik');
        $response->assertSee('Drajat');

        $response->assertSee('Bidang Sosial & Kebencanaan');
        $response->assertSee('Tengku Latif');
        $response->assertSee("Ma'un, S.Pd");
        $response->assertSee('Eko Sis A.');
    }

    public function test_internal_note_never_appears_in_public_html(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);
        $response->assertDontSee('Perlu dikonfirmasi: orang yang sama?');
    }

    public function test_initials_ignore_academic_and_honorary_titles(): void
    {
        $member1 = new BoardMember(['name' => 'dr. Dedy Iskandar Zulkarnaen, MM.']);
        $this->assertEquals('DI', $member1->initials);

        $member2 = new BoardMember(['name' => 'Ujang TSM, SH.']);
        $this->assertEquals('UT', $member2->initials);

        $member3 = new BoardMember(['name' => 'Agus Riyanto, S.Pd.']);
        $this->assertEquals('AR', $member3->initials);

        $member4 = new BoardMember(['name' => 'drh. Wiwien Kusumawati']);
        $this->assertEquals('WK', $member4->initials);

        $member5 = new BoardMember(['name' => 'drh. Ismu Subroto, M.Si']);
        $this->assertEquals('IS', $member5->initials);

        $member6 = new BoardMember(['name' => 'Budi Santosa, SPT, M.Si.']);
        $this->assertEquals('BS', $member6->initials);

        $member7 = new BoardMember(['name' => 'Chasan Mudhofar']);
        $this->assertEquals('CM', $member7->initials);

        $member8 = new BoardMember(['name' => 'Tarjono']);
        $this->assertEquals('TA', $member8->initials);
    }

    public function test_inactive_board_members_do_not_appear_in_public(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $member = BoardMember::where('name', 'Tengku Latif')->first();
        $member->update(['is_active' => false]);

        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);
        $response->assertDontSee('Tengku Latif');
    }

    public function test_board_sk_note_displays_only_when_filled(): void
    {
        $this->seed(BoardMemberSeeder::class);

        // Ketika kosong
        Setting::set('board_sk_note', '', 'profile');
        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);
        $response->assertDontSee('Berdasarkan');

        // Ketika diisi
        Setting::set('board_sk_note', 'Keputusan Rapat Kerja Daerah DPD JULEHA Brebes No. 01/2024', 'profile');
        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);
        $response->assertSee('Berdasarkan Keputusan Rapat Kerja Daerah DPD JULEHA Brebes No. 01/2024');
    }

    public function test_policy_restricts_board_members_management_to_authorized_roles(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $adminKegiatan = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN, 'is_active' => true]);
        $editorKonten = User::factory()->create(['role' => User::ROLE_EDITOR_KONTEN, 'is_active' => true]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'is_active' => true]);
        $inactiveAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => false]);

        $policy = new BoardMemberPolicy;

        $this->assertTrue($policy->viewAny($superAdmin));
        $this->assertTrue($policy->viewAny($adminKegiatan));
        $this->assertTrue($policy->viewAny($editorKonten));
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->update($adminKegiatan, new BoardMember));
        $this->assertTrue($policy->delete($editorKonten, new BoardMember));

        // Role peserta dan user nonaktif ditolak
        $this->assertFalse($policy->viewAny($participant));
        $this->assertFalse($policy->create($participant));
        $this->assertFalse($policy->viewAny($inactiveAdmin));
    }
}
