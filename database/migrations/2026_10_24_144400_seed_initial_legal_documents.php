<?php

use App\Models\Consent;
use App\Models\LegalDocument;
use App\Models\LegalDocumentVersion;
use App\Models\ParticipantProfile;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Teks Kebijakan Privasi yang sudah ada dimigrasikan sebagai versi 1.0 (PUBLISHED)
        $privacyContent = <<<'HTML'
<h2>1. Data Pribadi yang Kami Kumpulkan</h2>
<p>Untuk menyelenggarakan layanan portal kepesertaan, bimbingan teknis (bimtek), dan fasilitasi sertifikasi juru sembelih halal di Kabupaten Brebes, DPD JULEHA Kabupaten Brebes mengumpulkan jenis data berikut:</p>
<ul>
    <li><strong>Data Akun Google OAuth:</strong> Alamat email resmi, nama akun, foto profil avatar, dan Google ID (izin scope minimal <code>openid email profile</code>) untuk keperluan masuk dan verifikasi akun tunggal peserta.</li>
    <li><strong>Nomor Induk Kependudukan (NIK):</strong> 16 digit angka sesuai identitas KTP yang sah, wajib dilengkapi sebelum mendaftar pelatihan demi ketepatan pencetakan sertifikat.</li>
    <li><strong>Nama Lengkap Sesuai KTP:</strong> Nama lengkap resmi yang dicantumkan pada sertifikat pelatihan dan kompetensi profesi.</li>
    <li><strong>Nomor Kontak WhatsApp:</strong> Nomor telepon seluler aktif untuk koordinasi teknis, verifikasi pendaftaran, dan informasi bimbingan teknis.</li>
    <li><strong>Domisili &amp; Asal Instansi:</strong> Kecamatan, desa/kelurahan, dan instansi/masjid/RPH/RPU asal peserta di wilayah Kabupaten Brebes atau sekitarnya.</li>
    <li><strong>Data Kegiatan:</strong> Catatan pendaftaran bimtek, kehadiran sesi (presensi QR/kode), dan unduhan materi pelatihan.</li>
</ul>

<h2>2. Penggunaan Data dan Penerusan ke Lembaga Sertifikasi</h2>
<p>Data pribadi Anda dikumpulkan berdasarkan persetujuan yang Anda berikan saat melengkapi profil dan hanya digunakan untuk:</p>
<ul>
    <li>Penerbitan sertifikat bimbingan teknis resmi dari DPD JULEHA Kabupaten Brebes.</li>
    <li>Penerusan data nama sesuai KTP, NIK, dan data kontak kepada lembaga sertifikasi profesi resmi yaitu <strong>LSP Pertanian RI</strong> untuk keperluan pendaftaran uji kompetensi dan penerbitan sertifikat BNSP.</li>
    <li>Pencatatan hasil sertifikasi oleh DPD JULEHA Brebes untuk keperluan verifikasi keabsahan juru sembelih di wilayah Kabupaten Brebes.</li>
</ul>

<h2>3. Standar Keamanan &amp; Perlindungan Teknis Data Sensitif</h2>
<p>Kami memperlakukan NIK dan data peserta sebagai data pribadi sensitif dengan menerapkan pengamanan berlapis:</p>
<ul>
    <li><strong>Penyimpanan Terenkripsi:</strong> NIK Anda disimpan dalam kondisi terenkripsi di sistem basis data kami dan tidak dapat dibaca oleh pihak yang tidak berwenang.</li>
    <li><strong>Pencegahan Akun Ganda:</strong> Sistem menggunakan kode acak aman berbasis fungsi hash satu arah untuk memastikan satu NIK hanya dapat didaftarkan satu kali, tanpa membuka identitas mentah ke publik.</li>
    <li><strong>Penyamaran Tampilan:</strong> Nomor NIK Anda selalu disamarkan (contoh: <code>3329********1234</code>) pada layar dan tanda bukti keikutsertaan.</li>
    <li><strong>Pembatasan Akses &amp; Audit:</strong> Akses data dibatasi hanya untuk pengurus yang berwenang, dan setiap aktivitas penanganan data penting tercatat di buku log audit tanpa memuat NIK mentah.</li>
</ul>

