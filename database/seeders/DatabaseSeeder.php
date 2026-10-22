<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings
        $settings = [
            'site_name' => 'DPD JULEHA Kabupaten Brebes',
            'whatsapp_number' => '6282324448886',
            'whatsapp_secondary' => '62816333601',
            'admin_email' => 'dpdjulehabrebesofficial@gmail.com',
            'office_hours' => 'Senin – Jumat, 08.00 – 15.00 WIB',
            'office_address' => 'Jalan Kaliluwih, Wanasari, Kabupaten Brebes, Jawa Tengah',
            'sk_legalitas' => 'AHU-0000118.AH.01.07.TAHUN 2019',
            'vision' => 'Terwujudnya tata kelola dan juru sembelih halal yang profesional, kompeten, dan beradab di Kabupaten Brebes.',
            'mission' => json_encode([
                'Membina dan mengedukasi juru sembelih tentang fiqih penyembelihan syar’i Madzhab Syafi’i dan fatwa MUI.',
                'Menerapkan kaidah kesejahteraan hewan (animal welfare) dan higienitas penanganan daging.',
                'Menyelenggarakan bimbingan teknis dan fasilitasi uji kompetensi bersertifikat BNSP.',
                'Membangun sinergi dengan panitia kurban masjid, Rumah Pemotongan Hewan (RPH), dan dinas terkait.',
            ], JSON_UNESCAPED_UNICODE),
            'counter_juleha' => '250',
            'counter_bimtek' => '12',
            'counter_districts' => '17',
            'counter_rph' => '8',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 1. Program Resmi (7 Program)
        $this->call(ProgramSeeder::class);

        // 2. Events (Jadwal Kegiatan & Pelatihan 3 Jenis)
        $bimtekProgram = Program::where('slug', 'bimtek-pelatihan')->first();
        $sertifikasiProgram = Program::where('slug', 'sertifikasi-kompetensi-bnsp')->first();
        $totProgram = Program::where('slug', 'tot-juleha')->first();

        $event1 = Event::updateOrCreate(
            ['slug' => 'bimtek-angkatan-1'],
            [
                'title' => '[CONTOH] Bimtek Juru Sembelih Halal Angkatan Terdekat Brebes',
                'type' => EventType::Bimtek,
                'program_id' => $bimtekProgram?->id,
                'starts_at' => now()->addDays(14)->setTime(8, 0),
                'ends_at' => now()->addDays(14)->setTime(16, 0),
                'location' => '[CONTOH] Gedung Islamic Centre Brebes, Jl. Yos Sudarso No. 1',
                'quota' => 35,
                'fee' => 0,
                'status' => 'open',
                'description' => '[CONTOH] Pelatihan komprehensif fiqih sembelih halal, anatomi 3 saluran leher hewan, teknik asah bilah mikro-bevel, serta adab ihsan terhadap hewan sembelihan.',
                'curriculum' => [
                    'Fiqih Ibadah Penyembelihan & Doa Tasmiyah',
                    'Anatomi Leher: Hulkum, Mari\', dan Wadajain',
                    'Manajemen Bilah & Teknik Asah Golok Sembelih',
                    'Standar Higiene, Sanitasi Karkas, dan Daging ASUH',
                ],
            ]
        );

        $event2 = Event::updateOrCreate(
            ['slug' => 'sertifikasi-kompetensi-bnsp-angkatan-1'],
            [
                'title' => '[CONTOH] Uji Kompetensi & Sertifikasi BNSP Juru Sembelih Halal',
                'type' => EventType::SertifikasiKompetensi,
                'program_id' => $sertifikasiProgram?->id,
                'starts_at' => now()->addDays(30)->setTime(8, 30),
                'ends_at' => now()->addDays(30)->setTime(17, 0),
                'location' => '[CONTOH] RPH Brebes & TPH Terpadu',
                'quota' => 20,
                'fee' => 0,
                'status' => 'open',
                'description' => '[CONTOH] Asesmen kompetensi profesi Juleha berstandar Badan Nasional Sertifikasi Profesi (BNSP) melalui lembaga sertifikasi mitra.',
                'curriculum' => [
                    'Penerapan K3 di Area Penyembelihan',
                    'Pemeriksaan Antemortem & Kesejahteraan Hewan',
                    'Penyembelihan Ruminansia Besar Sekali Sayat',
                    'Pemeriksaan Postmortem & Penanganan Karkas',
                ],
            ]
        );

        $event3 = Event::updateOrCreate(
            ['slug' => 'tot-juleha-angkatan-1'],
            [
                'title' => '[CONTOH] Training of Trainer (TOT) Pelatih Sembelih Halal Brebes',
                'type' => EventType::Tot,
                'program_id' => $totProgram?->id,
                'starts_at' => now()->addDays(60)->setTime(8, 0),
                'ends_at' => now()->addDays(61)->setTime(17, 0),
                'location' => '[CONTOH] Aula Pusat Dakwah Brebes',
                'quota' => 15,
                'fee' => 0,
                'status' => 'open',
                'description' => '[CONTOH] Pembekalan calon pelatih dan instruktur teknik sembelih halal untuk kaderisasi di tingkat kecamatan.',
                'curriculum' => [
                    'Metodologi Pelatihan & Komunikasi Publik',
                    'Standardisasi Praktik Sembelih Syar\'i',
                    'Praktik Mengajar & Mentoring Lapangan',
                ],
            ]
        );

        $event5 = Event::updateOrCreate(
            ['slug' => 'bimtek-panitia-kurban-lampau'],
            [
                'title' => '[CONTOH] Pelatihan Khusus Panitia Kurban Masjid Se-Brebes',
                'type' => EventType::Bimtek,
                'program_id' => $bimtekProgram?->id,
                'starts_at' => now()->subMonths(3)->setTime(8, 0),
                'ends_at' => now()->subMonths(3)->setTime(15, 0),
                'location' => '[CONTOH] Aula Kemenag Brebes',
                'quota' => 50,
                'fee' => 0,
                'status' => 'done',
                'description' => '[CONTOH] Pembekalan teknis perebahan sapi aman (metode burley/rope), handling hewan, dan manajemen distribusi kurban.',
            ]
        );

        // 3. Registrations Contoh
        Registration::updateOrCreate(
            ['code' => 'JLH-2610-SMP1'],
            [
                'event_id' => $event1->id,
                'full_name' => '[CONTOH] Ahmad Subhan',
                'phone' => '6281234567891',
                'email' => 'ahmad.subhan@example.test',
                'district' => 'Brebes',
                'village' => 'Gandasuli',
                'participant_type' => 'calon_juleha',
                'notes' => 'Ingin belajar teknik sembelih syar\'i.',
                'status' => 'confirmed',
                'consent' => true,
                'consent_at' => now()->subDays(2),
            ]
        );

        Registration::updateOrCreate(
            ['code' => 'JLH-2610-SMP2'],
            [
                'event_id' => $event1->id,
                'full_name' => '[CONTOH] H. Mustofa',
                'phone' => '6281234567892',
                'email' => 'mustofa@example.test',
                'district' => 'Bumiayu',
                'village' => 'Dukuhturi',
                'participant_type' => 'pengurus_masjid',
                'notes' => 'Takmir Masjid Jami Al-Ikhlas.',
                'status' => 'pending',
                'consent' => true,
                'consent_at' => now()->subHours(5),
            ]
        );

        // 4. Board Members (Pengurus)
        // 4. Struktur Pengurus Resmi DPD JULEHA Brebes (Masa bakti 2024-2029)
        $this->call(BoardMemberSeeder::class);

        // 5. Posts (Berita & Edukasi)
        $posts = [
            [
                'title' => '[CONTOH] Memahami 3 Saluran Utama Leher Hewan yang Wajib Putus Saat Menyembelih',
                'slug' => 'syarat-sah-penyembelihan-halal',
                'category' => 'Fiqih Sembelih',
                'author_name' => 'Bidang Fatwa & Syariah',
                'summary' => 'Dalam fiqih Islam, sahnya penyembelihan hewan halal sangat bergantung pada terputusnya saluran pernapasan, saluran pencernaan, dan dua pembuluh darah leher.',
                'body' => "Penyembelihan hewan dalam Islam bukan sekadar proses mematikan hewan, melainkan ritual ibadah yang menentukan apakah daging yang dihasilkan berstatus halal atau menjadi bangkai yang haram dikonsumsi.\n\nPara ulama empat mazhab bersepakat mengenai pentingnya memutuskan saluran utama di bagian leher hewan dengan menggunakan alat yang tajam dan menyebut nama Allah SWT.\n\nTiga saluran utama yang wajib diperhatikan oleh setiap juru sembelih halal adalah:\n1. Hulkum (Tenggorokan / Saluran Pernapasan): Saluran jalannya udara dari hidung menuju paru-paru.\n2. Mari' (Kerongkongan / Saluran Makanan): Saluran jalannya makanan dan minuman dari rongga mulut menuju lambung.\n3. Wadajain (Dua Urat Leher / Pembuluh Darah): Dua pembuluh darah vena jugularis dan arteri karotis di kanan dan kiri leher.\n\nDPD JULEHA Kabupaten Brebes senantiasa menekankan pentingnya penguasaan posisi letak ketiga saluran ini serta keterampilan menyayat bilah secara cepat, mantap, dan sekali sayat tanpa jeda.",
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => '[CONTOH] Teknik Mengasah Golok dan Pisau Sembelih Hingga Ketajaman Maksimal',
                'slug' => 'tips-mengasah-bilah-sembelih',
                'category' => 'Teknis & Alat',
                'author_name' => 'Bidang Pelatihan DPD',
                'summary' => 'Bilah yang tajam sempurna adalah syarat mutlak prinsip ihsan dalam penyembelihan hewan agar proses berlangsung sekejap dan tanpa menyiksa.',
                'body' => "Rasulullah SAW bersabda: \"Dan apabila kamu menyembelih, maka sembelihlah dengan cara yang baik, dan hendaklah salah seorang di antara kalian menajamkan pisaunya dan memberi kenyamanan pada hewan sembelihannya.\" (HR. Muslim).\n\nMengasah bilah sembelih memerlukan pemahaman terhadap grit batu asah, mulai dari grit kasar (grit 1000) untuk membentuk sudut bevel mikro, hingga grit halus (grit 3000 - 6000) dan stropping kulit untuk membersihkan burr.\n\nBilah sembelih yang siap digunakan idealnya lulus uji ketajaman potong kertas tipis secara mulus atau uji cukur rambut tanpa tarikan kasar.",
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => '[CONTOH] Panduan Memilih Daging ASUH (Aman, Sehat, Utuh, Halal) di Pasar Tradisional',
                'slug' => 'mengenal-daging-asuh',
                'category' => 'Edukasi Konsumen',
                'author_name' => 'Humas & Edukasi Masyarakat',
                'summary' => 'Kenali tanda-tanda fisik daging berkualitas yang disembelih secara syar\'i untuk menjaga kesehatan keluarga.',
                'body' => "Masyarakat Kabupaten Brebes dihimbau untuk lebih teliti saat membeli daging ruminansia maupun unggas di pasar.\n\nDaging yang baik memiliki ciri warna merah segar khas, beraroma khas daging segar (tidak berbau asam atau amonia), memiliki tekstur kenyal dan bila ditekan akan kembali ke bentuk semula, serta tidak mengeluarkan cairan darah berlebih.\n\nHindari membeli daging yang tampak pucat berair (tanda glonggongan) atau kebiruan gelap yang menandakan darah tidak tertiris sempurna saat penyembelihan.",
                'is_published' => true,
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($posts as $p) {
            Post::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }

        // 6. Gallery Items
        $galleries = [
            [
                'title' => '[CONTOH] Praktik Perebahan Sapi Kurban Tanpa Kekerasan',
                'caption' => 'Pelatihan teknik tali burley untuk merebahkan hewan kurban secara tenang dan aman bagi juru sembelih.',
                'category' => 'Bimtek & Pelatihan',
                'image' => '/assets/img/gallery-1.jpg',
                'sort_order' => 1,
            ],
            [
                'title' => '[CONTOH] Uji Ketajaman Pisau Sembelih HHT (Hair Shaving Test)',
                'caption' => 'Setiap bilah peserta diuji kelayakan dan ketajamannya sebelum dinyatakan siap dipakai menyembelih.',
                'category' => 'Uji Kompetensi',
                'image' => '/assets/img/gallery-2.jpg',
                'sort_order' => 2,
            ],
            [
                'title' => '[CONTOH] Pendampingan Pemotongan Hewan Kurban di Masjid Jami',
                'caption' => 'Tim DPD JULEHA Brebes mendampingi panitia kurban untuk memastikan 3 saluran terputus sempurna.',
                'category' => 'Pengawasan Kurban',
                'image' => '/assets/img/gallery-3.jpg',
                'sort_order' => 3,
            ],
            [
                'title' => '[CONTOH] Silaturahmi Akbar dan Rakorda JULEHA Brebes',
                'caption' => 'Konsolidasi pengurus 17 koordinator kecamatan guna pemutakhiran data juru sembelih halal.',
                'category' => 'Silaturahmi & Raker',
                'image' => '/assets/img/gallery-4.jpg',
                'sort_order' => 4,
            ],
        ];

        foreach ($galleries as $g) {
            GalleryItem::updateOrCreate(
                ['title' => $g['title']],
                $g
            );
        }

        // 7. FAQs
        $faqs = [
            [
                'category' => 'Umum & Organisasi',
                'question' => 'Apa itu JULEHA dan apa perannya di Kabupaten Brebes?',
                'answer' => 'JULEHA adalah singkatan dari Juru Sembelih Halal. DPD JULEHA Kabupaten Brebes merupakan wadah pembinaan, standarisasi, dan sertifikasi bagi para penyembelih hewan agar proses penyembelihan di Brebes terjamin kehalalannya, higienis, dan menerapkan prinsip ihsan.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Bimbingan Teknis',
                'question' => 'Siapa saja yang dapat mendaftar bimbingan teknis JULEHA?',
                'answer' => 'Pelatihan terbuka untuk takmir masjid, panitia kurban, tukang jagal tradisional, petugas RPH/TPH, santri, dan masyarakat muslim umum yang ingin menguasai keterampilan sembelih syar\'i.',
                'sort_order' => 2,
            ],
            [
                'category' => 'Fiqih Sembelih',
                'question' => 'Apa saja 3 saluran leher hewan yang wajib putus?',
                'answer' => 'Tiga saluran tersebut adalah: 1. Hulkum (saluran napas), 2. Mari\' (saluran makan), dan 3. Wadajain (dua pembuluh darah kiri dan kanan). Semua harus terputus dalam satu gerakan cepat tanpa jeda.',
                'sort_order' => 3,
            ],
            [
                'category' => 'Kurban & Jasa',
                'question' => 'Apakah panitia masjid dapat mengundang instruktur JULEHA untuk pelatihan internal?',
                'answer' => 'Ya, DPD JULEHA Brebes melayani permohonan narasumber dan pendampingan kurban untuk takmir masjid di 17 kecamatan. Silakan hubungi nomor WhatsApp sekretariat kami.',
                'sort_order' => 4,
            ],
        ];

        foreach ($faqs as $f) {
            Faq::updateOrCreate(
                ['question' => $f['question']],
                $f
            );
        }
    }
}
