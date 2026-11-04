# Panduan Konfigurasi Google Picker API untuk Foto Pengurus

Dokumen ini menjelaskan langkah-langkah penyiapan Google Cloud Console untuk fitur **"Pilih dari Google Drive"** pada modul Struktur Pengurus DPD JULEHA Brebes.

Fitur ini berjalan **100% di browser administrator** (*client-side only*) menggunakan **Google Identity Services (GIS)** dan **Google Picker API**. Server Laravel tidak pernah menerima token Google, tidak menyimpan kredensial sensitif (*zero Client Secret*), dan tidak membuat permintaan keluar ke alamat yang dimasukkan pengguna (menghilangkan risiko SSRF).

---

## 1. Ikhtisar Arsitektur Keamanan

- **Scope OAuth**: Hanya `https://www.googleapis.com/auth/drive.file` (hanya dapat mengakses berkas yang dipilih langsung oleh pengguna via dialog Google Picker).
- **Penyimpanan Token**: Token akses (*access token*) hanya berada di memori peramban sementara (`window`) dan langsung dicabut (*revoked*) setelah berkas berhasil diambil.
- **Kredensial Server**: Hanya membutuhkan **API Key**, **OAuth Client ID**, dan **Project Number**. **TIDAK MEMBUTUHKAN DAN DILARANG MENGGUNAKAN `Client Secret`** pada fitur ini.
- **Pemuatan Skrip Dinamis (Lazy Loading)**: Skrip Google (`gsi/client` dan `apis.google.com/js/api.js`) hanya dimuat ketika tombol "Pilih dari Google Drive" diklik di panel admin. Tidak ada skrip Google pihak ketiga yang dimuat pada halaman publik atau portal peserta.

---

## 2. Variabel Lingkungan (.env)

Tambahkan tiga variabel berikut ke berkas `.env` aplikasi Anda:

```dotenv
# Google Picker & Drive API (Client-side browser only)
GOOGLE_PICKER_API_KEY=AIzaSy...
GOOGLE_PICKER_CLIENT_ID=xxxxxxxxxxxx-xxxxxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_PICKER_APP_ID=123456789012
```

> **Catatan Penting:**  
> Jika salah satu dari ketiga variabel di atas kosong, tombol "Pilih dari Google Drive" di formulir pengurus otomatis disembunyikan dan sistem menampilkan teks panduan:  
> *"Fitur Google Drive belum dikonfigurasi, gunakan Pilih dari komputer."*

---

## 3. Langkah Pembuatan di Google Cloud Console