<h2>4. Pihak yang Menerima Data Anda</h2>
<p>Kami menjaga amanah data Anda dengan penuh tanggung jawab:</p>
<ul>
    <li><strong>Pengurus DPD JULEHA Brebes:</strong> Petugas administrasi resmi yang bertugas memverifikasi kelayakan peserta dan mengurus administrasi pelatihan.</li>
    <li><strong>Lembaga Sertifikasi Profesi (LSP):</strong> Data nama sesuai KTP, NIK, dan kontak diteruskan kepada lembaga sertifikasi mitra semata-mata untuk proses uji kompetensi dan penerbitan sertifikat profesi resmi.</li>
    <li><strong>Penyedia Infrastruktur:</strong> Google (autentikasi masuk OAuth) serta penyedia hosting server dan email resmi sebagai pemroses teknis data.</li>
    <li><strong>Tidak Dijual ke Pihak Ketiga:</strong> DPD JULEHA Kabupaten Brebes tidak pernah dan tidak akan menjual, menyewakan, atau membagikan data Anda kepada pihak lain untuk tujuan iklan atau komersial.</li>
</ul>

<h2>5. Hak Peserta Atas Data Pribadi</h2>
<p>Setiap peserta memiliki kendali penuh atas data miliknya sesuai UU Perlindungan Data Pribadi (UU PDP):</p>
<ul>
    <li><strong>Hak Akses:</strong> Anda berhak melihat data pribadi Anda kapan saja melalui halaman Portal Peserta.</li>
    <li><strong>Hak Koreksi:</strong> Anda berhak memperbaiki nama, nomor kontak, maupun alamat jika ada kesalahan sebelum sertifikat resmi diterbitkan.</li>
    <li><strong>Hak Portabilitas:</strong> Anda berhak mengunduh salinan data pribadi yang tersimpan tentang Anda melalui menu Privasi di portal.</li>
    <li><strong>Hak Penarikan Persetujuan:</strong> Anda berhak menarik persetujuan opsional kapan saja dengan satu klik pada menu Privasi Portal.</li>
    <li><strong>Hak Penghapusan &amp; Anonimisasi:</strong> Anda berhak mengajukan permohonan penghapusan atau anonimisasi akun dan data pribadi dari sistem kami.</li>
</ul>

<h2>6. Konsekuensi Penarikan Persetujuan</h2>
<p>Persetujuan bersifat sukarela. Namun, apabila Anda menarik persetujuan wajib (seperti persetujuan validasi NIK atau penerusan ke LSP), sistem tidak dapat memproses pendaftaran pelatihan, Anda tidak dapat diikutsertakan dalam uji kompetensi, dan sertifikat resmi tidak dapat diterbitkan.</p>

<h2>7. Batas Usia Pengguna</h2>
<p>Layanan portal dan bimbingan teknis juru sembelih halal ditujukan bagi individu yang telah berusia minimal 18 (delapan belas) tahun atau telah cakap menurut hukum. Sistem tidak secara sengaja mengumpulkan data anak di bawah umur.</p>

<h2>8. Kebijakan Retensi &amp; Penghapusan Data (Anonimisasi)</h2>
<p>Bagi peserta yang telah menyelesaikan pelatihan atau memiliki sertifikat:</p>
<ul>
    <li><strong>Data yang Dihapus:</strong> Saat permohonan penghapusan data disetujui, identitas pribadi (Nama KTP, NIK terenkripsi, nomor WhatsApp, domisili, email) serta berkas fisik PDF sertifikat di penyimpanan privat dihapus permanen.</li>
    <li><strong>Catatan Minimal yang Dipertahankan:</strong> Catatan teknis minimal sertifikat (Nomor Sertifikat, Lembaga Penerbit, Tanggal Terbit, Masa Berlaku, dan Kode Verifikasi Publik) tetap disimpan demi integritas catatan profesi dan pencegahan sertifikat palsu. Nama pemegang akan ditampilkan sebagai "Data pemegang telah dihapus".</li>
    <li><strong>Masa Retensi:</strong> Catatan teknis dipertahankan selama masa berlaku sertifikat ditambah masa retensi yang ditetapkan oleh DPD bersama LSP mitra.</li>
</ul>

