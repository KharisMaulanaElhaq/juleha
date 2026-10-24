# Kebijakan & Tata Kelola Keamanan Data Pribadi (UU PDP)
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

Dokumen ini memuat standar operasional prosedur (SOP) pengelolaan, perlindungan teknis, enkripsi, hak subjek data, serta tanggap darurat data pribadi sesuai Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).

---

## 1. Data yang Dikumpulkan & Tujuan Pemrosesan

| Kategori Data | Jenis Data | Dasar & Alasan Pengumpulan | Masa Retensi |
|---|---|---|---|
| **Data Akun** | Nama lengkap, Alamat Email, Google ID, Foto Profil (Avatar) | Autentikasi sistem melalui Google OAuth, identifikasi akun peserta portal, verifikasi email, dan pengiriman notifikasi pendaftaran/jadwal. | Selama akun aktif atau hingga permintaan penghapusan akun. |
| **Data Identitas & Sertifikasi** | NIK (Nomor Induk Kependudukan - 16 digit), Nama Lengkap sesuai KTP | Syarat mutlak penerbitan sertifikat pelatihan/Bimtek resmi DPD JULEHA Brebes dan kelengkapan berkas uji kompetensi sertifikasi BNSP. | Disimpan untuk validitas verifikasi keabsahan sertifikat alumni juleha. |
| **Data Kontak & Domisili** | Nomor WhatsApp (format internasional `628...`), Kecamatan (17 kecamatan Kab. Brebes), Desa/Kelurahan, Asal Instansi/Lembaga/Masjid | Koordinasi pelaksanaan kegiatan lapangan, verifikasi domisili penugasan juru sembelih halal di Brebes, dan korespondensi admin. | Selama menjadi anggota/peserta aktif. |
| **Data Keanggotaan/Profesi** | Status profesi (Calon Juleha, Juleha Aktif, Pengurus Masjid/DKM, Pekerja RPH) | Pemetaan kompetensi dan penempatan kelas bimtek/sertifikasi yang relevan. | Selama akun aktif. |
| **Log Aktivitas & Teknis** | Waktu akses, IP Address, User-Agent, Catatan audit buka NIK | Keamanan sistem (audit trail pencegahan penyalahgunaan wewenang admin) dan kepatuhan hukum. | Minimal 12 bulan di tabel `audit_logs`. |

> **Prinsip Minimalisasi Data**: Data tempat dan tanggal lahir tidak dikumpulkan pada profil standar. Jika di masa mendatang dibutuhkan untuk penerbitan sertifikat LSP/BNSP, penambahan kolom hanya dilakukan atas persetujuan pemilik data.

---

## 2. Hak Akses Berjenjang (Role-Based Access Control)

Hak akses data peserta diatur secara ketat berdasarkan matriks peran (Role):

| Peran (Role) | Akses Data Profil Umum | Akses NIK Lengkap | Akses Kelola Pendaftaran | Akses Kelola Konten Web |
|---|---|---|---|---|
| **Peserta (`participant`)** | Hanya data profil miliknya sendiri | Hanya NIK miliknya dalam kondisi **tersamar** (`3329********1234`). | Hanya pendaftaran miliknya | Tidak dapat masuk `/admin` |
| **Editor Konten (`editor_konten`)** | DILARANG | DILARANG | DILARANG | Berita, Galeri, Agenda, FAQ, Program |
| **Admin Kegiatan (`admin_kegiatan`)** | Membaca & memverifikasi peserta kegiatan | NIK tersamar secara default. Tombol "Buka NIK Lengkap" dengan modal & **wajib audit log**. | Mengonfirmasi, menolak, memantau kehadiran | DILARANG |
| **Super Admin (`super_admin`)** | Akses penuh operasional | NIK tersamar secara default. Akses buka NIK tercatat audit log. Ekspor CSV ber-NIK tercatat audit log. | Penuh | Penuh |

---

## 3. Arsitektur Enkripsi & Hashing NIK

Untuk melindungi NIK dari kebocoran basis data (*data breach*), sistem menerapkan pendekatan keamanan berlapis:

### A. Enkripsi Dua Arah (`nik`)
- Disimpan dalam kolom bertipe `TEXT` di tabel `participant_profiles`.
- Menggunakan fitur bawaan Laravel Eloquent Cast: `'nik' => 'encrypted'`.
- Algoritma: **AES-256-CBC** atau **AES-256-GCM** yang diikat dengan `APP_KEY`.
- NIK didekripsi hanya di memori aplikasi saat dibutuhkan (misalnya pencetakan sertifikat resmi). Di database mentah, data berupa cipher text biner/Base64 acak.

