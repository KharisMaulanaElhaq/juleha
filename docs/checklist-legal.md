# Checklist Tindakan Kepatuhan Legal & Tata Kelola Organisasi
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

> **STATUS DOKUMEN: PANDUAN PENGURUS ORGANISASI**  
> *Dokumen ini berisi daftar periksa tindakan non-teknis yang wajib diselesaikan oleh jajaran Pengurus DPD JULEHA Kabupaten Brebes bersama penasihat hukum untuk memastikan legalitas kelembagaan dan kepatuhan hukum terhadap UU No. 27 Tahun 2022.*

---

## 1. Legalitas Organisasi & Dokumen Badan Hukum

- [ ] **1. Konfirmasi Nama Badan Hukum Resmi**:  
  Pastikan nama resmi perkumpulan terdaftar pada SK Kementerian Hukum dan HAM RI (misal: *Perkumpulan Juru Sembelih Halal Indonesia* atau SK DPD Brebes).  
  *Lokasi pengisian teknis:* Panel Admin Filament -> Menu **Pengaturan Situs** -> Tab **Legalitas** -> Kolom `legal_entity_name`.
- [ ] **2. Nomor & Tanggal SK Legalitas**:  
  Isi nomor SK pengesahan Kemenkumham dan/atau SK Pengukuhan DPP/DPW JULEHA untuk DPD Kabupaten Brebes beserta tanggal sahnya.  
  *Lokasi pengisian teknis:* Kolom `sk_legalitas` dan `legal_sk_date`.
- [ ] **3. NPWP Organisasi**:  
  Masukkan Nomor Pokok Wajib Pajak (NPWP) resmi atas nama perkumpulan. Centang saklar `legal_show_npwp` jika disepakati ditampilkan pada halaman publik `/tentang`.  
  *Lokasi pengisian teknis:* Kolom `legal_npwp`.
- [ ] **4. Unggah Salinan Berkas Legalitas (PDF)**:  
  Gunakan fitur repeater dokumen pada tab Legalitas untuk mengunggah dokumen PDF resmi (Akta Notaris, SK Kemenkumham, SK Susunan Pengurus Brebes) agar dapat diakses atau diunduh oleh pihak berkepentingan jika diaktifkan.

---

## 2. Tata Kelola Privasi & Penunjukan Pejabat (PIC Privasi)

- [ ] **1. SK Penunjukan Petugas Pelindungan Data (PIC Privasi)**:  
  Terbitkan Surat Keputusan (SK) Ketua DPD untuk menunjuk 1 (satu) orang pengurus sebagai penanggung jawab pelindungan data pribadi dan penanganan permohonan hak data.  
  *Lokasi pengisian teknis:* Panel Admin -> **Pengaturan Situs** -> Tab **Privasi & Hak Data** -> Kolom `privacy_officer_name`.
- [ ] **2. Penetapan Email & Jalur Kontak Khusus Privasi**:  
  Siapkan alamat email fungsional (misal: `privasi@julehabrebes.org` atau email resmi organisasi).  
  *Lokasi pengisian teknis:* Kolom `privacy_contact_email`.
- [ ] **3. Penetapan SLA Hari Kerja**:  
  Tentukan batas waktu tanggapan resmi permohonan hak data peserta (default sistem: 3 hari kerja, maksimal hukum UU PDP: 3x24 jam / dapat disesuaikan).  
  *Lokasi pengisian teknis:* Kolom `privacy_request_sla_days`.

---

## 3. Tinjauan dan Penerbitan Dokumen Hukum Publik

Seluruh dokumen hukum pada sistem saat ini berstatus **RANCANGAN** dan berstatus **Draft** di database (kecuali Kebijakan Privasi v1.0 yang sudah dipublikasikan sebelumnya):

- [ ] **1. Syarat dan Ketentuan Layanan (`/syarat-ketentuan`)**:  
  - Tinjau ketentuan pendaftaran kegiatan, kewajiban peserta, hak cipta materi pelatihan, dan batasan tanggung jawab organisasi.  
  - *Tindakan Pengurus:* Setelah disetujui rapat pengurus, Super Admin membuka menu **Dokumen Hukum** di Filament, edit dokumen, ubah status dari `Draft` menjadi `Published`.
- [ ] **2. Kebijakan Penyimpanan Perangkat & Cookie (`/penyimpanan-perangkat`)**:  
  - Tinjau rincian penyimpanan lokal (session cookie, localStorage tema gelap/terang, token CSRF).  
  - *Tindakan Pengurus:* Ubah status menjadi `Published` via Filament jika siap dibuka ke publik.
- [ ] **3. Batasan Tanggung Jawab / Disclaimer (`/disclaimer`)**:  
  - Tinjau klausul pemisahan tanggung jawab antara DPD JULEHA Brebes (fasilitator pelatihan) dengan Lembaga Sertifikasi Profesi (LSP/BNSP penilai independen).  
  - *Tindakan Pengurus:* Ubah status menjadi `Published` via Filament.
- [ ] **4. Hak Cipta dan Atribusi Konten (`/hak-cipta`)**:  
  - Tinjau atribusi lisensi materi edukasi, foto kegiatan, dan karya intelektual juru sembelih.  
  - *Tindakan Pengurus:* Ubah status menjadi `Published` via Filament.

---

## 4. Kemitraan Pihak Ketiga & Lembaga Sertifikasi

- [ ] **1. Penandatanganan PKS & DPA dengan LSP Mitra**:  
  Pastikan ada naskah Perjanjian Kerjasama resmi dengan LSP mitra BNSP (sesuai `cert_issuer_name`) yang menyertakan klausul perlindungan data peserta (lihat panduan `docs/perjanjian-pihak-ketiga.md`).
- [ ] **2. Evaluasi Kontrak Hosting**:  
  Pastikan penyedia server hosting web mematuhi regulasi penyimpanan data di wilayah hukum Republik Indonesia (UU PDP & PP PSTE).

---

## 5. Rutinitas Operasional & Keamanan Berkala

- [ ] **1. Pengecekan Permohonan Hak Data di Filament**:  
  Admin memeriksa menu **Permohonan Hak Data** minimal 1 (satu) kali setiap hari kerja untuk memantau permohonan baru agar tidak melewati tenggat SLA (ditandai lencana merah).
- [ ] **2. Tinjauan Log Audit Pembukaan NIK**:  
  Super Admin memeriksa menu Log Audit secara berkala untuk memastikan pembukaan NIK oleh staf admin selalu memiliki alasan yang sah dan relevan dengan kegiatan pelatihan.
- [ ] **3. Backup Database Terenkripsi**:  
  Memastikan prosedur backup harian berjalan dan file arsip tersimpan aman di luar server utama.

---

*Disetujui untuk PANDUAN PENGURUS:*  
*DPD JULEHA Kabupaten Brebes*
