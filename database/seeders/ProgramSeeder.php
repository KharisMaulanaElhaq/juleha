<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Konten 7 Program Resmi DPD JULEHA Kabupaten Brebes:
     * - Layanan Masyarakat:
     *   1. Penyembelihan Hewan Aqiqah
     *   2. Qurban
     *   3. Syiar dan Edukasi Keliling
     * - Pendidikan & Sertifikasi:
     *   4. Bimtek dan Pelatihan
     *   5. Sertifikasi Kompetensi BNSP (netral, LSP mitra editable admin)
     *   6. Training of Trainer (TOT) Juleha
     *   7. Instruktur BNSP
     */
    public function run(): void
    {
        $programs = [
            [
                'title' => 'Penyembelihan Hewan Aqiqah',
                'slug' => 'penyembelihan-aqiqah',
                'category' => 'Layanan Masyarakat',
                'summary' => 'Layanan pendampingan dan pelaksanaan penyembelihan hewan aqiqah yang sesuai syariat Islam dan standar higienis di wilayah Kabupaten Brebes.',
                'description' => '<p>DPD JULEHA Kabupaten Brebes menyediakan layanan pendampingan dan pelaksanaan penyembelihan hewan aqiqah untuk masyarakat. Seluruh proses dilakukan oleh juru sembelih yang memahami fiqih penyembelihan, adab ihsan terhadap hewan, dan higienitas penanganan karkas.</p><p>Kami berkomitmen memastikan setiap hewan aqiqah disembelih dengan doa dan niat yang benar, cepat, memutus saluran pernapasan, makanan, dan pembuluh darah leher secara sempurna tanpa menimbulkan rasa sakit berkepanjangan pada hewan.</p>',
                'icon' => 'heart',
                'lsp_name' => null,
                'features' => [
                    'Penerapan adab ihsan dan perlakuan ramah hewan sebelum penyembelihan',
                    'Pemotongan tepat memutus 3 saluran (hulkum, mari\', dan wadajain) sesuai tuntunan syariat',
                    'Kebersihan dan sanitasi alat sembelih terjaga berkala',
                    'Penanganan pasca-sembelih yang higienis dan terstandar',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Hubungi narahubung sekretariat DPD JULEHA Brebes melalui tombol WhatsApp',
                    'Sampaikan rencana tanggal, lokasi, dan jumlah hewan aqiqah yang akan disembelih',
                    'Konfirmasi koordinasi teknis waktu dan kehadiran juru sembelih di lokasi',
                ],
                'cta_type' => 'whatsapp',
                'cta_text' => 'Hubungi via WhatsApp',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan layanan Penyembelihan Hewan Aqiqah DPD JULEHA Brebes.',
                'sort_order' => 1,
                'is_active' => true,
                'meta_title' => 'Layanan Penyembelihan Hewan Aqiqah Syar\'i – DPD JULEHA Brebes',
                'meta_description' => 'Layanan dan pendampingan penyembelihan hewan aqiqah syar\'i, higienis, dan amanah di Kabupaten Brebes bersama DPD JULEHA.',
            ],
            [
                'title' => 'Qurban',
                'slug' => 'qurban',
                'category' => 'Layanan Masyarakat',
                'summary' => 'Pendampingan dan pelayanan penyembelihan hewan qurban saat Iduladha untuk panitia masjid, musholla, instansi, maupun warga di Kabupaten Brebes.',
                'description' => '<p>Program Qurban DPD JULEHA Kabupaten Brebes hadir untuk membantu panitia kurban masjid dan instansi dalam melaksanakan ibadah qurban secara tertib syar\'i, aman, dan higienis.</p><p>Relawan dan juru sembelih DPD JULEHA mengutamakan teknik perebahan (handling) yang tenang tanpa membanting hewan, penggunaan bilah yang sangat tajam, serta pemotongan 3 saluran leher dalam satu tarikan/dorongan untuk menjaga keabsahan kurban para mudhahhi.</p>',
                'icon' => 'award',
                'lsp_name' => null,
                'features' => [
                    'Teknik perebahan (handling) tali yang aman dan meminimalkan stres hewan kurban',
                    'Pemeriksaan ketajaman bilah sesuai standar amanah sembelih',
                    'Penyembelihan cepat dan tuntas memutus 3 saluran leher',
                    'Pendampingan edukasi pemisahan karkas dan jeroan agar daging tetap higienis',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Panitia DKM atau instansi mengajukan permohonan pendampingan qurban sebelum Iduladha',
                    'Koordinasi jadwal dan pemetaan juru sembelih di titik lokasi pemotongan',
                    'Pelaksanaan penyembelihan bersama tim DPD JULEHA Brebes di hari tasyrik',
                ],
                'cta_type' => 'whatsapp',
                'cta_text' => 'Hubungi via WhatsApp',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan layanan Pendampingan dan Penyembelihan Qurban DPD JULEHA Brebes.',
                'sort_order' => 2,
                'is_active' => true,
                'meta_title' => 'Layanan Pendampingan Qurban Syar\'i – DPD JULEHA Brebes',
                'meta_description' => 'Pelayanan dan pendampingan penyembelihan hewan qurban syar\'i, beradab, dan higienis untuk masjid dan panitia Iduladha se-Kabupaten Brebes.',
            ],
            [
                'title' => 'Syiar dan Edukasi Keliling (instansi, ponpes, kampus, dll)',
                'slug' => 'syiar-edukasi',
                'category' => 'Layanan Masyarakat',
                'summary' => 'Sosialisasi fiqih sembelih halal dan adab ihsan ke instansi pemerintah, pondok pesantren, kampus, majelis taklim, dan komunitas warga se-Kabupaten Brebes.',
                'description' => '<p>Syiar dan Edukasi Keliling merupakan inisiatif dakwah literasi kehalalan daging oleh DPD JULEHA Kabupaten Brebes. Kami siap hadir ke pondok pesantren, instansi kedinasan, kampus perguruan tinggi, sekolah, dan majelis taklim di seluruh wilayah Brebes.</p><p>Materi yang disampaikan meliputi fiqih dasar sembelih halal, adab memperlakukan hewan sebelum disembelih, teknik asah bilah yang benar, serta cara mengenali daging ASUH (Aman, Sehat, Utuh, Halal) bagi masyarakat luas.</p>',
                'icon' => 'megaphone',
                'lsp_name' => null,
                'features' => [
                    'Pemaparan fiqih sembelih halal oleh narasumber DPD JULEHA Brebes',
                    'Demo teknik asah bilah praktis dan pengujian ketajaman standar sembelih',
                    'Edukasi animal welfare dan adab ihsan terhadap hewan ternak',
                    'Sesi tanya jawab langsung seputar isu sembelih di masyarakat',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Pengurus instansi, ponpes, atau majelis mengajukan surat permohonan atau narahubung via WhatsApp',
                    'Penyelarasan topik materi, tanggal kegiatan, dan lokasi pertemuan',
                    'Tim edukasi DPD JULEHA hadir mengisi sesi syiar dan peragaan',
                ],
                'cta_type' => 'whatsapp',
                'cta_text' => 'Undang Narasumber via WhatsApp',
                'cta_whatsapp_message' => 'Halo Admin, kami ingin mengundang narasumber DPD JULEHA Brebes untuk kegiatan Syiar dan Edukasi Keliling Sembelih Halal.',
                'sort_order' => 3,
                'is_active' => true,
                'meta_title' => 'Syiar & Edukasi Keliling Sembelih Halal – DPD JULEHA Brebes',
                'meta_description' => 'Program sosialisasi dan edukasi keliling fiqih sembelih halal untuk pesantren, kampus, instansi, dan masyarakat Kabupaten Brebes.',
            ],
            [
                'title' => 'Bimtek dan Pelatihan',
                'slug' => 'bimtek-pelatihan',
                'category' => 'Pendidikan & Sertifikasi',
                'summary' => 'Bimbingan teknis intensif teori fiqih dan praktik penyembelihan hewan halal bagi juru sembelih, pengurus masjid, dan praktisi di Kabupaten Brebes.',
                'description' => '<p>Bimbingan Teknis (Bimtek) Juru Sembelih Halal diselenggarakan secara berkala oleh DPD JULEHA Kabupaten Brebes untuk membina kompetensi para juru sembelih. Pelatihan memadukan kajian hukum syariat Islam dengan praktik lapangan secara proporsional.</p><p>Peserta mempelajari rukun dan syarat penyembelihan, adab doa tasmiyah, anatomi tiga saluran leher (hulkum, mari\', wadajain), teknik perebahan hewan yang ramah, serta manajemen bilah tajam berstandar uji kertas/rambut.</p>',
                'icon' => 'book-open',
                'lsp_name' => null,
                'features' => [
                    'Kajian mendalam fiqih penyembelihan syar\'i bersama pemateri kompeten',
                    'Praktik teknik pengasahan bilah golok/pisau sembelih',
                    'Praktik teknik simpul tali dan perebahan hewan tanpa kekerasan',
                    'Simulasi penyembelihan langsung dan evaluasi teknis',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Pilih angkatan pelatihan yang sedang dibuka pada menu Jadwal Kegiatan',
                    'Lakukan pendaftaran melalui portal peserta resmi',
                    'Konfirmasi keikutsertaan kepada panitia pelaksana DPD JULEHA Brebes',
                ],
                'cta_type' => 'bimtek',
                'cta_text' => 'Daftar Bimtek Sekarang',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan jadwal dan pendaftaran Bimtek Juru Sembelih Halal DPD JULEHA Brebes.',
                'sort_order' => 4,
                'is_active' => true,
                'meta_title' => 'Bimtek & Pelatihan Juru Sembelih Halal – DPD JULEHA Brebes',
                'meta_description' => 'Bimbingan teknis resmi teori fiqih dan praktik sembelih halal bersertifikat di Kabupaten Brebes bersama DPD JULEHA.',
            ],
            [
                'title' => 'Sertifikasi Kompetensi BNSP',
                'slug' => 'sertifikasi-kompetensi-bnsp',
                'category' => 'Pendidikan & Sertifikasi',
                'summary' => 'Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra. Informasi lengkap akan diumumkan oleh DPD.',
                'description' => '<p>Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra. Informasi lengkap akan diumumkan oleh DPD.</p>',
                'icon' => 'badge-check',
                'lsp_name' => null,
                'features' => [
                    'Fasilitasi uji kompetensi juru sembelih halal bersama lembaga sertifikasi mitra',
                    'Standardisasi keahlian juru sembelih halal syar\'i dan higienis',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Konsultasikan kesiapan portofolio dengan tim sertifikasi DPD JULEHA via WhatsApp',
                    'Pengumuman resmi angkatan uji kompetensi akan diumumkan di situs DPD JULEHA Brebes',
                ],
                'cta_type' => 'whatsapp',
                'cta_text' => 'Tanya via WhatsApp',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan informasi fasilitasi Sertifikasi Kompetensi BNSP DPD JULEHA Brebes.',
                'sort_order' => 5,
                'is_active' => true,
                'meta_title' => 'Sertifikasi Kompetensi BNSP – DPD JULEHA Brebes',
                'meta_description' => 'Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra di Kabupaten Brebes.',
            ],
            [
                'title' => 'Training of Trainer (TOT) Juleha',
                'slug' => 'tot-juleha',
                'category' => 'Pendidikan & Sertifikasi',
                'summary' => 'Program pembekalan bagi juru sembelih senior untuk menjadi pelatih dan pendamping teknik sembelih halal di tingkat kecamatan se-Kabupaten Brebes.',
                'description' => '<p>Training of Trainer (TOT) dirancang khusus untuk mencetak kader pelatih dan instruktur sembelih halal di tingkat kecamatan. Program ini ditujukan bagi anggota JULEHA yang telah berpengalaman di lapangan dan memiliki komitmen dakwah yang kuat.</p><p>Peserta dibekali metode pengajaran interaktif, teknik demonstrasi handling dan asah bilah di hadapan publik, penguasaan fiqih lanjutan, serta kemampuan mendampingi panitia kurban dan juru sembelih pemula di wilayah masing-masing.</p>',
                'icon' => 'users',
                'lsp_name' => null,
                'features' => [
                    'Standardisasi materi pengajaran fiqih sembelih dan handling hewan',
                    'Pelatihan keterampilan komunikasi instruksional dan peragaan',
                    'Penguatan kapasitas kaderisasi pelatih di 17 kecamatan Kabupaten Brebes',
                    'Penugasan sebagai tim pendamping dan asisten instruktur DPD JULEHA',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Anggota aktif JULEHA Brebes yang telah mengikuti bimtek dan memiliki jam terbang penyembelihan',
                    'Mendapatkan rekomendasi dari koordinator bidang atau pengurus DPD',
                    'Mengikuti agenda TOT sesuai jadwal pembinaan yang ditetapkan',
                ],
                'cta_type' => 'whatsapp',
                'cta_text' => 'Tanya Informasi TOT',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan informasi seputar program Training of Trainer (TOT) DPD JULEHA Brebes.',
                'sort_order' => 6,
                'is_active' => true,
                'meta_title' => 'Training of Trainer (TOT) Juleha – DPD JULEHA Brebes',
                'meta_description' => 'Program pelatihan instruktur dan kader pelatih sembelih halal di tingkat kecamatan se-Kabupaten Brebes.',
            ],
            [
                'title' => 'Instruktur BNSP',
                'slug' => 'instruktur-bnsp',
                'category' => 'Pendidikan & Sertifikasi',
                'summary' => 'Fasilitasi pembinaan dan uji kompetensi jenjang instruktur/pelatih kejuruan sembelih halal melalui LSP mitra terlisensi BNSP.',
                'description' => '<p>Program Instruktur BNSP merupakan jenjang kompetensi lanjutan bagi pelatih sembelih halal yang ingin mendapatkan sertifikasi resmi metodologi pelatihan berbasis kompetensi (PBK) dari BNSP.</p><p>Melalui kerja sama dengan LSP mitra terlisensi, DPD JULEHA Kabupaten Brebes memfasilitasi calon instruktur dalam memenuhi standar kualifikasi nasional agar mampu mengajar, merancang kurikulum pelatihan kerja, dan menguji keterampilan sembelih halal secara profesional.</p>',
                'icon' => 'graduation-cap',
                'lsp_name' => 'LSP Mitra Terlisensi BNSP',
                'features' => [
                    'Pendalaman metodologi pelatihan kerja berbasis kompetensi (PBK)',
                    'Fasilitasi uji sertifikasi instruktur/pelatih bersama asesor LSP mitra',
                    'Pengakuan kualifikasi resmi sebagai instruktur kejuruan sembelih halal',
                    'Kesiapan membina bimbingan teknis di tingkat daerah maupun nasional',
                ],
                'requirements' => null,
                'how_to_join' => [
                    'Telah memiliki sertifikat kompetensi juru sembelih halal dan pengalaman sebagai trainer',
                    'Konsultasikan pemenuhan unit kompetensi metodologi instruktur bersama DPD JULEHA',
                    'Mengikuti asesmen jenjang instruktur melalui LSP mitra terlisensi BNSP',
                ],
                'cta_type' => 'whatsapp',
                'cta_text' => 'Konsultasi Jenjang Instruktur',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan informasi fasilitasi jenjang Instruktur BNSP Sembelih Halal melalui DPD JULEHA Brebes.',
                'sort_order' => 7,
                'is_active' => true,
                'meta_title' => 'Fasilitasi Jenjang Instruktur BNSP – DPD JULEHA Brebes',
                'meta_description' => 'Fasilitasi sertifikasi profesi instruktur metodologi pelatihan sembelih halal berstandar BNSP melalui LSP mitra di Brebes.',
            ],
        ];

        foreach ($programs as $prog) {
            $existing = Program::where('slug', $prog['slug'])->first();
            if ($existing) {
                // Jangan timpa jika admin sudah mengubahnya, perbarui hanya urutan & kategori & metadata dasar jika kosong
                $existing->update([
                    'sort_order' => $prog['sort_order'],
                    'category' => $prog['category'],
                    'title' => $prog['title'],
                ]);
            } else {
                Program::create($prog);
            }
        }
    }
}