### B. Blind Index / Hashing (`nik_hash`)
- Pencarian dan pencegahan duplikasi NIK dilakukan tanpa pernah mendekripsi data satu per satu.
- Kolom `nik_hash` berupa string 64 karakter heksadesimal dengan indeks unik (`UNIQUE KEY`).
- Algoritma: **HMAC-SHA256** menggunakan kunci rahasia terpisah: `NIK_HASH_KEY`.
- Rumus: `hash_hmac('sha256', $nik, env('NIK_HASH_KEY'))`.
- Dengan kunci rahasia terpisah dari `APP_KEY`, penyerang yang hanya memiliki salah satu kunci tidak dapat memetakan NIK peserta.

### C. Proteksi Serialisasi & Notifikasi
- Kolom `nik` dan `nik_hash` didaftarkan dalam array `$hidden` pada model `ParticipantProfile`.
- NIK **TIDAK PERNAH** dimunculkan dalam:
  1. Serialisasi JSON / REST API.
  2. Notifikasi email antrean pendaftaran ke admin maupun peserta.
  3. URL query parameters.
  4. File log aplikasi Laravel (`storage/logs/laravel.log`).
  5. Ekspor CSV standar pendaftaran di Filament.

---

## 4. Prosedur Rotasi Kunci (`APP_KEY` & `NIK_HASH_KEY`)

Rotasi kunci kriptografi wajib dilakukan secara berkala (minimal 1 tahun sekali) atau segera setelah dicurigai adanya insiden kebocoran kunci lingkungan (`.env`).

### A. Prosedur Rotasi `NIK_HASH_KEY`
Karena `nik_hash` dihasilkan secara deterministik dari `nik`:
1. Buat backup basis data lengkap terlebih dahulu.
2. Buat kunci baru, misalnya `NIK_HASH_KEY_NEW`.
3. Jalankan script/artisan command transisi untuk membaca setiap profil peserta:
   - Dekripsi `nik` peserta menggunakan `APP_KEY`.
   - Hitung hash baru: `hash_hmac('sha256', $nik, $newKey)`.
   - Simpan hash baru ke tabel sementara atau perbarui `nik_hash`.
4. Perbarui nilai `NIK_HASH_KEY` di file `.env` produksi dengan kunci baru.
5. Hapus cache konfigurasi: `php artisan config:cache`.

### B. Prosedur Rotasi `APP_KEY`
Laravel menyediakan mekanisme migrasi cipher text jika melakukan rotasi `APP_KEY`:
1. Cadangkan database dan file `.env` lama.
2. Tempatkan aplikasi dalam mode perawatan: `php artisan down`.
3. Dekripsi seluruh kolom `nik` yang ada dengan key lama, lalu enkripsi ulang menggunakan key baru (dapat memanfaatkan command khusus atau paket `laravel-rehash-encrypted-attributes`).
4. Perbarui `APP_KEY` pada file `.env`.
5. Bersihkan cache: `php artisan config:clear && php artisan config:cache`.
6. Kembalikan aplikasi ke status online: `php artisan up`.

---

## 5. Prosedur Backup Basis Data Terenkripsi

Backup basis data tidak boleh disimpan dalam format teks SQL mentah tanpa enkripsi.

### A. SOP Backup Harian
1. Ekspor dump basis data MySQL:
   ```bash
   mysqldump -u [db_user] -p [db_name] > backup_juleha_brebes_raw.sql
   ```
2. Kompresi dan enkripsi menggunakan GPG atau OpenSSL AES-256:
   ```bash
   openssl enc -aes-256-cbc -salt -pbkdf2 -in backup_juleha_brebes_raw.sql -out backup_$(date +%Y%m%d).sql.enc -pass pass:[PASSPHRASE_BACKUP_TERPISAH]
   ```
3. Hapus file mentah `backup_juleha_brebes_raw.sql` dengan aman (`shred` atau `rm`).
4. Simpan file `.enc` pada penyimpanan *off-site* (misal: S3 / Google Drive khusus arsip terproteksi 2FA).

### B. Uji Pemulihan (Disaster Recovery Drill)
Lakukan simulasi dekripsi dan restorasi backup setidaknya setiap 6 bulan sekali pada lingkungan staging lokal terisolasi.

---

## 6. Prosedur Penghapusan & Hak Subjek Data (*Right to be Forgotten*)

Berdasarkan Pasal 39 UU PDP, subjek data (peserta) berhak meminta pembaruan, perbaikan, atau penghapusan data pribadi miliknya.

