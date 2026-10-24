# PRD: Website Company Profile & Portal Peserta DPD JULEHA Kabupaten Brebes

| | |
|---|---|
| Versi | 0.3 (draft) |
| Tanggal | 24 Oktober 2026 |
| Status | Dalam pengembangan, modul kepatuhan privasi (UU PDP) dan legalitas telah diterapkan |
| Template dasar | CardioCare (ThemeWagon, Tailwind CSS + HTML5, gratis), sudah diubah ke tema JULEHA |
| Dokumen pendamping | `architecture.md`, `data-pribadi.md`, `ropa.md`, `retensi-data.md` |

## Riwayat perubahan

| Versi | Perubahan |
|---|---|
| 0.1 | Company profile dan pendaftaran bimtek |
| 0.2 | Portal peserta (login Google, profil, dashboard), empat jenis kegiatan, delapan program, materi per kegiatan, presensi QR, **NIK wajib** untuk sertifikat, peran dan hak akses, peta tahap pengembangan |
| 0.3 | Modul Privasi dan Kepatuhan Hukum (UU PDP): Dokumen Hukum Dinamis (Filament), Persetujuan Granular Berbasis Tujuan (`consents`), Saluran Hak Data Subjek (`data_requests`), Anonimisasi Akun Terpusat, Tab Pengaturan Legalitas & Privasi, dan Self-hosted Aset Font/Icon |

> Dokumen ini berisi asumsi. Poin yang perlu dikonfirmasi ke pengurus DPD ada di **bagian 13**.

---

## 1. Latar belakang

DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes belum punya website resmi. Akibatnya:

- Calon peserta tidak punya satu tempat resmi untuk melihat program dan jadwal kegiatan.
- Pendaftaran dan pendataan peserta dilakukan manual (chat/kertas), sehingga rawan hilang dan sulit direkap.
- Materi pelatihan, kehadiran, dan data untuk sertifikat tersebar di banyak tempat.
- Organisasi belum punya kehadiran digital yang bisa dirujuk oleh masjid, rumah potong, pedagang, dan instansi.

## 2. Tujuan

1. Menyediakan **company profile** resmi DPD JULEHA Kab. Brebes.
2. Menyediakan **portal peserta**: akun, profil, pendaftaran kegiatan, materi, dan riwayat kehadiran.
3. Mencatat **kehadiran dengan QR** yang sulit dititipkan.
4. Menyimpan data peserta yang rapi dan aman, termasuk **NIK untuk penerbitan sertifikat**.
5. Menyediakan **kanal informasi** (berita, jadwal, galeri) yang mudah diperbarui tanpa coding.

### Metrik keberhasilan (3 bulan setelah rilis)

| Metrik | Target awal (bisa disesuaikan) |
|---|---|
| Pendaftar kegiatan lewat website | ≥ 70% dari total pendaftar |
| Rekap peserta dan kehadiran | Dari manual menjadi ekspor 1 klik |
| Presensi yang tercatat via QR | ≥ 90% dari kehadiran per sesi |
| Peserta yang melengkapi profil (NIK) sebelum kegiatan | ≥ 95% |
| Halaman memuat di jaringan seluler | < 3 detik (LCP) |
| Pembaruan konten tanpa developer | Ya |

## 3. Pengguna

| Persona | Kebutuhan utama |
|---|---|
| **Pengunjung umum** (masyarakat, masjid, RPH, pedagang, instansi) | Mengenal organisasi, melihat program, mencari kontak |
| **Peserta** (calon juleha, juleha aktif, pengurus masjid, pekerja RPH) | Daftar akun, mendaftar kegiatan, melihat status, mengunduh materi, presensi |
| **Admin DPD** | Kelola kegiatan, konfirmasi pendaftar, unggah materi, buka presensi, rekap, siapkan data sertifikat |
| **Editor konten** | Kelola berita, galeri, FAQ, program |
| **Pimpinan** | Melihat ringkasan jumlah peserta dan kegiatan |

## 4. Program dan jenis kegiatan

### 4.1 Tujuh program (halaman publik)

**Layanan Masyarakat**
1. Penyembelihan Hewan Aqiqah
2. Qurban
3. Syiar dan Edukasi Keliling (instansi, ponpes, kampus, dll)

**Pendidikan & Sertifikasi**
4. Bimtek dan Pelatihan
5. Sertifikasi Kompetensi BNSP
6. Training of Trainer (TOT) Juleha
7. Instruktur BNSP

