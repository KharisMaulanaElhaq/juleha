# Standar Operasional Prosedur (SOP) Penanganan Insiden Kebocoran Data Pribadi
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

> **STATUS DOKUMEN: RANCANGAN INTERNAL**  
> *SOP ini memuat prosedur tanggap darurat teknis dan organisasional jika terjadi insiden keamanan atau dugaan kebocoran data pribadi, mengacu pada Pasal 46 UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (kewajiban pemberitahuan tertulis maksimal 3 x 24 jam).*

---

## 1. Definisi Insiden Kebocoran Data

Insiden kebocoran data pribadi mencakup setiap kejadian pelanggaran keamanan yang menyebabkan terjadinya:
- Pengaksesan, perolehan, atau pengungkapan data pribadi secara tidak sah/tanpa hak.
- Perubahan, perusakan, atau penghapusan data pribadi secara tidak sah.
- Hilangnya kendali atau kebocoran kredensial administratif (`.env`, `APP_KEY`, kata sandi database).
- Akses tidak sah ke basis data MySQL atau berkas sertifikat privat di server.

---

## 2. Tim Tanggap Insiden Keamanan (Incident Response Team)

Susunan tim darurat DPD JULEHA Kabupaten Brebes:

| Peran | Pihak yang Bertanggung Jawab | Tugas Utama |
|---|---|---|
| **Ketua Penanggung Jawab** | Ketua DPD JULEHA Brebes / [DITETAPKAN PENGURUS] | Mengambil keputusan strategis, koordinasi resmi dengan lembaga pengawas & publik. |
| **Koordinator Teknis (Lead Dev / Sysadmin)** | Pengelola Teknis Website / [DITETAPKAN PENGURUS] | Isolasi teknis server, penelusuran log audit, identifikasi celah, dan pemulihan data. |
| **Petugas Pelindungan Data (PIC Privasi)** | Mengacu pada `privacy_officer_name` / [DITETAPKAN PENGURUS] | Inventarisasi jumlah subjek data terdampak, menyusun draf laporan resmi 3x24 jam. |
| **Penasihat Hukum / Advokasi** | Bidang Hukum DPD JULEHA Brebes / [DITETAPKAN PENGURUS] | Tinjauan kepatuhan regulasi, pendampingan komunikasi hukum, dan pencegahan sengketa. |

---

## 3. Tahapan Penanganan Insiden (4 Fase)

### Fase 1: Identifikasi dan Pelaporan Awal (0 – 6 Jam Pertama)
1. Setiap pengurus, staf admin, atau peserta yang menemukan kejanggalan keamanan wajib segera melapor ke email kontak privasi resmi (`privacy_contact_email`) atau grup koordinasi darurat pengurus.
2. Tim Teknis melakukan verifikasi kebenaran insiden:
   - Memeriksa tabel `audit_logs` untuk anomali pembukaan NIK massal atau ekspor data tidak wajar.
   - Memeriksa web server access log (Apache/Nginx) dan file `storage/logs/laravel.log`.
   - Menentukan status: **Insiden Terkonfirmasi** atau **False Alarm**.

### Fase 2: Pengendalian & Isolasi Celah (6 – 24 Jam)
1. **Mengaktifkan Mode Pemeliharaan**:
   Jika kebocoran melibatkan integritas aplikasi aktif:
   ```bash
   php artisan down --secret="[KODE_RAHASIA_ADMIN]"
   ```
2. **Pencabutan Akses Berisiko**:
   - Nonaktifkan akun staf atau super admin yang terindikasi disusupi atau diretas.
   - Rotasi seluruh kredensial database, API tokens, dan Google OAuth Client Secrets.
   - Blokir IP penyerang pada level firewall/web server.
3. **Penyelamatan Bukti Digital (Forensik)**:
   - Buat snapshot/image sistem saat ini sebelum melakukan perubahan atau reboot.
   - Gandakan file log audit dan database dump untuk analisis forensik hukum.

### Fase 3: Analisis Dampak & Penyusunan Laporan (24 – 48 Jam)
1. Tim mengidentifikasi:
   - Jenis data pribadi yang terdampak (apakah mencakup NIK terenkripsi, nomor WhatsApp, atau nama).
   - Jumlah subjek data (peserta/alumni) yang terdampak.
   - Potensi risiko kerugian bagi subjek data (misalnya risiko penipuan mengatasnamakan DPD).
2. Menyiapkan Draf Laporan Tertulis Pemberitahuan Resmi.

### Fase 4: Pemberitahuan Resmi (Maksimal 3 x 24 Jam / 72 Jam)
Berdasarkan Pasal 46 UU PDP, dalam hal terjadi kegagalan pelindungan data pribadi, Pengendali Data Pribadi **wajib menyampaikan pemberitahuan tertulis paling lambat 3 x 24 jam** kepada:
1. **Subjek Data Pribadi (Peserta Terimbas)**:
   - Pemberitahuan dikirimkan melalui email resmi atau pengumuman resmi di portal website.
   - Isi pemberitahuan meliputi:
     a. Data pribadi yang terungkap.
     b. Kapan dan bagaimana data pribadi tersebut terungkap.
     c. Upaya penanganan dan pemulihan yang telah dan sedang dilakukan oleh DPD JULEHA.
     d. Langkah-langkah pencegahan yang disarankan bagi peserta (misal: waspada terhadap pesan WhatsApp mencurigakan).
     e. Kontak narahubung resmi tim tanggap insiden DPD JULEHA Brebes.
2. **Lembaga Pengawas Pelindungan Data Pribadi / Kominfo**:
   - Disampaikan secara tertulis sesuai format baku dari kementerian/lembaga terkait.

---

## 4. Prosedur Pemulihan (Recovery) & Pengamanan Ulang

1. **Rotasi Kriptografi**:
   - Jika dicurigai adanya kebocoran kunci, lakukan rotasi `APP_KEY` dan `NIK_HASH_KEY` sesuai panduan di `docs/data-pribadi.md`.
2. **Audit Kode & Penambalan (Patching)**:
   - Perbaiki celah kerentanan kode yang menjadi sumber serangan.
   - Lakukan pengujian otomatis (PHPUnit feature tests) untuk memverifikasi perbaikan.
3. **Pencabutan Mode Pemeliharaan**:
   ```bash
   php artisan up
   ```
4. **Dokumentasi Pasca-Insiden (Post-Mortem Review)**:
   - Menyusun laporan internal evaluasi penyebab insiden dan pembaruan SOP dalam waktu 14 hari kerja setelah insiden tertangani.

---

*Disetujui untuk RANCANGAN TEKNIS:*  
*DPD JULEHA Kabupaten Brebes*