### Langkah A: Buat atau Pilih Project
1. Buka [Google Cloud Console](https://console.cloud.google.com/).
2. Pilih project yang sudah ada (misal: project yang sama untuk Google Login OAuth peserta) atau buat project baru dengan nama `DPD JULEHA Brebes`.
3. Catat **Project Number** (Nomor Proyek):
   - Klik menu **Dashboard** / **Overview**.
   - Pada kartu **Project info**, salin angka pada kolom **Project number** (misal: `718293049182`).
   - Masukkan angka ini ke `GOOGLE_PICKER_APP_ID`.

---

### Langkah B: Aktifkan API yang Dibutuhkan
1. Pada menu navigasi kiri, pilih **APIs & Services** > **Library** (Pustaka).
2. Cari dan aktifkan dua API berikut:
   - **Google Drive API** (Klik *Enable*).
   - **Google Picker API** (Klik *Enable*).

---

### Langkah C: Konfigurasi Layar Persetujuan OAuth (OAuth Consent Screen)
1. Buka **APIs & Services** > **OAuth consent screen**.
2. Pilih User Type: **External** (Eksternal), lalu klik **Create**.
3. Isi informasi dasar aplikasi:
   - **App name**: `DPD JULEHA Brebes`
   - **User support email**: Email sekretariat/admin.
   - **Developer contact information**: Email pengembang/admin.
4. Pada tahapan **Scopes**:
   - Klik **Add or Remove Scopes**.
   - Tambahkan scope: `https://www.googleapis.com/auth/drive.file` (View and manage Google Drive files and folders that you have opened or created with this app).
   - Klik **Update** dan simpan.
5. Pada tahapan **Test users**:
   - Jika status aplikasi masih **Testing**, tambahkan email Google administrator yang akan mengunggah foto pengurus (misal: `dpdjulehabrebesofficial@gmail.com` dan email admin lainnya).

---

### Langkah D: Buat OAuth 2.0 Client ID (Web Application)
1. Buka **APIs & Services** > **Credentials**.
2. Klik **Create Credentials** > **OAuth client ID**.
3. Pilih Application Type: **Web application**.
4. Beri nama: `Google Picker - DPD JULEHA Brebes`.
5. Pada bagian **Authorized JavaScript origins** (Asal JavaScript yang diotorisasi), tambahkan domain website Anda (tanpa garis miring di akhir):
   - Lingkungan Lokal: `http://localhost`, `http://127.0.0.1:8000`, `http://juleha-brebes.test`
   - Lingkungan Produksi: `https://julehabrebes.web.id`
6. Kosongkan bagian *Authorized redirect URIs* (karena Google Picker GIS menggunakan mekanisme dialog in-memory tanpa pengalihan URL).
7. Klik **Create**.
8. Salin string **Client ID** (akhiran `.apps.googleusercontent.com`) dan masukkan ke `GOOGLE_PICKER_CLIENT_ID` di `.env`.

---

### Langkah E: Buat API Key & Batasi Keamanannya (Restricted API Key)
1. Pada menu **Credentials**, klik **Create Credentials** > **API key**.
2. Salin nilai kunci yang muncul (awalan `AIzaSy...`).
3. **Wajib Membatasi API Key (Security Best Practices):**
   - Klik tombol **Edit** (ikon pensil) pada API key yang baru dibuat.
   - Beri nama: `Browser Picker API Key`.
   - Pada bagian **Set application restrictions**:
     - Pilih **Websites** (Perujuk HTTP).
     - Tambahkan batasan URL perujuk website Anda:
       - `https://julehabrebes.web.id/*`
       - `http://localhost/*` (jika ingin digunakan di lokal).
       - `http://127.0.0.1:8000/*`
   - Pada bagian **API restrictions**:
     - Pilih **Restrict key**.
     - Centang hanya:
       - **Google Drive API**
       - **Google Picker API**
   - Klik **Save** (Simpan).
4. Masukkan nilai kunci ini ke `GOOGLE_PICKER_API_KEY` di `.env`.

---

## 4. Status Aplikasi: Testing vs In Production

| Aspek | Mode Testing (Pengujian) | Mode In Production (Produksi) |
|---|---|---|
| **Akses Pengguna** | Hanya akun Google yang terdaftar di *Test users* yang dapat memilih foto. | Semua pengurus/admin yang memiliki akun Google dapat memilih foto. |
| **Layar Peringatan** | Muncul layar *"Google hasn't verified this app"*. Pengguna klik **Advanced** > **Go to DPD JULEHA Brebes (unsafe)** untuk melanjutkan. | Tidak muncul layar peringatan setelah diverifikasi oleh Google. |
| **Rekomendasi** | Gunakan saat pengembangan atau jika admin pengunggah foto hanya 1-3 orang pengurus inti yang emailnya didaftarkan di *Test users*. | Jika aplikasi dipakai oleh banyak tim admin, ajukan verifikasi aplikasi ke Google. |

---

## 5. Daftar Kesalahan Umum & Solusinya

### 1. `origin_mismatch` atau `The given origin is not allowed for the given client ID`
- **Penyebab**: Domain yang sedang diakses (misal: `https://julehabrebes.web.id`) belum didaftarkan pada kolom **Authorized JavaScript origins** di OAuth Client ID.
- **Solusi**: Buka Google Cloud Console > Credentials > edit Web Client ID > tambahkan skema dan domain lengkap yang sesuai (perhatikan `http://` vs `https://` dan port).

### 2. `API_KEY_INVALID` atau `Requests from this HTTP referrer are blocked`
- **Penyebab**: API key dibatasi dengan perujuk HTTP yang tidak cocok dengan domain situs, atau perujuk belum menyertakan wildcard `/*`.
- **Solusi**: Pastikan format perujuk di API key adalah `https://julehabrebes.web.id/*`.

### 3. Jendela Popup Diblokir Browser (`popup_blocked_by_browser`)
- **Penyebab**: Peramban memblokir jendela popup otomatis otorisasi Google.
- **Solusi**: Klik ikon peringatan popup di bilah alamat peramban (address bar) dan pilih "Selalu izinkan pop-up dari situs ini", lalu klik tombol **Coba Lagi**.

### 4. `access_denied` / `User canceled authorization`
- **Penyebab**: Pengguna menutup jendela login Google atau membatalkan permintaan izin.
- **Solusi**: Klik tombol **Coba Lagi** dan pastikan menyetujui akses saat dialog muncul.

### 5. `The API (Drive API / Picker API) has not been used or is disabled`
- **Penyebab**: Salah satu atau kedua API belum diaktifkan di Google Cloud Console.
- **Solusi**: Buka APIs & Services > Library > cari "Google Drive API" dan "Google Picker API" > klik **Enable**.

### 6. `Developer Key is invalid`
- **Penyebab**: Variabel `GOOGLE_PICKER_API_KEY` di `.env` salah salin atau API restriction memblokir Google Picker API.
- **Solusi**: Periksa kembali nilai API Key di `.env` dan pastikan Picker API dicentang pada batasan API key.