Program 1 sampai 3 diarahkan ke WhatsApp admin. Program 4 sampai 6 punya kegiatan (angkatan) yang bisa didaftar lewat portal. "Instruktur BNSP" tetap program terpisah dan bukan jenis kegiatan.

> **Catatan penggabungan program & jenis kegiatan:** "Sertifikasi Kompetensi" dan "Sertifikasi BNSP" telah digabungkan menjadi SATU program dan SATU jenis kegiatan resmi: **Sertifikasi Kompetensi BNSP** (`sertifikasi_kompetensi`, slug: `sertifikasi-kompetensi-bnsp`). Kolom partner/LSP tetap kosong sampai diisi admin. Deskripsi bersifat netral ("Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra. Informasi lengkap akan diumumkan oleh DPD.") dengan tombol tanya via WhatsApp.

### 4.2 Tiga jenis kegiatan (yang bisa didaftar peserta)

| Jenis | Label |
|---|---|
| `bimtek` | Bimtek |
| `sertifikasi_kompetensi` | Sertifikasi Kompetensi BNSP |
| `tot` | Training of Trainer (TOT) |

Semua jenis memakai kerangka yang sama: pendaftaran, materi, sesi, presensi. Pembedanya hanya jenis dan label. Peserta mendaftar ke **angkatan/jadwal tertentu** (misal "Bimtek Angkatan 13, Oktober 2026"), bukan ke program secara umum, sehingga materi dan presensi menempel di angkatan itu.

## 5. Ruang lingkup dan tahapan

### Fase 1: Situs publik dan admin (sudah berjalan, perlu pengisian konten)
- Halaman publik: Beranda, Tentang, Pengurus, Program, Bimtek, Berita, Galeri, FAQ, Kontak.
- Pengaturan situs di panel admin (kontak, WhatsApp, email, SK, visi-misi, statistik).
- Pendaftaran bimtek awal dan konfirmasi via WhatsApp.
- Tema ungu tua dan emas.

### Fase 2: Portal peserta
- Login Google, profil peserta (termasuk NIK), dashboard.
- Pendaftaran ke kegiatan (enrollment) dan konfirmasi admin.
- Empat jenis kegiatan dan delapan program.
- Peran dan hak akses.

### Fase 3: Materi
- Admin mengunggah materi per kegiatan.
- Peserta aktif mengakses dan mengunduh materi kegiatannya saja.

### Fase 4: Presensi QR
- Sesi per kegiatan, layar presensi QR dinamis, presensi manual, rekap.

### Fase 5: Hasil kompetensi dan sertifikat (belum dikunci)
- Hasil asesmen, nomor sertifikat, penerbit, tanggal terbit, masa berlaku, unggah/unduh sertifikat.
- Bentuk final menunggu jawaban pengurus soal perbedaan sertifikasi (bagian 13).

### Di luar lingkup
- Pembayaran online (kecuali nanti dibutuhkan).
- Aplikasi mobile native.
- Penerbitan sertifikat resmi oleh sistem tanpa lembaga penerbit yang sah.
- Verifikasi NIK ke Dukcapil (butuh kerja sama resmi, dan situs ini hanya memvalidasi format).

## 6. Peran dan hak akses

| Peran | Akses |
|---|---|
| `participant` | Dashboard peserta, profil sendiri, mendaftar kegiatan, materi dan presensi kegiatan yang diikuti. **Tidak bisa masuk `/admin`** |
| `super_admin` | Semua, termasuk pengaturan, ekspor dengan NIK, dan log audit |
| `admin_kegiatan` | Kegiatan, pendaftaran, materi, presensi, hasil kompetensi, dan membuka NIK (tercatat) |
| `editor_konten` | Berita, galeri, FAQ, program. **Tidak** bisa melihat pendaftaran, NIK, materi, atau presensi |

Keputusan saat ini: **hanya admin** (super_admin dan admin_kegiatan) yang boleh mengunggah materi dan mengelola presensi. Peran `instruktur` belum dibuat, tetapi hak akses diimplementasikan lewat Laravel Policy sehingga mudah ditambahkan nanti.

## 7. Kebutuhan fungsional

Prioritas: **P0** wajib, **P1** sebaiknya ada, **P2** berikutnya.

### 7.1 Situs publik

