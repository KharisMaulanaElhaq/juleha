# Penilaian Dampak Pelindungan Data Ringkas (DPIA Ringan)
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

> **STATUS DOKUMEN: RANCANGAN INTERNAL**  
> *DPIA Ringkas ini disusun mengacu pada Pasal 34 UU No. 27 Tahun 2022 untuk menilai risiko pemrosesan data sensitif (khususnya NIK dan integrasi ke pihak ketiga lembaga sertifikasi) serta menguji kecukupan mitigasi teknis dan organisasional yang diterapkan.*

---

## 1. Latar Belakang & Ruang Lingkup Penilaian

DPD JULEHA Kabupaten Brebes mengelola sistem informasi pendaftaran bimbingan teknis (Bimtek) juru sembelih halal dan fasilitasi uji kompetensi bersertifikat BNSP.

Meskipun sistem tidak memproses data biometrik atau rekam medis, sistem memproses data spesifik berupa **Nomor Induk Kependudukan (NIK - 16 digit)** yang berpotensi memiliki dampak risiko tinggi jika disalahgunakan oleh pihak tidak berhak.

Tujuan DPIA ini adalah:
1. Memastikan prinsip proporsionalitas dan minimalisasi data terpenuhi.
2. Mengidentifikasi potensi ancaman keamanan data pribadi.
3. Memastikan efektivitas kontrol teknis (enkripsi, RBAC, audit log, penyamaran, anonimisasi).

---

## 2. Penilaian Kebutuhan dan Proporsionalitas Pemrosesan

| Pertanyaan Evaluasi | Analisis Sistem DPD JULEHA Brebes | Kesimpulan |
|---|---|---|
| **Apakah pengumpulan NIK mutlak diperlukan?** | Ya. NIK adalah persyaratan baku yang diwajibkan oleh BNSP dan lembaga sertifikasi profesi untuk penerbitan sertifikat kompetensi resmi negara dan mencegah duplikasi identitas alumni. | **Proporsional & Sah** |
| **Apakah ada data yang berlebihan?** | Tidak. Tanggal lahir, nama ibu kandung, dan agama tidak diminta pada profil standar karena tidak dibutuhkan untuk pelatihan internal. | **Memenuhi Minimalisasi Data** |
| **Apakah dasar persetujuan transparan?** | Ya. Persetujuan pemrosesan NIK dan penerusan ke LSP dipisahkan menjadi tujuan tersendiri pada tabel `consents` dan dapat ditinjau kapan saja melalui portal peserta. | **Transparan & Akuntabel** |

---

## 3. Matriks Analisis Risiko dan Mitigasi Keamanan

### Risiko 1: Kebocoran Data Mentah Basis Data (Database Leak)
- **Deskripsi Risiko**: Penyerang berhasil mengunduh file dump SQL atau membobol akses database MySQL.
- **Tingkat Keparahan Awal**: **Tinggi** (Risiko penyalahgunaan NIK untuk pinjaman daring ilegal atau pemalsuan identitas).
- **Langkah Mitigasi Teknis yang Diimplementasikan**:
  1. Kolom `nik` dienkripsi AES-256 pada database (`'nik' => 'encrypted'`). Tanpa `APP_KEY`, teks acak tidak dapat dibaca.
  2. Pencarian NIK unik menggunakan `nik_hash` dengan algoritma HMAC-SHA256 (`NIK_HASH_KEY`). Kunci hash terpisah dari kunci enkripsi.
- **Tingkat Risiko Sisa (Residual Risk)**: **Rendah** (Data terenkripsi kuat standar industri).

### Risiko 2: Penyalahgunaan Wewenang Internal (Internal Admin Abuse)
- **Deskripsi Risiko**: Staf admin melihat atau menyalin daftar NIK peserta untuk kepentingan pribadi.
- **Tingkat Keparahan Awal**: **Tinggi**.
- **Langkah Mitigasi Teknis yang Diimplementasikan**:
  1. Role-Based Access Control (RBAC): Editor konten tidak memiliki akses ke data peserta.
  2. NIK tersamar secara baku (`3329********1234`) di seluruh antarmuka Filament Admin.
  3. Pembukaan NIK lengkap memerlukan penekanan tombol konfirmasi dengan input alasan wajib.
  4. Setiap tindakan membuka NIK dicatat secara permanen di tabel `audit_logs` (IP, waktu, ID staf, alasan).
- **Tingkat Risiko Sisa**: **Sangat Rendah**.

### Risiko 3: Akses Publik Tidak Sah ke Berkas Sertifikat (Insecure Direct Object Reference - IDOR)
- **Deskripsi Risiko**: Pihak luar menebak URL file PDF sertifikat untuk mengunduh dokumen peserta lain.
- **Tingkat Keparahan Awal**: **Sedang**.
- **Langkah Mitigasi Teknis yang Diimplementasikan**:
  1. Berkas PDF sertifikat disimpan pada `storage/app/private/certificates/` (private disk, bukan `public`).
  2. Akses unduh diarahkan melalui controller dengan otorisasi ketat (`auth` peserta pemilik atau peran admin berwenang).
  3. Verifikasi publik di `/verifikasi/{code}` hanya menampilkan metadata ringkas dan tidak memuat tautan unduh berkas mentah.
- **Tingkat Risiko Sisa**: **Rendah**.

### Risiko 4: Pelacakan Perilaku oleh Pihak Ketiga (Third-Party Tracking)
- **Deskripsi Risiko**: Layanan pihak ketiga merekam IP dan aktivitas pengunjung saat membuka website DPD.
- **Tingkat Keparahan Awal**: **Rendah**.
- **Langkah Mitigasi Teknis yang Diimplementasikan**:
  1. Self-hosting pustaka icon Lucide (`/assets/lucide.min.js`), menghapus dependensi unpkg CDN.
  2. Tidak memasang skrip pelacak analitik pihak ketiga tanpa persetujuan eksplisit.
  3. Menghapus parameter prefilled identitas (NIK/nama) pada tautan WhatsApp resmi.
- **Tingkat Risiko Sisa**: **Sangat Rendah**.

---

## 4. Kesimpulan & Rekomendasi

Sistem informasi DPD JULEHA Kabupaten Brebes telah menerapkan kontrol teknis yang memadai dan berimbang untuk memitigasi risiko pelindungan data pribadi.

**Rekomendasi Tindak Lanjut Organisasi:**
1. Pengurus wajib menetapkan SK penunjukan resmi Petugas Pelindungan Data (PIC Privasi).
2. Penandatanganan Data Processing Agreement (DPA) dengan LSP mitra sebelum pertukaran data peserta uji kompetensi dilakukan.
3. Melakukan simulasi pemulihan backup dan penanganan insiden minimal 1 (satu) tahun sekali.

---

*Disetujui untuk RANCANGAN TEKNIS:*  
*DPD JULEHA Kabupaten Brebes*