### A. Mekanisme Permohonan
1. Peserta mengajukan permohonan tertulis melalui email resmi DPD JULEHA Brebes (`dpdjulehabrebesofficial@gmail.com`) atau melalui form pengaduan sekretariat.
2. Petugas memverifikasi identitas pemohon dengan mencocokkan email Google dan nomor WhatsApp terdaftar.

### B. Ketentuan Penghapusan & Anonimisasi Akun (Kebijakan Terpilih: Opsi A)
- **Akun Belum Memiliki Riwayat/Sertifikat**: Data profil (`participant_profiles`) dan akun pengguna (`users`) dapat dihapus (*soft delete* atau *hard delete* jika tanpa riwayat).
- **Akun Telah Memiliki Sertifikat Resmi (Uji Kompetensi LSP)**:
  - **Larangan Hapus Permanen**: Akun yang memiliki catatan sertifikat **TIDAK BOLEH dihapus permanen (*force delete*)**, melainkan hanya dapat dianonimkan (*anonymize*).
  - **Penghapusan Berkas Digital**: Berkas fisik sertifikat PDF milik peserta di penyimpanan privat (*private storage*) **dihapus secara permanen** demi menjaga privasi dokumen peserta.
  - **Retensi Catatan Minimal Verifikasi**: Catatan data teknis sertifikat di basis data (`certificate_number`, `scheme_name`, `issuer_name`, `issued_at`, `valid_until`, `public_code`) **tetap dipertahankan** demi integritas verifikasi pihak ketiga (seperti Rumah Pemotongan Hewan, dinas pemerintah, atau masjid yang mengecek keabsahan nomor sertifikat yang pernah diterbitkan).
  - **Anonimisasi Identitas Pemegang**: Nama peserta diganti dengan format teranonimkan (`Pengguna Dihapus #[ID]`), NIK, NIK hash, WhatsApp, email, dan alamat domisili dihapus permanen. Kolom `holder_data_erased_at` dicatat dengan waktu saat ini.
  - **Status Terpisah dari Dicabut**: Sertifikat milik akun yang dianonimkan berstatus **"Data Pemegang Dihapus"** dan **TIDAK ditandai Dicabut**, karena penghapusan data privasi bukan merupakan sanksi pencabutan profesi. Pada halaman verifikasi publik, nama pemegang tampil sebagai teks **"Data pemegang telah dihapus"**.
  - **Masa Retensi Catatan Minimal**: Catatan teknis sertifikat dipertahankan selama masa berlaku aktif ditambah masa retensi yang dikonfigurasi pada pengaturan situs `cert_record_retention_years`. Selama pengaturan ini kosong (belum ditetapkan), sistem tidak menghapus catatan apa pun secara otomatis. Nilai masa retensi ini diputuskan oleh Pengurus DPD bersama LSP penerbit. Apabila terisi, scheduled command `certificates:prune-expired-records` akan membersihkan catatan teknis setelah melewati batas `valid_until + cert_record_retention_years`.
- Log audit aksi dianonimkan dicatat di `audit_logs` dengan aksi `anonymized`.

---

## 7. Tata Kelola Data Sertifikat & Transfer Data ke LSP

### A. Alur Data ke Lembaga Sertifikasi Profesi (LSP)
1. DPD JULEHA Kabupaten Brebes bertindak sebagai penyelenggara/fasilitator uji kompetensi. Sertifikat digital diterbitkan oleh LSP berlisensi BNSP (default: **LSP Pertanian RI**).
2. **Ekspor Data Peserta untuk LSP**:
   - Hanya dapat dilakukan oleh **Super Admin** (`super_admin`) melalui Relation Manager pada kegiatan jenis `sertifikasi_kompetensi`.
   - Wajib melalui dialog konfirmasi eksplisit yang mengingatkan bahwa data sensitif (termasuk NIK lengkap, Nama sesuai KTP, Kontak WhatsApp, Email, dan Domisili) dibagikan kepada LSP penerbit (`{cert_issuer_name}`).
   - Seluruh aktivitas ekspor dicatat secara permanen di `audit_logs` dengan aksi `exported_for_lsp` tanpa mencantumkan nilai NIK.
   - File ekspor dikirimkan sebagai stream sekali pakai (*memory stream download*) bertanda UTF-8 BOM, **tidak disimpan di penyimpanan server** setelah diunduh.
   - Kolom yang dihasilkan: No, Nama Sesuai KTP, NIK, Nomor WhatsApp, Email, Kecamatan, Desa/Kelurahan.
   - Seluruh sel teks dinetralkan dari risiko *CSV Formula Injection* (awalan tanda petik tunggal untuk karakter `=`, `+`, `-`, `@`, tab, atau carriage return).

