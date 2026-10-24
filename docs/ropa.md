# Catatan Aktivitas Pemrosesan Data Pribadi (ROPA)
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

> **STATUS DOKUMEN: RANCANGAN INTERNAL**  
> *Dokumen ini merupakan rancangan teknis kepatuhan berdasarkan Pasal 31 Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP). Dokumen ini wajib ditinjau dan disahkan secara resmi oleh Pengurus DPD JULEHA Kabupaten Brebes bersama penasihat hukum organisasi.*

---

## 1. Identitas Pengendali Data Pribadi

| Komponen | Keterangan / Nilai Sistem | Status Verifikasi |
|---|---|---|
| **Nama Organisasi** | Dewan Pengurus Daerah Juru Sembelih Halal (DPD JULEHA) Kabupaten Brebes | Sesuai Pengaturan Situs |
| **Status Badan Hukum** | Perkumpulan Berbadan Hukum / [DITETAPKAN PENGURUS] | Menunggu Konfirmasi SK Kemenkumham |
| **Nomor SK Organisasi** | Mengacu pada pengaturan `legal_sk_number` (Default: `[DITETAPKAN PENGURUS]`) | Pengaturan Situs |
| **Alamat Sekretariat** | Mengacu pada pengaturan `org_address` | Sesuai Pengaturan Situs |
| **Kontak Resmi & Email** | Mengacu pada pengaturan `org_email` dan `privacy_contact_email` | Sesuai Pengaturan Situs |
| **Pejabat / PIC Privasi** | Mengacu pada pengaturan `privacy_officer_name` (Default: `[DITETAPKAN PENGURUS]`) | Menunggu Penunjukan Resmi Pengurus |

---

## 2. Inventarisasi Aktivitas Pemrosesan Data (Record of Processing)

Sistem informasi website DPD JULEHA Kabupaten Brebes menyelenggarakan 5 (lima) alur pemrosesan data utama:

### Aktivitas 1: Pendaftaran Akun Peserta & Autentikasi Google OAuth
- **Tujuan Pemrosesan**: Memfasilitasi pendaftaran mandiri peserta, login aman tanpa kata sandi terpisah, dan verifikasi alamat email peserta.
- **Kategori Subjek Data**: Peserta pelatihan, calon juru sembelih, pengurus masjid/DKM di Brebes.
- **Kategori Data Pribadi**:
  - Nama Lengkap (dari Google Account).
  - Alamat Email (dari Google Account).
  - Google ID & Foto Avatar (dari Google Account).
  - IP Address dan User-Agent saat login/aktivitas.
- **Dasar Hukum (Lawful Basis)**: Persetujuan Subjek Data (`Consent::PURPOSE_ACCOUNT_AND_ACTIVITY`) dan pemenuhan kewajiban administratif pembuatan akun portal.
- **Penerima Pihak Ketiga**: Google LLC (layanan Google OAuth API).
- **Masa Simpan Data**: Selama akun peserta aktif, atau hingga permohonan penghapusan akun disetujui.

### Aktivitas 2: Kelengkapan Profil & Pendaftaran Kegiatan Bimtek
- **Tujuan Pemrosesan**: Verifikasi kelayakan peserta, alokasi kuota, pengelompokan wilayah penugasan di 17 kecamatan Brebes, dan koordinasi lapangan.
- **Kategori Subjek Data**: Peserta terdaftar pada agenda Bimbingan Teknis (Bimtek).
- **Kategori Data Pribadi**:
  - Nomor Induk Kependudukan (NIK - 16 digit).
  - Nama Lengkap sesuai KTP.
  - Nomor WhatsApp.
  - Asal Instansi/Masjid/DKM.
  - Kecamatan dan Desa/Kelurahan domisili di Kabupaten Brebes.
  - Pengalaman sembelih dan profesi.
- **Pengamanan Teknis Khusus**:
  - Kolom `nik` dienkripsi dua arah menggunakan kunci `APP_KEY` (AES-256-CBC/GCM).
  - Kolom `nik_hash` menggunakan blind index HMAC-SHA256 (`NIK_HASH_KEY`) untuk mencegah tabrakan data tanpa membaca isi NIK.
  - Akses staf Filament dibatasi RBAC dan setiap pembukaan tampilan NIK wajib memasukkan alasan serta tercatat di `audit_logs`.
- **Dasar Hukum**: Persetujuan eksplisit per tujuan saat melengkapi profil dan saat mendaftar kegiatan.
- **Masa Simpan Data**: Selama masa operasional pelatihan hingga batas retensi arsip alumni [DITETAPKAN PENGURUS].

### Aktivitas 3: Pendaftaran Uji Sertifikasi Kompetensi (LSP / BNSP)
- **Tujuan Pemrosesan**: Memfasilitasi penerusan data peserta uji kompetensi ke Lembaga Sertifikasi Profesi pihak ketiga.
- **Kategori Data Pribadi**:
  - NIK, Nama Lengkap KTP, Nomor WhatsApp, Kecamatan/Desa, Asal Lembaga, serta Riwayat Pelatihan Juleha.
