# Jadwal Retensi dan Pemusnahan Data Pribadi
**DPD JULEHA (Juru Sembelih Halal) Kabupaten Brebes**

> **STATUS DOKUMEN: RANCANGAN INTERNAL**  
> *Dokumen ini merupakan pedoman teknis jadwal retensi data. Masa simpan yang bertanda [DITETAPKAN PENGURUS] wajib diputuskan melalui rapat pleno pengurus DPD JULEHA Brebes dengan mempertimbangkan ketentuan hukum dan kebutuhan arsip organisasi.*

---

## 1. Prinsip Pembatasan Masa Simpan (Storage Limitation)

Sesuai Pasal 16 ayat (2) huruf e UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi, data pribadi tidak boleh disimpan lebih lama dari waktu yang diperlukan untuk mencapai tujuan pemrosesannya.

Ketika masa retensi berakhir atau terdapat permohonan penghapusan yang sah, sistem DPD JULEHA Brebes menerapkan 2 (dua) metode penanganan:
1. **Pemusnahan Berkas Fisik & Token**: Menghapus file secara permanen dari media penyimpanan (*unlinking* file fisik PDF sertifikat di private storage).
2. **Anonimisasi Permanen (Irreversible Anonymization)**: Menimpa (*overwrite*) seluruh atribut identitas pengenal pada basis data dengan data anonim sehingga tidak dapat lagi dikaitkan dengan individu tertentu, dengan tetap mempertahankan data agregat historis kegiatan (jumlah peserta, statistik kehadiran) untuk kepentingan laporan kepengurusan.

---

## 2. Tabel Jadwal Retensi Berdasarkan Kategori Data

| Kategori Data | Jenis Kolom / Tabel Sistem | Masa Retensi Aktif | Masa Retensi Pasif / Arsip | Tindakan Akhir Retensi |
|---|---|---|---|---|
| **Data Akun Pengguna** | Tabel `users` (`name`, `email`, `google_id`, `avatar_url`) | Selama akun aktif digunakan peserta | 30 hari setelah pengajuan hapus akun disetujui | Anonimisasi permanen (`name` => `Peserta Dianonimkan`, `email` => `dihapus_{id}@anonymized.invalid`, soft delete) |
| **Data Profil & Identitas Sensitif** | Tabel `participant_profiles` (`nik`, `nik_hash`, `phone`, `subdistrict`, `village`, `institution`) | Selama akun aktif | Disimpan untuk validitas verifikasi sertifikat selama masa aktif sertifikat / [DITETAPKAN PENGURUS: 3-5 Tahun] | Dikosongkan (`NULL`) saat akun dianonimkan |
| **Persetujuan (Consent)** | Tabel `consents` (`purpose`, `document_version`, `granted_at`, `withdrawn_at`) | Selama persetujuan berlaku | Minimal 2 (dua) tahun setelah penarikan atau akun dianonimkan | Arsip pembuktian hukum audit kepatuhan |
| **Permohonan Pendaftaran Kegiatan** | Tabel `enrollments` (`user_id`, `event_id`, `status`) | Dari pendaftaran hingga kegiatan selesai | [DITETAPKAN PENGURUS: 5 Tahun] untuk data statistik & laporan tahunan | Tetap disimpan dalam bentuk anonymized record tanpa pengenal pribadi |
| **Presensi & Kehadiran** | Tabel `attendances` & `attendance_scans` | Selama kegiatan berlangsung | [DITETAPKAN PENGURUS: 3 Tahun] sebagai bukti pemenuhan jam pelajaran | Disimpan sebagai rekapitulasi numerik |
| **Sertifikat Pelatihan Digital** | Tabel `certificates` & berkas fisik PDF di `storage/app/private/certificates/` | Selama alumni berstatus aktif dan sertifikat berlaku | Catatan nomor sertifikat dan kode verifikasi publik dipertahankan untuk verifikasi keaslian | Berkas PDF fisik dihapus permanen jika akun dianonimkan atas permohonan hak hapus data |
| **Permohonan Hak Data** | Tabel `data_requests` (`ticket_number`, `name`, `email`, `phone`, `request_type`, `notes`) | Selama proses SLA penyelesaian (default 3x24 jam / maksimal sesuai pengaturan `privacy_request_sla_days`) | Minimal 2 (dua) tahun sejak status `disetujui` / `ditolak` | Disimpan sebagai arsip bukti kepatuhan penanganan hak subjek data |
| **Log Audit Administratif** | Tabel `audit_logs` (`actor_id`, `action`, `details`, `ip_address`, `user_agent`) | 12 (dua belas) bulan pertama | [DITETAPKAN PENGURUS: Minimal 12-24 Bulan] sesuai standar keamanan siber | Pembersihan berkala melalui scheduled job rotasi log |
| **Log Server & Error** | File `storage/logs/laravel.log` | 30 (tiga puluh) hari dengan sistem rotasi harian | Maksimal 90 hari | Dihapus otomatis oleh sistem rotasi log file |

---

## 3. Alur Teknis Pemusnahan & Anonimisasi (`UserAnonymizationService`)

Apabila Super Admin menyetujui permohonan hak penghapusan akun atau masa retensi pasif terpenuhi, sistem mengeksekusi service terstandarisasi:

1. **Penghapusan Berkas Fisik Sertifikat**:
   - Sistem menelusuri seluruh berkas PDF sertifikat milik peserta pada `storage/app/private/certificates/{event_id}/{user_id}_*.pdf`.
   - File fisik dihapus dari disk penyimpanan (`Storage::disk('private')->delete(...)`).
   - Nomor registrasi dan kode verifikasi publik sertifikat tetap diarsipkan dengan nama disamarkan untuk mencegah duplikasi kode acak.
2. **Pembersihan Kolom Identitas**:
   - `participant_profiles.nik` di-set menjadi `NULL`.
   - `participant_profiles.nik_hash` di-set menjadi `NULL`.
   - `participant_profiles.phone` di-set menjadi `NULL`.
   - `participant_profiles.address_street` di-set menjadi `NULL`.
   - `participant_profiles.emergency_contact` di-set menjadi `NULL`.
3. **Penyamaran Akun User**:
   - `users.name` diubah menjadi `'Peserta Dianonimkan'`.
   - `users.email` diubah menjadi `'dihapus_{user_id}_{timestamp}@anonymized.invalid'`.
   - `users.google_id` dan `users.avatar_url` di-set menjadi `NULL`.
   - `users.deleted_at` diisi timestamp (Soft Delete) sehingga peserta tidak dapat login kembali.
4. **Pencatatan Audit Tanpa Data Pribadi**:
   - Sistem mencatat entri pada `audit_logs` dengan aksi `'anonymize_user'`.
   - Rincian log audit HANYA mencatat: `User ID`, `Actor ID`, nomor permohonan `data_requests`, dan waktu eksekusi. NIK, nama lama, dan email lama **TIDAK DICATAT** pada rincian audit log.

---

## 4. Pengecualian Retensi (Legal Hold)

Penghapusan data tidak dapat dilakukan secara langsung apabila:
1. Sedang berlangsung proses penyelidikan, penyidikan, atau proses peradilan oleh aparat penegak hukum yang berwenang di Republik Indonesia.
2. Terdapat kewajiban pelaporan hukum yang secara khusus mewajibkan penyimpanan dokumen dalam jangka waktu tertentu berdasarkan peraturan perundang-undangan.
3. Terjadi sengketa hukum atau dugaan pemalsuan sertifikat yang memerlukan bukti catatan audit asli.

---

*Disetujui untuk RANCANGAN TEKNIS:*  
*DPD JULEHA Kabupaten Brebes*