| ID | Kebutuhan | Prio |
|---|---|---|
| F-01 | Beranda: hero, ringkasan, program, jadwal terdekat, CTA daftar | P0 |
| F-02 | Tentang: profil, visi, misi, sejarah, dasar hukum/SK (dari pengaturan situs) | P0 |
| F-03 | Struktur pengurus (foto, nama, jabatan) | P0 |
| F-04 | Program: delapan program, dikelompokkan dua kategori, halaman detail per program | P0 |
| F-05 | Daftar kegiatan: tanggal, lokasi, kuota, sisa kuota, status, jenis | P0 |
| F-06 | Berita dengan daftar dan detail | P1 |
| F-07 | Galeri foto kegiatan | P1 |
| F-08 | FAQ | P1 |
| F-09 | Kontak: alamat, peta, WhatsApp, email, jam layanan (semua dari pengaturan situs) | P0 |
| F-10 | Counter statistik dari pengaturan situs, hanya angka yang terverifikasi | P2 |
| F-11 | Mode terang/gelap | P2 |
| F-12 | Kebijakan Privasi yang memuat data Google, NIK, dan hak penghapusan | P0 |

### 7.2 Akun dan profil peserta

| ID | Kebutuhan | Prio |
|---|---|---|
| A-01 | Pendaftaran akun peserta **wajib lewat Login dengan Google** (scope email dan profil saja) | P0 |
| A-02 | Akun Google dengan email terverifikasi yang cocok ditautkan ke akun lama, bukan membuat duplikat | P0 |
| A-03 | Akun dari Google selalu `participant`, tidak bisa naik peran dan tidak bisa masuk `/admin` | P0 |
| A-04 | Admin login ke `/admin` dengan email dan password (bukan Google) | P0 |
| A-05 | Pengaturan situs: toggle pendaftaran email+password sebagai cadangan (default nonaktif) | P1 |
| A-06 | Setelah login pertama, wajib "Lengkapi Profil" sebelum bisa mendaftar kegiatan | P0 |
| A-07 | Profil: nomor WhatsApp (unik, dinormalisasi 62...), nama sesuai KTP, **NIK**, kecamatan, desa, asal/instansi, status, persetujuan data | P0 |
| A-08 | Validasi NIK: 16 digit, format valid, tidak ganda. Tidak mengklaim verifikasi Dukcapil | P0 |
| A-09 | NIK tersimpan terenkripsi, ditampilkan tersamar, pembukaan oleh admin tercatat di audit | P0 |
| A-10 | Pesan error Google (izin ditolak, email tidak terverifikasi, layanan down) berbahasa Indonesia | P1 |
| A-11 | Tempat dan tanggal lahir ditambahkan **hanya jika** dibutuhkan sertifikat | P1 |

### 7.3 Pendaftaran kegiatan

| ID | Kebutuhan | Prio |
|---|---|---|
| R-01 | Peserta mendaftar ke kegiatan berstatus `open` dari dashboard, setelah profil lengkap | P0 |
| R-02 | Cek kuota dan duplikat di dalam transaksi database | P0 |
| R-03 | Status pendaftaran: menunggu, aktif, ditolak, selesai, dengan catatan admin | P0 |
| R-04 | Notifikasi email ke admin untuk pendaftar baru, dan ke peserta saat status berubah | P0 |
| R-05 | Dashboard peserta: kegiatan saya, katalog kegiatan dibuka (dikelompokkan per jenis), jadwal terdekat | P0 |
| R-06 | Tombol konfirmasi WhatsApp dengan pesan terisi sebagai jalur cadangan | P1 |
| R-07 | Data pendaftaran lama ditautkan ke akun hanya bila nomor HP cocok, sisanya arsip | P1 |

### 7.4 Materi

| ID | Kebutuhan | Prio |
|---|---|---|
| M-01 | Admin mengunggah materi per kegiatan (file atau tautan), dengan urutan dan status terbit | P0 |
| M-02 | File disimpan di penyimpanan **privat**, bukan folder publik | P0 |
| M-03 | Hanya peserta berstatus aktif di kegiatan itu yang bisa melihat dan mengunduh | P0 |
| M-04 | Tipe dan ukuran file dibatasi, nama file diacak | P0 |
| M-05 | Pratinjau PDF di browser, tautan video dibuka di tab baru | P1 |
| M-06 | Catatan unduhan dan notifikasi materi baru | P2 |

### 7.5 Presensi QR