### B. Penyimpanan & Akses Berkas Sertifikat Digital
1. Sistem **tidak membuat atau mencetak sertifikat BNSP sendiri**. Sistem hanya menyimpan berkas PDF resmi yang diterbitkan oleh LSP.
2. Berkas sertifikat PDF disimpan pada disk penyimpanan **PRIVAT** (`storage/app/private/certificates`), bukan folder publik yang dapat ditebak.
3. Akses unduh berkas dikontrol secara ketat:
   - Peserta hanya dapat mengunduh sertifikat miliknya sendiri jika berstatus **Kompeten** dan **tidak dicabut**.
   - Admin Kegiatan & Super Admin dapat mengunduh berkas melalui panel admin, dengan setiap unduhan tercatat di log audit (`downloaded_by_admin`).
   - Respon unduhan wajib memakai header: `Content-Type: application/pdf`, `Content-Disposition: attachment`, `X-Content-Type-Options: nosniff`, dan `Cache-Control: no-store, private, no-cache, must-revalidate`.
4. Validasi berkas saat unggah:
   - Format wajib PDF asli, ukuran maksimal 5 MB.
   - Pengecekan *Magic Bytes* (`%PDF-` pada header berkas) untuk menolak berkas palsu yang disamarkan.
   - Perhitungan dan pencatatan *hash* kriptografi **SHA-256** untuk menjamin integritas berkas.

### C. Verifikasi Publik Tanpa Paparan Data Pribadi
1. Publik dapat memeriksa keabsahan catatan sertifikat DPD melalui `/verifikasi` (berdasarkan nomor sertifikat) atau tautan acak `/verifikasi/{public_code}` (minimal 12 karakter acak yang dibuat secara kriptografis aman melalui `Str::random`).
2. **Pencarian Melalui Nomor Sertifikat**:
   - Menampilkan hasil keabsahan catatan teknis (Status, Skema Kompetensi, Lembaga Penerbit, Nomor Sertifikat, Tanggal Terbit, Masa Berlaku) **TANPA menampilkan nama pemegang**.
   - Nama pemegang (tersamar) hanya dapat diverifikasi melalui tautan langsung kode unik `public_code` yang tertera pada sertifikat fisik/digital.
3. **Penanganan Sertifikat Dicabut**:
   - Tampilkan status **"Dicabut"** secara jelas dan tegas.
   - **Tanpa menampilkan nama pemegang** dan **tanpa menampilkan alasan internal pencabutan** kepada publik.
4. **Pencegahan Enumerasi & Brute-Force**:
   - Sertifikat dengan hasil `belum_kompeten` **tidak pernah tampil** di verifikasi publik.
   - Pesan "tidak ditemukan" diseragamkan untuk nomor salah maupun tidak terdaftar.
   - Dilengkapi *rate limiting* ketat per IP (`throttle:15,1`) serta penundaan waktu (*small delay*) dan pembatasan lebih ketat setelah beberapa pencarian gagal berurutan dari satu IP.
   - Menggunakan tag meta `<meta name="robots" content="noindex, nofollow">` serta tidak didaftarkan dalam sitemap.

---

## 7. Rencana Tanggap Darurat Kebocoran Data (*Data Breach Response Plan*)

Jika terjadi indikasi peretasan atau kebocoran data pribadi:

### Langkah 1: Penghentian & Isolasi (0 - 2 Jam)
- Segera isolasi server yang terdampak.
- Revoke kredensial database dan ubah `APP_KEY`, `NIK_HASH_KEY`, serta `GOOGLE_CLIENT_SECRET`.
- Aktifkan mode pemeliharaan (`php artisan down`).

### Langkah 2: Investigasi & Audit (2 - 24 Jam)
- Analisis file `storage/logs/` dan tabel `audit_logs`.
- Identifikasi ruang lingkup kebocoran: apakah basis data terekspos mentah? (Ingat bahwa kolom `nik` terenkripsi dan `nik_hash` berstatus blind index sehingga pelaku tidak dapat membaca NIK secara langsung tanpa `APP_KEY`).

### Langkah 3: Pemberitahuan Resmi (Maksimal 3 x 24 Jam)
Sesuai Pasal 46 UU PDP:
- Kirimkan pemberitahuan resmi tertulis kepada seluruh peserta yang terdampak melalui email terdaftar.
- Laporkan insiden kepada Lembaga Pelindungan Data Pribadi / instansi berwenang.
- Sertakan informasi mengenai:
  1. Jenis data yang terimbas.
  2. Kapan insiden terjadi.
  3. Upaya penanganan dan mitigasi yang telah dilakukan oleh DPD JULEHA Brebes.
  4. Kontak nara hubung DPD untuk bantuan teknis dan informasi lebih lanjut.