<h2>9. Prosedur Insiden Perlindungan Data</h2>
<p>Apabila terjadi insiden keamanan atau kegagalan pelindungan data pribadi pada sistem kami, DPD JULEHA Brebes akan segera mengambil tindakan pengamanan teknis, mengisolasi dampak, serta memberikan pemberitahuan resmi kepada peserta terdampak dan pihak berwenang sesuai ketentuan peraturan perundang-undangan yang berlaku.</p>

<h2>10. Perubahan Kebijakan &amp; Kontak</h2>
<p>Kebijakan privasi ini dapat diperbarui sewaktu-waktu. Apabila terdapat perubahan mendasar pada persetujuan wajib, peserta akan diminta memberikan persetujuan ulang pada saat masuk ke sistem. Pertanyaan dan pengaduan privasi dapat disampaikan melalui kontak sekretariat resmi kami.</p>
HTML;

        $privacyDoc = LegalDocument::updateOrCreate(
            ['slug' => LegalDocument::SLUG_KEBIJAKAN_PRIVASI],
            [
                'title' => 'Kebijakan Privasi & Perlindungan Data Pribadi',
                'version' => '1.0',
                'effective_date' => '2026-10-13',
                'content' => $privacyContent,
                'status' => LegalDocument::STATUS_PUBLISHED,
                'change_summary' => 'Penerbitan versi awal Kebijakan Privasi dan Kepatuhan UU PDP No. 27 Tahun 2022.',
                'is_mandatory_consent' => true,
                'published_at' => now(),
            ]
        );

        LegalDocumentVersion::updateOrCreate(
            [
                'legal_document_id' => $privacyDoc->id,
                'version' => '1.0',
            ],
            [
                'effective_date' => '2026-10-13',
                'content' => $privacyContent,
                'change_summary' => 'Penerbitan versi awal Kebijakan Privasi.',
                'published_at' => now(),
            ]
        );

        // 2. Dokumen Baru dibuat berstatus DRAFT (TIDAK tampil di publik sampai super_admin menerbitkannya)
        $termsContent = <<<'HTML'
<h2>1. Ketentuan Umum dan Pendaftaran Akun</h2>
<p>Selamat datang di situs resmi dan portal peserta DPD JULEHA Kabupaten Brebes. Dengan mendaftar, mengakses, atau menggunakan layanan ini, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh Syarat dan Ketentuan ini.</p>
<ul>
    <li>Akun peserta dibuat melalui layanan masuk Google OAuth resmi untuk memastikan keamanan dan verifikasi kepemilikan akun tunggal.</li>
    <li>Anda bertanggung jawab penuh atas kerahasiaan dan keamanan akun Google Anda serta seluruh aktivitas yang terjadi melalui akun Anda.</li>
</ul>

<h2>2. Kebenaran Data dan Kewajiban Peserta</h2>
<p>Setiap peserta bimbingan teknis berkewajiban untuk:</p>
<ul>
    <li>Memberikan data identitas resmi yang benar, akurat, dan mutakhir, termasuk Nomor Induk Kependudukan (NIK) dan nama lengkap sesuai KTP.</li>
    <li>Menghindari pemalsuan data identitas pribadi atau penggunaan identitas milik orang lain. Pemalsuan NIK atau identitas berakibat pembatalan status peserta dan sertifikat yang bersangkutan.</li>
    <li>Memperbarui data kontak (WhatsApp dan email aktif) guna kelancaran koordinasi pelatihan dan ujian.</li>
</ul>

<h2>3. Pendaftaran Kegiatan, Kuota, dan Hak Penyelenggara</h2>
<ul>
    <li>Setiap kegiatan bimbingan teknis memiliki kuota peserta tertentu yang ditetapkan panitia demi efektivitas dan keselamatan pelatihan praktik.</li>
    <li>Pengisian formulir pendaftaran merupakan pengajuan keikutsertaan. Panitia DPD JULEHA Brebes berhak memverifikasi kelayakan, mengonfirmasi, atau menolak pendaftaran jika kuota telah terpenuhi atau syarat administrasi belum lengkap.</li>
    <li>Pemberitahuan penerimaan atau penolakan disampaikan melalui portal peserta dan/atau pesan WhatsApp resmi panitia.</li>
</ul>

