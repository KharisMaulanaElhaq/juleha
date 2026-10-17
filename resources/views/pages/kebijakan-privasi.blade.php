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
                    2. Dasar & Tujuan Pengumpulan NIK
                </h2>
                <p class="mt-3">
                    Pengumpulan Nomor Induk Kependudukan (NIK) dilakukan dengan dasar persetujuan eksplisit (consent) dari peserta dan digunakan semata-mata untuk:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Penerbitan <strong>Sertifikat Bimbingan Teknis</strong> dan pelaporan administrasi ke instansi terkait.</li>
                    <li>Fasilitasi pendaftaran asesmen dan penerbitan sertifikat pada <strong>Lembaga Sertifikasi Profesi (LSP) / BNSP</strong>.</li>
                    <li>Mencegah duplikasi data kepesertaan ganda dalam satu angkatan kegiatan.</li>
                </ul>
                <div class="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-xl border border-amber-200 dark:border-amber-800/50 text-xs text-amber-900 dark:text-amber-300 mt-3">
                    <strong>Pemberitahuan Validasi:</strong> Sistem DPD JULEHA Brebes memvalidasi format matematis 16 digit NIK dan struktur tanggal lahir secara lokal. Sistem kami <em>tidak mengklaim dan tidak melakukan verifikasi langsung ke server basis data kependudukan Dukcapil</em>.
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
                    <li><strong>Enkripsi Tingkat Database:</strong> Nilai NIK disimpan terenkripsi di database menggunakan algoritma kriptografi AES-256. NIK mentah tidak tersimpan dalam bentuk teks polos.</li>
                    <li><strong>HMAC-SHA256 untuk Pencarian & Anti-Duplikasi:</strong> Pencegahan NIK ganda menggunakan digest hash HMAC-SHA256 dengan kunci rahasia terpisah (<code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">NIK_HASH_KEY</code>), bukan menyimpan NIK mentah.</li>
                    <li><strong>Penyamaran (Masking):</strong> Di seluruh tampilan publik dan dashboard peserta, NIK ditampilkan secara tersamar (misal: <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">3329********1234</code>).</li>
                    <li><strong>Pengecualian Serialisasi & Log:</strong> NIK disembunyikan secara permanen dari serialisasi JSON/API, log aplikasi, laporan error sistem, dan notifikasi email.</li>
                    <li><strong>Pencatatan Audit (Audit Trail):</strong> Setiap aksi pembukaan NIK lengkap oleh administrator di panel admin dicatat secara otomatis dalam tabel log audit (mencakup waktu, identitas administrator, target peserta, dan alamat IP).</li>
                </ul>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    4. Pihak yang Memiliki Akses ke Data
                </h2>
                <p class="mt-3">
                    Akses terhadap data peserta diatur secara ketat berdasarkan prinsip kebutuhan wewenang:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Super Admin & Admin Kegiatan DPD:</strong> Berhak meninjau pendaftaran dan membuka NIK demi pencetakan sertifikat (setiap pembukaan diaudit).</li>
                    <li><strong>Editor Konten:</strong> <em>Dilarang keras</em> dan tidak memiliki hak akses melihat pendaftaran peserta maupun NIK.</li>
                    <li><strong>Pihak Ketiga:</strong> DPD JULEHA Kabupaten Brebes <strong>tidak pernah</strong> menjual, menyewakan, atau memberikan data pribadi peserta kepada pihak komersial pihak ketiga manapun. Data hanya diteruskan ke LSP mitra terlisensi jika peserta mendaftar uji kompetensi sertifikasi resmi.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    5. Hak Peserta & Prosedur Penghapusan Data
                </h2>
                <p class="mt-3">
                    Sesuai dengan ketentuan Undang-Undang Perlindungan Data Pribadi (UU PDP), peserta memiliki hak untuk:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Melihat dan memperbarui profil kontak miliknya secara mandiri melalui menu <strong>Kelola Profil</strong> di dashboard.</li>
                    <li>Mengajukan permohonan koreksi NIK apabila terjadi kesalahan pengetikan sebelum sertifikat resmi diterbitkan.</li>
                    <li>Meminta penghapusan akun dan data pribadi dari basis data aktif dengan mengajukan permohonan tertulis kepada sekretariat DPD JULEHA Brebes melalui kontak resmi di bawah ini.</li>
                </ul>
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
