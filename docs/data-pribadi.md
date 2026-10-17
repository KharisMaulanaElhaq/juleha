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
1. Peserta mengajukan permohonan tertulis melalui email resmi DPD JULEHA Brebes (`dpd.julehabrebes@gmail.com`) atau melalui form pengaduan sekretariat.
2. Petugas memverifikasi identitas pemohon dengan mencocokkan email Google dan nomor WhatsApp terdaftar.

### B. Ketentuan Penghapusan
- **Akun Belum Memiliki Sertifikat**: Data profil (`participant_profiles`) dan akun pengguna (`users`) akan dihapus secara permanen (*hard delete*) dalam waktu maksimal 3x24 jam kerja.
- **Akun Telah Memiliki Sertifikat Resmi**: Demi kepatuhan hukum dan verifikasi keabsahan sertifikat publik:
  - NIK dan nomor WhatsApp dianonimkan (misal: diganti hash acak / disamarkan permanen).
  - Catatan nomor sertifikat dan nama pada arsip alumni tetap dipertahankan sebagai arsip keabsahan sembelihan halal.
- Log audit penghapusan data dicatat di `audit_logs` dengan aksi `DATA_ERASURE_REQUEST`.

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
