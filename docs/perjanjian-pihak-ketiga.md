# Pedoman Perjanjian Pemrosesan Data Pihak Ketiga (DPA)
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

> **STATUS DOKUMEN: RANCANGAN INTERNAL**  
> *Dokumen ini merupakan panduan dan checklist klausul pelindungan data pribadi yang wajib ada saat DPD JULEHA Kabupaten Brebes mengikat kerjasama dengan pihak ketiga (Lembaga Sertifikasi Profesi, penyedia hosting, vendor TI, dan penyedia layanan pihak ketiga).*

---

## 1. Landasan Hukum Kemitraan Pemrosesan Data

Berdasarkan UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi:
- **Pengendali Data Pribadi**: DPD JULEHA Kabupaten Brebes (menentukan tujuan dan kendali pemrosesan).
- **Prosesor Data Pribadi**: Pihak ketiga yang melakukan pemrosesan data pribadi atas nama dan instruksi sah dari Pengendali Data.
- Pengendali Data wajib memastikan bahwa Prosesor Data menerapkan langkah-langkah teknis dan organisasional yang memadai guna menjamin keamanan data pribadi.

---

## 2. Inventarisasi Pihak Ketiga & Model Perjanjian

| Pihak Ketiga | Jenis Layanan | Data yang Diproses | Status Perjanjian / Acuan |
|---|---|---|---|
| **Lembaga Sertifikasi Profesi (LSP)** | Penyelenggara Uji Kompetensi BNSP | NIK, Nama Lengkap, Nomor HP, Alamat Domisili, Riwayat Pelatihan | **Wajib Perjanjian Kerjasama (PKS) & DPA Tertulis** [DITETAPKAN PENGURUS] |
| **Penyedia Layanan Cloud / VPS Hosting** | Infrastruktur Server & Basis Data MySQL | Seluruh data aplikasi tersimpan (dalam keadaan terenkripsi) | Mengacu pada Master Service Agreement (MSA) & Kebijakan Keamanan Penyedia Hosting |
| **Google LLC** | Autentikasi Google OAuth 2.0 | Nama, Email, Google User ID, Avatar Foto | Google APIs Terms of Service & Google Cloud Privacy Notice |
| **Penyedia Mail Server (SMTP)** | Pengiriman Email Transaksional | Alamat Email Penerima, Nama, Judul Notifikasi | Terms of Service Penyedia SMTP yang digunakan |

---

## 3. Checklist Klausul Wajib DPA (Khususnya Kerjasama LSP/BNSP)

Setiap rancangan Perjanjian Kerjasama (PKS) atau addendum pemrosesan data dengan LSP mitra wajib memuat poin-poin berikut:

- [ ] **1. Batasan Tujuan Pemrosesan (Purpose Limitation)**:  
  Pihak ketiga dilarang keras menggunakan data peserta DPD JULEHA untuk tujuan pemasaran, komersialisasi, atau diserahkan kepada pihak lain di luar proses sertifikasi kompetensi resmi.
- [ ] **2. Larangan Pengalihan Tanpa Izin (No Sub-Processing)**:  
  Pihak ketiga tidak boleh melimpahkan pemrosesan data kepada sub-kontraktor tanpa persetujuan tertulis terlebih dahulu dari DPD JULEHA Brebes.
- [ ] **3. Kewajiban Pengamanan Teknis (Security Measures)**:  
  Pihak ketiga wajib menerapkan enkripsi pada transmisi dan penyimpanan berkas ber-NIK serta membatasi akses personel internal.
- [ ] **4. Kewajiban Notifikasi Insiden (Data Breach Notification)**:  
  Pihak ketiga wajib memberitahukan secara tertulis kepada DPD JULEHA Brebes dalam waktu maksimal 24 jam sejak ditemukannya indikasi atau insiden kegagalan pelindungan data.
- [ ] **5. Hak Audit & Verifikasi**:  
  DPD JULEHA Brebes berhak meminta klarifikasi, konfirmasi kepatuhan, atau bukti tata kelola data yang dilakukan oleh pihak ketiga.
- [ ] **6. Penghapusan Data Pasca-Layanan (Post-Service Deletion)**:  
  Setelah proses uji sertifikasi dan penerbitan sertifikat selesai, pihak ketiga wajib memusnahkan atau mengembalikan salinan data mentah sesuai batas masa retensi hukum yang disepakati.

---

## 4. Format Rancangan Klausul Standar Kerjasama

```text
PASAL [...]
PELINDUNGAN DATA PRIBADI PESERTA

1. PARA PIHAK sepakat untuk mematuhi seluruh ketentuan Undang-Undang Republik Indonesia 
   Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).
2. PIHAK KEDUA (LSP) bertindak sebagai Prosesor Data Pribadi yang menerima delegasi 
   data peserta pelatihan dari PIHAK PERTAMA (DPD JULEHA Brebes) semata-mata untuk 
   keperluan pelaksanaan uji sertifikasi kompetensi.
3. PIHAK KEDUA menjamin kerahasiaan Nomor Induk Kependudukan (NIK) dan data pribadi 
   peserta lainnya serta dilarang menyebarluaskan, mendistribusikan, atau memanfaatkan 
   data tersebut untuk keperluan lain tanpa persetujuan tertulis dari PIHAK PERTAMA.
4. Apabila terjadi kegagalan sistem keamanan pada PIHAK KEDUA yang mengakibatkan 
   kebocoran data peserta, PIHAK KEDUA wajib memberitahukan kepada PIHAK PERTAMA 
   secara tertulis dalam waktu paling lambat 1 x 24 jam.
```

---

*Disetujui untuk RANCANGAN TEKNIS:*  
*DPD JULEHA Kabupaten Brebes*