| ID | Kebutuhan | Prio |
|---|---|---|
| P-01 | Sesi per kegiatan (judul, tanggal, jam mulai dan selesai, lokasi) | P0 |
| P-02 | Layar presensi untuk proyektor: QR **berganti tiap ±45 detik**, hitung mundur, jumlah hadir | P0 |
| P-03 | Peserta memindai dengan kamera HP, login, lalu tercatat hadir jika: token valid, sesi berlangsung, peserta aktif di kegiatan itu, belum absen | P0 |
| P-04 | Satu peserta satu presensi per sesi (unik) | P0 |
| P-05 | Presensi manual oleh admin (hadir/izin/sakit/alpa), sumbernya tercatat (`qr` atau `manual`) | P0 |
| P-06 | Rekap kehadiran per kegiatan (matriks peserta x sesi) dan ekspor Excel | P0 |
| P-07 | Riwayat dan persentase kehadiran di dashboard peserta | P1 |
| P-08 | Rate limit dan log audit (IP, user agent) pada route presensi | P1 |
| P-09 | Geolokasi **tidak** dijadikan syarat (mudah dipalsukan dan menimbulkan masalah privasi) | Keputusan |

### 7.6 Hasil kompetensi dan sertifikat (Fase 5, belum dikunci)

| ID | Kebutuhan | Prio |
|---|---|---|
| S-01 | Hasil asesmen per peserta (kompeten/belum), nomor sertifikat, penerbit, tanggal terbit, masa berlaku | P1 |
| S-02 | Unggah dan unduh sertifikat dari akun peserta | P1 |
| S-03 | Data NIK dan nama untuk sertifikat diambil dari profil, dengan batas kehadiran minimum jika ditetapkan | P1 |
| S-04 | Halaman verifikasi sertifikat dengan nomor, tanpa menampilkan NIK | P2 |

### 7.7 Panel admin

| ID | Kebutuhan | Prio |
|---|---|---|
| D-01 | CRUD kegiatan, program, berita, galeri, pengurus, FAQ | P0 |
| D-02 | Pendaftaran: filter per kegiatan/jenis/status, konfirmasi dan tolak (satuan dan massal), ekspor | P0 |
| D-03 | Dasbor: pendaftar menunggu, kegiatan dibuka beserta sisa kuota, pendaftar per kecamatan | P1 |
| D-04 | Pengaturan situs bertab (kontak, profil dan visi-misi, statistik, media sosial) dengan komponen native Filament | P0 |
| D-05 | Menu dikelompokkan: Kegiatan, Konten, Pengaturan, dengan badge pendaftar yang menunggu | P1 |
| D-06 | Semua data organisasi di tampilan publik dibaca dari satu sumber (pengaturan situs), tanpa hardcode | P0 |
| D-07 | Log audit pembukaan NIK, ekspor dengan NIK, dan aktivitas admin penting | P0 |
| D-08 | Responsif untuk HP dan seluruh teks berbahasa Indonesia | P1 |

## 8. Kebutuhan non-fungsional

- **Responsif dan mobile-first.** Mayoritas pengguna memakai HP dengan jaringan bervariasi.
- **Performa:** gambar WebP, CSS hasil build Vite, target LCP < 3 detik di 4G.
- **Keamanan:** HTTPS wajib, password admin di-hash, CSRF, validasi server, rate limit pada login, form, dan presensi, session regenerate setelah login.
- **Privasi dan UU PDP (UU No. 27 Tahun 2022):** data minimal, persetujuan eksplisit, kebijakan privasi, hak penghapusan, prosedur jika terjadi kebocoran. Rincian NIK ada di bagian 9.
- **Zona waktu:** Asia/Jakarta untuk semua jadwal dan presensi.
- **Aksesibilitas:** kontras memadai (WCAG AA), fokus keyboard terlihat, label form jelas.
- **SEO dasar:** title, meta, sitemap, Open Graph, data terstruktur Organization.
- **Bahasa:** Indonesia.
- **Ketersediaan:** target 99% per bulan.
- **Pemeliharaan:** konten dikelola dari panel admin. Aturan bisnis (kuota, presensi, hak akses) ada di backend, bukan JavaScript.
- **Email transaksional** harus berfungsi (notifikasi, reset password admin).

## 9. Penanganan data pribadi (NIK dan data Google)

NIK **wajib** karena dipakai untuk penerbitan sertifikat. NIK adalah data sensitif, sehingga:

1. Disimpan **terenkripsi** di database (cast `encrypted`), bukan teks biasa.
2. Cek duplikat memakai **`nik_hash`** (HMAC-SHA256 dengan kunci rahasia terpisah), bukan NIK mentah.
3. Ditampilkan **tersamar** (4 digit awal dan akhir). Nilai lengkap hanya bisa dibuka oleh `super_admin` dan `admin_kegiatan` lewat aksi khusus yang **dicatat** (siapa, kapan, IP).
4. **Tidak** muncul di email, notifikasi, log, pesan WhatsApp, URL, atau JSON model.
5. Ekspor Excel/CSV **tanpa NIK secara default**. Ekspor dengan NIK hanya `super_admin`, dengan konfirmasi dan tercatat.
6. Peserta hanya melihat NIK sendiri (tersamar). Perubahan NIK setelah dipakai pada sertifikat yang terbit harus lewat admin.
7. Kolom yang dikumpulkan dibatasi pada kebutuhan sertifikat. Tambahan kolom (misal tempat dan tanggal lahir) hanya setelah ada kebutuhan yang jelas.
8. Kebijakan Privasi dan teks persetujuan menyebut: tujuan, siapa yang mengakses, masa simpan, dan cara meminta penghapusan.
9. Backup database **terenkripsi** dan disimpan terpisah dari server. `APP_KEY` dan `NIK_HASH_KEY` disimpan aman di luar repo dan **tidak boleh diganti** setelah ada data.
10. Dari Google hanya diambil email, nama, dan foto profil, serta ID Google untuk penautan akun.

## 10. Sitemap

```
/                                  Beranda
/tentang                           Tentang
/pengurus                          Struktur pengurus
/program                           Program (8, dua kategori)
/program/{slug}                    Detail program
/bimtek                            Daftar kegiatan dibuka (semua jenis)
/bimtek/{slug}                     Detail kegiatan
/berita, /berita/{slug}            Berita
/galeri                            Galeri
/faq                               FAQ
/kontak                            Kontak
/kebijakan-privasi                 Kebijakan privasi

/masuk, /daftar                    Login dan daftar (Google)
/auth/google, /auth/google/callback  OAuth
/dashboard                         Dashboard peserta
/dashboard/profil                  Lengkapi/ubah profil
/dashboard/kegiatan/{event}        Kelas kegiatan
/dashboard/kegiatan/{event}/materi Materi (peserta aktif)
/presensi/{token}                  Pemindaian presensi (perlu login)

/admin                             Panel admin (email dan password)
/admin/presensi/{session}/layar    Layar presensi QR (admin)
```

## 11. Alur utama

### 11.1 Daftar akun dan mendaftar kegiatan
1. Pengunjung menekan "Daftar"/"Masuk dengan Google".
2. Setelah persetujuan Google, akun `participant` dibuat atau ditautkan.
3. Peserta melengkapi profil (WhatsApp, nama sesuai KTP, NIK, domisili, status, persetujuan data).
4. Peserta memilih kegiatan `open` dan mendaftar. Sistem cek kuota dan duplikat, status **Menunggu**.
5. Admin mendapat notifikasi, memeriksa, lalu mengubah ke **Aktif** atau **Ditolak**.
6. Peserta menerima email dan melihat status di dashboard. Peserta aktif mendapat akses materi.

### 11.2 Presensi QR
1. Admin membuka **Layar Presensi** untuk sesi yang berlangsung.
2. QR berganti tiap ±45 detik dan memuat link dengan token berumur pendek.
3. Peserta memindai QR dengan kamera HP, dan jika belum login diarahkan login lalu kembali.
4. Server memeriksa token, jam sesi, status peserta, dan duplikasi. Hasil tampil jelas.
5. Jika HP peserta bermasalah, admin mengisi presensi manual (sumber tercatat).

## 12. Status template CardioCare

Template sudah dipasang sebagai Blade + Vite dan diubah ke tema JULEHA (ungu tua dan emas). Halaman layanan medis diganti menjadi halaman program. Folder template asli disimpan sebagai referensi di luar `public/` dan dikecualikan dari git.

Hal yang tetap berlaku untuk template gratis:
- **Atribusi** (K29Solutions / ThemeWagon) dipertahankan di footer sesuai ketentuan lisensi. Baca halaman lisensi sebelum rilis.
- Semua gambar demo diganti dengan foto sendiri atau gambar berlisensi jelas.

## 13. Pertanyaan terbuka

Pertanyaan terbuka yang **telah terjawab**:
- **Penerbit sertifikat:** LSP Pertanian RI (dikonfigurasi dinamis lewat pengaturan `cert_issuer_name`, didampingi lisensi `cert_issuer_license`).
- **Masa berlaku sertifikat BNSP:** 3 tahun (dihitung otomatis sejak tanggal terbit lewat `cert_validity_years`).
- **Bentuk sertifikat:** Digital (file PDF resmi dari LSP disimpan di penyimpanan privat; sistem DPD tidak mencetak/membuat sertifikat sendiri).