<h2>4. Tata Tertib Kehadiran dan Presensi Pelatihan</h2>
<ul>
    <li>Peserta wajib mengikuti rangkaian sesi bimbingan teknis secara penuh sesuai jadwal yang ditentukan.</li>
    <li>Presensi dilakukan secara mandiri oleh peserta melalui pemindaian kode QR sesi atau input kode angka resmi yang ditampilkan panitia di ruang pelatihan.</li>
    <li>Tindakan titip absen, membagikan kode presensi kepada pihak yang tidak hadir di lokasi, atau memanipulasi presensi merupakan pelanggaran berat yang membatalkan kelayakan sertifikasi.</li>
</ul>

<h2>5. Hak Cipta Materi Pelatihan</h2>
<ul>
    <li>Seluruh materi bimbingan teknis (modul pelatihan, slide presentasi, panduan praktikum, dan rekaman materi) adalah kekayaan intelektual milik DPD JULEHA Brebes dan/atau narasumber terkait.</li>
    <li>Materi pelatihan semata-mata disediakan untuk pembelajaran pribadi peserta terdaftar. Peserta dilarang keras mereproduksi, memperjualbelikan, atau menyebarluaskan materi tanpa izin tertulis dari DPD JULEHA Brebes.</li>
</ul>

<h2>6. Batasan Peran dan Tanggung Jawab Organisasi</h2>
<ul>
    <li>DPD JULEHA Kabupaten Brebes bertindak sebagai penyelenggara bimbingan teknis (bimtek) dan fasilitator pembinaan juru sembelih halal di wilayah Brebes.</li>
    <li>Sertifikat kompetensi profesi juru sembelih halal berstandar BNSP diterbitkan secara independen oleh Lembaga Sertifikasi Profesi (LSP) terlisensi resmi sesuai hasil uji kompetensi yang dilakukan oleh asesor LSP. DPD bukan merupakan lembaga penerbit sertifikat kompetensi negara tersebut.</li>
    <li>Informasi jadwal, materi, dan lokasi kegiatan dapat disesuaikan panitia apabila terjadi kendala teknis atau keadaan kahar (force majeure).</li>
</ul>

<h2>7. Penonaktifan dan Penangguhan Akun</h2>
<p>DPD JULEHA Brebes berhak menangguhkan atau menonaktifkan akun peserta yang terbukti melanggar syarat ketentuan ini, melakukan pelecehan, atau menyalahgunakan sistem portal.</p>

<h2>8. Hukum yang Berlaku dan Penyelesaian Sengketa</h2>
<p>Syarat dan Ketentuan ini diatur dan ditafsirkan berdasarkan hukum Negara Kesatuan Republik Indonesia. Setiap perselisihan yang timbul akan diupayakan untuk diselesaikan secara musyawarah dan kekeluargaan terlebih dahulu melalui Sekretariat DPD JULEHA Kabupaten Brebes.</p>
HTML;

        $termsDoc = LegalDocument::updateOrCreate(
            ['slug' => LegalDocument::SLUG_SYARAT_KETENTUAN],
            [
                'title' => 'Syarat dan Ketentuan Layanan',
                'version' => '1.0',
                'effective_date' => '2026-10-24',
                'content' => $termsContent,
                'status' => LegalDocument::STATUS_DRAFT,
                'change_summary' => 'Rancangan awal Syarat dan Ketentuan Layanan.',
                'is_mandatory_consent' => true,
                'published_at' => null,
            ]
        );

        $cookieContent = <<<'HTML'
<h2>1. Apa itu Penyimpanan di Perangkat?</h2>
<p>Saat Anda mengakses situs dan portal DPD JULEHA Kabupaten Brebes, situs menyimpan sejumlah kecil informasi teknis di peramban (browser) perangkat Anda. Informasi ini berfungsi agar sistem dapat mengenali Anda saat berpindah halaman dan menjaga keamanan akun Anda.</p>