- **Dasar Hukum**: Persetujuan eksplisit terpisah (`Consent::PURPOSE_SHARE_TO_LSP` dan `Consent::PURPOSE_NIK_CERTIFICATE`).
- **Penerima Pihak Ketiga**: Lembaga Sertifikasi Profesi (LSP) mitra resmi BNSP (sesuai pengaturan `cert_issuer_name`).
- **Masa Simpan Data**: Selama proses pendaftaran uji sertifikasi dan audit arsip kompetensi.

### Aktivitas 4: Penerbitan dan Verifikasi Sertifikat Resmi DPD
- **Tujuan Pemrosesan**: Menerbitkan berkas sertifikat digital (PDF) bertanda tangan elektronik/QR code dan menyediakan portal verifikasi keaslian publik.
- **Kategori Data Pribadi**:
  - Nomor sertifikat, kode acak verifikasi publik (`public_code`), nama peserta, jenis bimtek, tanggal terbit, dan tautan berkas privat.
- **Keterbukaan Data di Halaman Verifikasi Publik**:
  - Halaman `/verifikasi/{code}` HANYA menampilkan: Nama Peserta, Judul Pelatihan, Tanggal Terbit, dan Status Keabsahan.
  - NIK dan Nomor Kontak **TIDAK PERNAH** ditampilkan di halaman verifikasi publik.
- **Penyimpanan Berkas**: Berkas fisik PDF disimpan di private disk storage Laravel (`storage/app/private/certificates/`), tidak dapat diakses langsung via URL web publik tanpa token/autentikasi terotorisasi.

### Aktivitas 5: Penanganan Permohonan Hak Subjek Data
- **Tujuan Pemrosesan**: Melayani hak akses, salinan data (JSON), pembaruan data, penarikan persetujuan, serta penghapusan/anonimisasi akun sesuai amanat UU PDP.
- **Kategori Data Pribadi**:
  - Nomor Registrasi Permohonan (`REQ-YYYYMMDD-XXXX`), Nama Pemohon, Email, Nomor WhatsApp, Jenis Hak yang Dituntut, Alasan Permohonan, dan Catatan Penyelesaian.
- **Dasar Hukum**: Kewajiban hukum Pengendali Data berdasarkan Bab VI UU No. 27 Tahun 2022.
- **Masa Simpan Data**: Minimal 2 (dua) tahun setelah penyelesaian permohonan untuk keperluan audit kepatuhan organisasi.

---

## 3. Matriks Pihak Ketiga dan Pemroses Data (Processors)

| Nama Pemroses | Peran / Layanan | Lokasi Server / Yurisdiksi | Instrumen Kepatuhan |
|---|---|---|---|
| **Google LLC** | Google OAuth 2.0 (Autentikasi Login) | Global / Amerika Serikat & Mitra | Google Cloud Privacy Policy & API Terms |
| **LSP Mitra BNSP** | Lembaga Sertifikasi Profesi Pihak Ketiga | Indonesia | Perjanjian Kerjasama (PKS) & DPA [DITETAPKAN PENGURUS] |
| **Penyedia Layanan Hosting/Cloud** | Server Web, Database MySQL, Penyimpanan Berkas | [DITETAPKAN PENGURUS - Misal: Datacenter Indonesia] | Perjanjian Layanan Hosting & Dokumen Keamanan ISO 27001 Penyedia |
| **Penyedia SMTP / Mail Server** | Pengiriman Email Transaksional (Notifikasi Akun & Hak Data) | [DITETAPKAN PENGURUS - Sesuai config mail] | Kebijakan Privasi Penyedia Layanan Mail |

---

## 4. Langkah Pengamanan Teknis & Organisasional (TOMs)

1. **Enkripsi Data At-Rest**:
   - Kolom sensitif NIK dienkripsi AES-256 pada database.
   - Hash blind index menggunakan HMAC-SHA256 dengan secret key terpisah dari aplikasi.
2. **Enkripsi Data In-Transit**:
   - Komunikasi seluruh halaman wajib melalui HTTPS (Enforced TLS).
   - Penggunaan header keamanan HTTP (`Strict-Transport-Security`, `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`).
3. **Penyimpanan Aset Font & Icon Mandiri (Self-Hosted)**:
   - Pustaka icon Lucide di-self-host lokal pada `/assets/lucide.min.js`, mencegah kebocoran IP peserta ke CDN pihak ketiga publik saat bernavigasi.
4. **Pencegahan Kebocoran URL & Tautan Eksternal**:
   - Tautan WhatsApp narahubung/admin tidak memuat parameter prefilled NIK, email, atau nomor HP peserta.
5. **Hak Akses & Jejak Audit (Audit Trail)**:
   - Akses administratif dibatasi dengan Role-Based Access Control (RBAC).
   - Setiap tindakan membuka NIK lengkap dicatat otomatis di `audit_logs` dengan IP address, user-agent, ID aktor, dan timestamp.
6. **Mekanisme Anonimisasi Data**:
   - Fitur penghapusan akun peserta mengonversi nama menjadi `'Peserta Dianonimkan'`, menghapus email, NIK, WhatsApp, alamat, dan menghapus berkas fisik sertifikat privat, sehingga identitas subjek tidak dapat dipulihkan kembali.

---

*Catatan: Dokumen ROPA ini wajib ditinjau ulang secara berkala setiap tahun atau setiap kali terdapat penambahan modul/alur pemrosesan baru pada website DPD JULEHA Kabupaten Brebes.*