Pertanyaan terbuka yang **masih menunggu kepastian**:
1. **Syarat peserta & biaya:** Berapa rincian biaya uji kompetensi dan dokumen persyaratan teknis dari LSP (saat ini ditampilkan "Akan diumumkan oleh DPD JULEHA Brebes").
2. **Format data resmi formulir LSP:** Kolom data apa saja selain Nama KTP, NIK, WhatsApp, Email, Kecamatan, dan Desa yang disyaratkan oleh form resmi LSP Pertanian RI saat pendaftaran kolektif.
3. **Prosedur perpanjangan (resertifikasi):** Mekanisme perpanjangan setelah 3 tahun masa berlaku habis (sistem saat ini mengarahkan ke kontak WhatsApp panitia).
4. **Instruktur BNSP:** Apakah anggota DPD yang memegang sertifikat instruktur/asesor, atau program penyaluran?
5. **Akun Google Cloud** (OAuth Client) dibuat atas akun resmi DPD, bukan pribadi. Siapa pemiliknya?
6. Layanan aqiqah dan qurban: ada paket, area layanan, atau tarif? Jika tidak, cukup diarahkan ke WhatsApp.
7. Apakah DPD sudah menyetujui website ini, dan **domain serta hosting** atas nama siapa?
8. Logo dan pedoman identitas resmi, serta foto kegiatan asli.

## 14. Risiko dan mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Kebocoran NIK | Penyalahgunaan identitas, risiko hukum dan reputasi | Enkripsi, hash, akses berperan, audit, ekspor tanpa NIK, backup terenkripsi, prosedur insiden |
| Peserta tanpa akun Google tidak bisa daftar | Jumlah peserta turun | Opsi cadangan email+password, panitia siap membantu |
| Login Google gagal (konfigurasi OAuth, status "Testing", redirect URI) | Tidak ada yang bisa daftar | Ikuti `docs/google-oauth.md`, ubah ke "In production", uji di domain produksi |
| Titip absen QR | Data kehadiran tidak sahih | QR berganti cepat, login wajib, cek status peserta, presensi manual tercatat |
| Klaim sertifikasi tidak akurat (BNSP, SKKNI, angka statistik) | Masalah reputasi dan hukum | Verifikasi dengan DPD sebelum tayang, gunakan teks netral |
| Materi bocor lewat URL | Materi dibagikan ke non-peserta | Penyimpanan privat, unduhan lewat route bertingkat izin |
| Berkas sertifikat bocor | Dokumen privat disalahgunakan | Penyimpanan privat, nama file acak, verifikasi kepemilikan, header anti-cache |
| Kehilangan `APP_KEY`/`NIK_HASH_KEY` | Data terenkripsi tidak bisa dibuka | Simpan kunci di pengelola password terpisah, sertakan di prosedur backup |
| Domain/hosting atas nama pribadi | Sengketa kepemilikan | Daftarkan atas nama organisasi atau buat serah terima |
| Email tidak terkirim | Notifikasi tidak sampai | Siapkan SMTP/layanan email transaksional dan uji sebelum rilis |
| Spam dan pendaftar palsu | Data kotor | Login Google, konfirmasi admin, rate limit |

## 15. Tahapan kerja

| Tahap | Isi | Status |
|---|---|---|
| 0 | Persiapan: persetujuan DPD, konten, domain | Berjalan |
| 1 | Template ke Laravel, tema JULEHA, panel admin, pengaturan situs | Selesai |
| 2 | Portal peserta: Google login, profil dan NIK, enrollment, tiga jenis kegiatan, tujuh program | Selesai |
| 3 | Materi per kegiatan & unduhan privat | Selesai |
| 4 | Presensi QR dan rekapitulasi kehadiran | Selesai |
| 5 | Hasil kompetensi dan sertifikat (catat hasil, simpan PDF LSP, unduh privat, verifikasi publik, pengingat 3 tahun) | Selesai |
| 5.5 | Kepatuhan Privasi (UU PDP) & Legalitas Organisasi: Dokumen hukum terkelola admin (5 dokumen), persetujuan granular (`consents`), saluran hak subjek data (`data_requests`), service anonimisasi, tab legalitas & privasi, self-host aset privat | Selesai |
| 6 | Pengujian menyeluruh, pengisian konten, deploy produksi (HTTPS, cron `schedule:run`, backup) | Sedang berjalan |