<h2>2. Jenis Penyimpanan yang Digunakan Sistem Kami</h2>
<p>Sistem kami hanya menggunakan penyimpanan teknis yang benar-benar esensial untuk pengoperasian layanan:</p>
<ul>
    <li><strong>Cookie Sesi (Laravel Session):</strong> Berfungsi mengingat status masuk Anda sehingga Anda tidak perlu masuk ulang di setiap perpindahan halaman portal. Cookie ini otomatis berakhir saat sesi habis atau Anda keluar.</li>
    <li><strong>Token Keamanan CSRF (XSRF-TOKEN):</strong> Berfungsi melindungi akun Anda dari serangan pemalsuan permintaan antar-situs saat Anda mengirimkan formulir pendaftaran atau profil.</li>
    <li><strong>Cookie "Ingat Saya" (Remember Token):</strong> Tersimpan secara aman hanya jika Anda memilih opsi untuk tetap masuk pada perangkat pribadi Anda.</li>
    <li><strong>Penyimpanan Lokal (localStorage):</strong> Digunakan untuk menyimpan preferensi tampilan peramban Anda, yaitu:
        <ul>
            <li>Pilihan mode tampilan gelap (Dark Mode) atau terang.</li>
            <li>Pilihan skala ukuran huruf pembesar teks ramah lansia (Normal, Besar, Sangat Besar).</li>
        </ul>
    </li>
</ul>

<h2>3. Tidak Ada Pelacak Iklan atau Analitik Pihak Ketiga</h2>
<p>Website DPD JULEHA Kabupaten Brebes <strong>tidak menggunakan</strong> cookie pelacak iklan komersial, pixel media sosial, atau alat analitik perilaku pihak ketiga. Privasi dan kenyamanan Anda adalah prioritas kami.</p>

<h2>4. Mengelola Penyimpanan Peramban Anda</h2>
<p>Anda dapat mengatur, memblokir, atau menghapus cookie kapan saja melalui menu pengaturan pada peramban web Anda (Google Chrome, Mozilla Firefox, Safari, Microsoft Edge). Perlu diperhatikan bahwa menonaktifkan cookie sesi akan menyebabkan Anda tidak dapat masuk ke Portal Peserta.</p>
HTML;

        LegalDocument::updateOrCreate(
            ['slug' => LegalDocument::SLUG_PENYIMPANAN_PERANGKAT],
            [
                'title' => 'Penyimpanan di Perangkat (Cookie & Preferensi)',
                'version' => '1.0',
                'effective_date' => '2026-10-24',
                'content' => $cookieContent,
                'status' => LegalDocument::STATUS_DRAFT,
                'change_summary' => 'Rancangan awal dokumen penyimpanan di perangkat dan cookie.',
                'is_mandatory_consent' => false,
                'published_at' => null,
            ]
        );

        $disclaimerContent = <<<'HTML'
<h2>1. Catatan Verifikasi Sertifikat</h2>
<p>Layanan pencarian dan verifikasi sertifikat pada website ini menyajikan catatan verifikasi internal DPD JULEHA Kabupaten Brebes atas juru sembelih yang telah mengikuti pembinaan atau terdaftar di wilayah Kabupaten Brebes. Keabsahan yuridis dan masa berlaku sertifikat kompetensi profesi negara mengacu sepenuhnya pada Lembaga Sertifikasi Profesi (LSP) penerbit resmi dan Badan Nasional Sertifikasi Profesi (BNSP).</p>

<h2>2. Keakuratan dan Pembaruan Informasi</h2>
<p>DPD JULEHA Brebes berupaya menyajikan informasi jadwal pelatihan, profil bimbingan teknis, dan artikel fiqih sembelih secara akurat dan tepat waktu. Namun demikian, informasi dapat diperbarui, disesuaikan, atau disempurnakan sewaktu-waktu sesuai perkembangan teknis dan kebijakan organisasi.</p>

<h2>3. Sifat Materi Pelatihan &amp; Fiqih Sembelih</h2>
<p>Materi bimbingan teknis, video panduan, dan artikel yang dipublikasikan di situs ini ditujukan semata-mata untuk tujuan edukasi, literasi kehalalan, dan standardisasi keterampilan juru sembelih halal. Materi tersebut merujuk pada ketentuan fiqih syariat Islam, fatwa Majelis Ulama Indonesia (MUI), serta standar teknis kesehatan masyarakat veteriner (Kesmavet), dan bukan merupakan fatwa keagamaan perorangan.</p>

