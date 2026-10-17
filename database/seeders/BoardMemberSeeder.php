<?php

namespace Database\Seeders;

use App\Models\BoardMember;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class BoardMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Simpan pengaturan periode & SK kepengurusan
        Setting::set('board_period', '2024-2029', 'profile');
        if (! Setting::where('key', 'board_sk_note')->exists()) {
            Setting::set('board_sk_note', '', 'profile');
        }

        // 2. Hapus data contoh/dummy lama yang berlabel [CONTOH]
        BoardMember::where('name', 'like', '[CONTOH]%')->delete();

        // 3. Selaraskan data lama non-contoh yang sudah ada agar tidak bentrok
        $legacyMappings = [
            'Chasan Mudofar' => ['name' => 'Chasan Mudhofar', 'position' => 'Ketua', 'group' => BoardMember::GROUP_PIMPINAN_HARIAN, 'kind' => BoardMember::KIND_PERSON, 'division' => null, 'sort_order' => 4],
            'Ujang TSM, S.H' => ['name' => 'Ujang TSM, SH.', 'position' => 'Sekretaris I', 'group' => BoardMember::GROUP_PIMPINAN_HARIAN, 'kind' => BoardMember::KIND_PERSON, 'division' => null, 'sort_order' => 5],
            'TARJONO' => ['name' => 'Tarjono', 'position' => 'Bendahara I', 'group' => BoardMember::GROUP_PIMPINAN_HARIAN, 'kind' => BoardMember::KIND_PERSON, 'division' => null, 'sort_order' => 6],
            'Kusmanto, M.Pd' => ['name' => 'Kusmanto, M.Pd.', 'position' => null, 'group' => BoardMember::GROUP_BIDANG, 'kind' => BoardMember::KIND_PERSON, 'division' => 'Bidang Pendidikan', 'sort_order' => 8],
            'drh.Ismu Subroto, M.Si' => ['name' => 'drh. Ismu Subroto, M.Si', 'position' => null, 'group' => BoardMember::GROUP_BIDANG, 'kind' => BoardMember::KIND_PERSON, 'division' => 'Bidang Tim Ahli', 'sort_order' => 15],
            'Bambang Sugito, S.H' => ['name' => 'Bambang Sugito', 'position' => null, 'group' => BoardMember::GROUP_BIDANG, 'kind' => BoardMember::KIND_PERSON, 'division' => 'Bidang Adat', 'sort_order' => 23],
        ];

        foreach ($legacyMappings as $oldName => $attributes) {
            $existing = BoardMember::where('name', $oldName)->first();
            if ($existing) {
                $existing->update($attributes);
            }
        }

        // 4. Data Resmi Struktur Kepengurusan DPD JULEHA Brebes (Masa bakti 2024-2029)
        $officialMembers = [
            // ==========================================
            // KELOMPOK PELINDUNG & PEMBINA (LEMBAGA)
            // ==========================================
            [
                'kind' => BoardMember::KIND_INSTITUTION,
                'group' => BoardMember::GROUP_PELINDUNG_PEMBINA,
                'division' => null,
                'position' => 'Pelindung',
                'name' => 'Bupati Brebes',
                'sort_order' => 1,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_INSTITUTION,
                'group' => BoardMember::GROUP_PELINDUNG_PEMBINA,
                'division' => null,
                'position' => 'Dewan Pembina',
                'name' => 'DPKH Kabupaten Brebes',
                'sort_order' => 2,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_INSTITUTION,
                'group' => BoardMember::GROUP_PELINDUNG_PEMBINA,
                'division' => null,
                'position' => 'Dewan Syariah',
                'name' => 'Kemenag Kabupaten Brebes',
                'sort_order' => 3,
                'is_active' => true,
                'internal_note' => null,
            ],

            // ==========================================
            // KELOMPOK PIMPINAN HARIAN (PERORANGAN)
            // ==========================================
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
                'division' => null,
                'position' => 'Ketua',
                'name' => 'Chasan Mudhofar',
                'sort_order' => 4,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
                'division' => null,
                'position' => 'Sekretaris I',
                'name' => 'Ujang TSM, SH.',
                'sort_order' => 5,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
                'division' => null,
                'position' => 'Bendahara I',
                'name' => 'Tarjono',
                'sort_order' => 6,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
                'division' => null,
                'position' => 'Bendahara II',
                'name' => 'Agus Riyanto, S.Pd.',
                'sort_order' => 7,
                'is_active' => true,
                'internal_note' => 'Perlu dikonfirmasi: orang yang sama?',
            ],

            // ==========================================
            // KELOMPOK BIDANG-BIDANG (PERORANGAN)
            // ==========================================
            // Bidang Pendidikan
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Pendidikan',
                'position' => null,
                'name' => 'Kusmanto, M.Pd.',
                'sort_order' => 8,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Pendidikan',
                'position' => null,
                'name' => 'dr. Dedy Iskandar Zulkarnaen, MM.',
                'sort_order' => 9,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Da'wah
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => "Bidang Da'wah",
                'position' => null,
                'name' => "Ma'mur Roziqin, M.PdI.",
                'sort_order' => 10,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => "Bidang Da'wah",
                'position' => null,
                'name' => 'Imam Djaelani',
                'sort_order' => 11,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => "Bidang Da'wah",
                'position' => null,
                'name' => 'H. Sofro Rahman',
                'sort_order' => 12,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Pengembangan Program
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Pengembangan Program',
                'position' => null,
                'name' => 'Budi Santosa, SPT, M.Si.',
                'sort_order' => 13,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Pengembangan Program',
                'position' => null,
                'name' => 'Yustar Khamidi, SH.',
                'sort_order' => 14,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Tim Ahli
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Tim Ahli',
                'position' => null,
                'name' => 'drh. Ismu Subroto, M.Si',
                'sort_order' => 15,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Tim Ahli',
                'position' => null,
                'name' => 'drh. Wiwien Kusumawati',
                'sort_order' => 16,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Usaha
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Usaha',
                'position' => null,
                'name' => 'Darmawan',
                'sort_order' => 17,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Usaha',
                'position' => null,
                'name' => 'Negus Hisyam Maula',
                'sort_order' => 18,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Usaha',
                'position' => null,
                'name' => 'Agus Riyanto',
                'sort_order' => 19,
                'is_active' => true,
                'internal_note' => 'Perlu dikonfirmasi: orang yang sama?',
            ],

            // Bidang Humas
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Humas',
                'position' => null,
                'name' => 'Sugiharto',
                'sort_order' => 20,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Humas',
                'position' => null,
                'name' => 'S. Sapta Widiarso',
                'sort_order' => 21,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Humas',
                'position' => null,
                'name' => 'Ahmad Yusdi',
                'sort_order' => 22,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Adat
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Adat',
                'position' => null,
                'name' => 'Bambang Sugito',
                'sort_order' => 23,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Adat',
                'position' => null,
                'name' => 'Mulyanto',
                'sort_order' => 24,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Adat',
                'position' => null,
                'name' => 'Asmawi',
                'sort_order' => 25,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Perlengkapan/Umum
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Perlengkapan/Umum',
                'position' => null,
                'name' => 'Hamim Basori',
                'sort_order' => 26,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Perlengkapan/Umum',
                'position' => null,
                'name' => 'Taufik',
                'sort_order' => 27,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Perlengkapan/Umum',
                'position' => null,
                'name' => 'Drajat',
                'sort_order' => 28,
                'is_active' => true,
                'internal_note' => null,
            ],

            // Bidang Sosial & Kebencanaan
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Sosial & Kebencanaan',
                'position' => null,
                'name' => 'Tengku Latif',
                'sort_order' => 29,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Sosial & Kebencanaan',
                'position' => null,
                'name' => "Ma'un, S.Pd",
                'sort_order' => 30,
                'is_active' => true,
                'internal_note' => null,
            ],
            [
                'kind' => BoardMember::KIND_PERSON,
                'group' => BoardMember::GROUP_BIDANG,
                'division' => 'Bidang Sosial & Kebencanaan',
                'position' => null,
                'name' => 'Eko Sis A.',
                'sort_order' => 31,
                'is_active' => true,
                'internal_note' => null,
            ],
        ];

        foreach ($officialMembers as $memberData) {
            // Kunci updateOrCreate stabil menggunakan group + division + name
            // Dengan demikian "Agus Riyanto" (Bendahara II, group pimpinan_harian)
            // dan "Agus Riyanto" (Bidang Usaha, group bidang) dijamin tersimpan terpisah.
            BoardMember::updateOrCreate(
                [
                    'group' => $memberData['group'],
                    'division' => $memberData['division'],
                    'name' => $memberData['name'],
                ],
                $memberData
            );
        }
    }
}
