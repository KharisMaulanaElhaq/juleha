@extends('layouts.app')

@section('title', 'Kebijakan Privasi & Perlindungan Data Pribadi (PDP) – DPD JULEHA Brebes')
@section('meta_description', 'Kebijakan privasi dan perlindungan data pribadi (NIK, akun Google, kontak) peserta dan pengunjung situs web DPD JULEHA Kabupaten Brebes sesuai UU PDP.')

@section('content')
<!-- ===================== HERO PRIVASI ===================== -->
<section class="relative py-16 sm:py-20" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Kepatuhan UU No. 27 Tahun 2022 (UU PDP)
        </span>
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-white mb-4">
            Kebijakan Privasi & Perlindungan Data Pribadi
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed">
            Komitmen DPD JULEHA Kabupaten Brebes dalam menjaga kerahasiaan, keamanan enkripsi data NIK, dan hak privasi peserta.
        </p>
    </div>
</section>

<!-- ===================== ISI KEBIJAKAN PRIVASI ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 text-sm sm:text-base leading-relaxed space-y-8">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    1. Data Pribadi yang Kami Kumpulkan
                </h2>
                <p class="mt-3">
                    Untuk menyelenggarakan layanan portal kepesertaan, bimbingan teknis (bimtek), dan sertifikasi juru sembelih halal, DPD JULEHA Kabupaten Brebes mengumpulkan jenis data berikut:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Data Akun Google OAuth:</strong> Alamat email, nama akun, foto profil avatar, dan Google User ID (dengan izin terbatas scope <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">openid email profile</code>) untuk verifikasi akun tunggal.</li>
                    <li><strong>Nomor Induk Kependudukan (NIK):</strong> Tepat 16 digit angka sesuai identitas KTP yang sah, wajib dilengkapi sebelum peserta dapat mendaftar kegiatan pelatihan.</li>
                    <li><strong>Nama Lengkap Sesuai KTP:</strong> Nama lengkap resmi untuk pencetakan sertifikat kompetensi.</li>
                    <li><strong>Nomor Kontak WhatsApp:</strong> Nomor telepon seluler aktif untuk koordinasi teknis, verifikasi pendaftaran, dan grup bimbingan.</li>
                    <li><strong>Domisili & Asal Instansi:</strong> Kecamatan, desa/kelurahan, dan instansi/masjid/RPH asal peserta di wilayah Kabupaten Brebes atau sekitarnya.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    2. Penggunaan Data dan Penerusan ke Lembaga Sertifikasi
                </h2>
                <p class="mt-3">
                    Data pribadi Anda dikumpulkan berdasarkan persetujuan yang Anda berikan saat melengkapi profil dan hanya digunakan untuk:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Penerbitan sertifikat bimbingan teknis resmi dari DPD JULEHA Brebes.</li>
                    <li>Penerusan data nama sesuai KTP, NIK, dan data kontak kepada lembaga sertifikasi resmi yaitu <strong>{{ setting('cert_issuer_name', 'LSP Pertanian RI') }}</strong> hanya untuk keperluan pendaftaran uji kompetensi dan penerbitan sertifikat.</li>
                    <li>Pencatatan hasil sertifikasi oleh DPD JULEHA Brebes untuk keperluan verifikasi keabsahan juru sembelih di wilayah Kabupaten Brebes.</li>
                </ul>
                <div class="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-xl border border-amber-200 dark:border-amber-800/50 text-xs text-amber-900 dark:text-amber-300 mt-3">
                    <strong>Pemberitahuan:</strong> DPD JULEHA Brebes memvalidasi format NIK 16 angka demi ketepatan pencetakan sertifikat dan tidak menyalahgunakan data Anda untuk keperluan di luar sertifikasi.
                </div>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    3. Standar Keamanan & Enkripsi Data Sensitif
                </h2>
                <p class="mt-3">
                    Kami memperlakukan NIK sebagai data pribadi sensitif dengan menerapkan pengamanan berlapis:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Penyimpanan Terenkripsi:</strong> NIK Anda disimpan dalam kondisi terenkripsi di sistem basis data kami dan tidak dapat dibaca oleh pihak yang tidak berwenang.</li>
                    <li><strong>Pencegahan Akun Ganda:</strong> Sistem menggunakan kode acak aman untuk memastikan satu NIK hanya dapat didaftarkan satu kali, tanpa membuka identitas mentah ke publik.</li>
                    <li><strong>Penyamaran Tampilan:</strong> Nomor NIK Anda selalu disamarkan (misalnya: <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">3329********1234</code>) pada layar dan tanda bukti keikutsertaan.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    4. Pihak yang Menerima Data Anda
                </h2>
                <p class="mt-3">
                    Kami menjaga amanah data Anda dengan sangat hati-hati:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Pengurus DPD JULEHA Brebes:</strong> Petugas administrasi resmi yang bertugas memverifikasi kelayakan peserta dan mengurus administrasi pelatihan.</li>
                    <li><strong>Lembaga Sertifikasi:</strong> Data nama sesuai KTP, NIK, dan kontak diteruskan kepada <strong>{{ setting('cert_issuer_name', 'LSP Pertanian RI') }}</strong> semata-mata untuk proses uji kompetensi dan penerbitan sertifikat profesi resmi.</li>
                    <li><strong>Tidak Dijual ke Pihak Ketiga:</strong> DPD JULEHA Kabupaten Brebes tidak pernah dan tidak akan menjual, menyewakan, atau membagikan data Anda kepada pihak lain untuk tujuan iklan atau komersial.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    5. Hak Peserta Atas Data Pribadi
                </h2>
                <p class="mt-3">
                    Setiap peserta memiliki kendali penuh atas data miliknya dengan hak-hak sebagai berikut:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Hak Akses:</strong> Anda berhak melihat data pribadi Anda kapan saja melalui halaman Profil Peserta di portal ini.</li>
                    <li><strong>Hak Koreksi:</strong> Anda berhak memperbaiki atau memperbarui nama, nomor kontak, maupun alamat jika ada kesalahan sebelum sertifikat resmi diterbitkan.</li>
                    <li><strong>Hak Penghapusan & Anonimisasi:</strong> Anda berhak meminta penghapusan akun dan data pribadi dari sistem kami sesuai UU Perlindungan Data Pribadi (UU PDP).</li>
                </ul>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    6. Kebijakan Penghapusan Data (Anonimisasi) & Masa Retensi Sertifikat
                </h2>
                <div class="mt-3 space-y-3 text-slate-700 dark:text-slate-300 leading-relaxed text-sm sm:text-base">
                    <p>
                        Bagi peserta yang telah menyelesaikan uji kompetensi dan memiliki sertifikat resmi, DPD JULEHA Kabupaten Brebes menerapkan kebijakan kepatuhan UU PDP sebagai berikut:
                    </p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>
                            <strong>Apa yang Dihapus:</strong> Saat permohonan penghapusan data diproses, seluruh data identitas pribadi Anda (Nama lengkap KTP, NIK terenkripsi, nomor WhatsApp, alamat domisili, email) serta berkas fisik PDF sertifikat di server privat akan <em>dihapus secara permanen</em>.
                        </li>
                        <li>
                            <strong>Apa yang Tetap Disimpan & Alasannya:</strong> Catatan teknis minimal sertifikat (Nomor Sertifikat, Skema Sertifikasi, Lembaga Penerbit, Tanggal Terbit, Masa Berlaku, dan Kode Verifikasi Publik) tetap disimpan dalam basis data. Hal ini diwajibkan demi integritas sertifikasi profesi nasional, mencegah sertifikat palsu, dan memungkinkan pihak ketiga (seperti RPH, dinas terkait, atau takmir masjid) memverifikasi bahwa sertifikat tersebut pernah sah diterbitkan.
                        </li>
                        <li>
                            <strong>Tampilan Tanpa Identitas:</strong> Sertifikat milik akun yang telah dihapus datanya berstatus <em>"Data Pemegang Dihapus"</em> (bukan dicabut). Pada halaman verifikasi publik, nama pemegang ditampilkan sebagai teks <strong>"Data pemegang telah dihapus"</strong> tanpa membocorkan identitas apa pun.
                        </li>
                        <li>
                            <strong>Masa Retensi:</strong> Catatan teknis minimal dipertahankan selama sertifikat aktif ditambah masa retensi yang ditetapkan oleh Pengurus DPD bersama LSP penerbit (kebijakan retensi diputuskan bersama LSP). Setelah melewati batas retensi tersebut, catatan minimal akan dihapus permanen secara otomatis oleh sistem.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/60 p-6 rounded-2xl border border-slate-200 dark:border-slate-700">
                <h3 class="font-heading font-bold text-base text-slate-800 dark:text-white mb-2">
                    Kontak Pengaduan & Layanan Sekretariat
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Untuk pertanyaan seputar kebijakan privasi, permohonan pembaruan data, atau penarikan persetujuan, silakan hubungi:
                </p>
                <div class="mt-3 space-y-1 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                    <p><strong>Sekretariat DPD JULEHA Kabupaten Brebes</strong></p>
                    <p>Alamat: {{ setting('office_address', 'Jalan Kaliluwih, Wanasari, Kabupaten Brebes, Jawa Tengah 52252') }}</p>
                    <p>WhatsApp: {{ setting_wa_display('whatsapp_number') }}</p>
                    <p>Email: {{ setting('admin_email', 'dpdjulehabrebesofficial@gmail.com') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