<h2>4. Tautan Menuju Situs Pihak Ketiga</h2>
<p>Website ini dapat menyertakan tautan menuju layanan atau situs eksternal (misalnya tautan pesan cepat WhatsApp, akun media sosial, atau rujukan pustaka). DPD JULEHA Brebes tidak memiliki kendali atas materi atau praktik privasi yang dijalankan oleh pihak eksternal tersebut.</p>
HTML;

        LegalDocument::updateOrCreate(
            ['slug' => LegalDocument::SLUG_DISCLAIMER],
            [
                'title' => 'Pernyataan Penyangkalan (Disclaimer)',
                'version' => '1.0',
                'effective_date' => '2026-10-24',
                'content' => $disclaimerContent,
                'status' => LegalDocument::STATUS_DRAFT,
                'change_summary' => 'Rancangan awal pernyataan penyangkalan (disclaimer).',
                'is_mandatory_consent' => false,
                'published_at' => null,
            ]
        );

        $copyrightContent = <<<'HTML'
<h2>1. Hak Cipta Konten dan Merek</h2>
<p>Seluruh materi publikasi resmi yang ada pada situs ini, meliputi teks pengumuman, modul bimbingan teknis, artikel edukasi, logo resmi DPD JULEHA Brebes, dan dokumentasi foto kegiatan daerah merupakan hak milik DPD JULEHA Kabupaten Brebes, kecuali dinyatakan lain.</p>
<p>Pengutipan atau penyebarluasan materi informasi publik diperbolehkan untuk kepentingan edukasi non-komersial dengan tetap mencantumkan sumber resmi: <em>DPD JULEHA Kabupaten Brebes (julehabrebes.org)</em>.</p>

<h2>2. Atribusi Desain dan Lisensi Template</h2>
<p>Pengembangan antarmuka website ini menggunakan rancangan tata letak dasar karya desainer dan penyedia template berikut yang kami hormati lisensi dan atribusinya:</p>
<ul>
    <li>Desain template berbasis karya <a href="https://www.templatemonster.com/authors/k29solutions/" target="_blank" rel="noopener">K29Solutions</a>.</li>
    <li>Didistribusikan melalui platform <a href="https://www.themewagon.com/" target="_blank" rel="noopener">ThemeWagon</a>.</li>
</ul>

<h2>3. Perangkat Lunak Sumber Terbuka (Open Source)</h2>
<p>Sistem portal ini dikembangkan menggunakan kerangka kerja dan pustaka sumber terbuka yang tunduk pada lisensinya masing-masing:</p>
<ul>
    <li>Framework: <strong>Laravel</strong> (Lisensi MIT)</li>
    <li>Antarmuka Panel: <strong>Filament</strong> (Lisensi MIT)</li>
    <li>Pustaka Ikon: <strong>Lucide Icons</strong> (Lisensi ISC)</li>
    <li>Kerangka Desain: <strong>Tailwind CSS</strong> (Lisensi MIT)</li>
</ul>
HTML;

        LegalDocument::updateOrCreate(
            ['slug' => LegalDocument::SLUG_HAK_CIPTA],
            [
                'title' => 'Hak Cipta dan Atribusi',
                'version' => '1.0',
                'effective_date' => '2026-10-24',
                'content' => $copyrightContent,
                'status' => LegalDocument::STATUS_DRAFT,
                'change_summary' => 'Rancangan awal hak cipta dan atribusi template.',
                'is_mandatory_consent' => false,
                'published_at' => null,
            ]
        );

        // 3. Backfill persetujuan awal ke tabel consents untuk peserta yang telah consent = true
        $profiles = ParticipantProfile::where('consent', true)->get();
        foreach ($profiles as $profile) {
            $userId = $profile->user_id;
            $version = $profile->consent_version ?: '1.0';
            $grantedAt = $profile->consent_at ?: $profile->updated_at ?: now();

            foreach (Consent::MANDATORY_PURPOSES as $purpose) {
                Consent::updateOrCreate(
                    ['user_id' => $userId, 'purpose' => $purpose],
                    [
                        'document_version' => $version,
                        'granted_at' => $grantedAt,
                        'withdrawn_at' => null,
                    ]
                );
            }
        }

        // 4. Inisialisasi pengaturan SLA hak data jika belum ada
        if (! Setting::get('privacy_request_sla_days')) {
            Setting::set('privacy_request_sla_days', '3');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep existing records or cleanup
    }
};
