# Panduan Konfigurasi Google OAuth 2.0 (Portal Peserta JULEHA)

Dokumen ini menjelaskan langkah-langkah pembuatan dan konfigurasi **Google OAuth Client ID** di Google Cloud Console untuk autentikasi portal peserta DPD JULEHA Kabupaten Brebes.

---

## 1. Persyaratan Awal

1. Akun Google organisasi (misal: `dpdjulehabrebesofficial@gmail.com`) atau akun Google Cloud administrator.
2. Akses ke [Google Cloud Console](https://console.cloud.google.com/).

---

## 2. Langkah Pembuatan Proyek & Konfigurasi OAuth

### Langkah 1: Buat Proyek Baru di Google Cloud
1. Masuk ke [Google Cloud Console](https://console.cloud.google.com/).
2. Pada menu dropdown proyek di bagian atas, pilih **New Project**.
3. Isi nama proyek, misalnya: `Juleha Brebes Portal`.
4. Klik **Create** dan tunggu hingga proyek selesai disiapkan.

### Langkah 2: Konfigurasi OAuth Consent Screen
1. Buka menu **APIs & Services** > **OAuth consent screen**.
2. Pilih jenis pengguna:
   - Pilih **External** (agar masyarakat umum/peserta dengan akun Gmail apa saja dapat mendaftar).
   - Klik **Create**.
3. Lengkapi informasi aplikasi:
   - **App name**: `DPD JULEHA Kabupaten Brebes`
   - **User support email**: `dpdjulehabrebesofficial@gmail.com`
   - **App logo**: Unggah logo resmi JULEHA Brebes (`assets/logo-juleha-brebes.png`).
   - **App domain**:
     - *Application home page*: `https://julehabrebes.org` (atau domain produksi).
     - *Application privacy policy link*: `https://julehabrebes.org/kebijakan-privasi`
     - *Application terms of service link*: `https://julehabrebes.org/tentang`
   - **Developer contact information**: Email administrator teknis.
4. Klik **Save and Continue**.

### Langkah 3: Konfigurasi Scopes (Hak Akses Minimal)
Portal Peserta JULEHA menerapkan prinsip **data minimalis**:
1. Pada halaman **Scopes**, klik **Add or Remove Scopes**.
2. Centang **hanya 3 scope standar**:
   - `.../auth/userinfo.email` (melihat alamat email utama).
   - `.../auth/userinfo.profile` (melihat info profil dasar dan foto avatar).
   - `openid` (autentikasi OpenID Connect).
3. **Jangan menambahkan scope sensitif** seperti Google Drive, Kalender, atau Kontak.
4. Klik **Update** lalu **Save and Continue**.

### Langkah 4: Publikasikan Aplikasi (Publishing Status: In Production)
1. Secara default, status aplikasi adalah **Testing** (hanya bisa diakses oleh 100 email uji coba).
2. Di halaman **OAuth consent screen**, klik tombol **PUBLISH APP** agar berubah menjadi **In Production**.
3. Dengan hanya menggunakan scope dasar (`openid`, `email`, `profile`), aplikasi **tidak memerlukan proses verifikasi rumit (CASA / security audit)** dari Google dan langsung bisa digunakan oleh seluruh peserta.

### Langkah 5: Buat OAuth 2.0 Client ID Credentials
1. Masuk ke **APIs & Services** > **Credentials**.
2. Klik **+ CREATE CREDENTIALS** > **OAuth client ID**.
3. Pilih Application type: **Web application**.
4. Isi Nama: `Juleha Brebes Web Client`.
5. Pada bagian **Authorized JavaScript origins**:
   - Pengembangan lokal: `http://localhost:8000` dan `http://127.0.0.1:8000`
   - Produksi: `https://julehabrebes.org` (dan `https://www.julehabrebes.org`)
6. Pada bagian **Authorized redirect URIs** (Sangat Penting):
   - Pengembangan lokal:
     - `http://localhost:8000/auth/google/callback`
     - `http://127.0.0.1:8000/auth/google/callback`
   - Produksi:
     - `https://julehabrebes.org/auth/google/callback`
7. Klik **CREATE**.
8. Salin **Client ID** dan **Client Secret** yang muncul pada pop-up dialog.

---

## 3. Konfigurasi Variabel Lingkungan (.env)

Buka file `.env` di server/komputer dan masukkan kredensial yang diperoleh (jangan pernah commit file `.env` ke Git repository):

```env
# URL Utama Website
APP_URL=http://localhost:8000

# Kredensial Google OAuth 2.0
GOOGLE_CLIENT_ID=isi-dengan-client-id-dari-google-cloud.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=isi-dengan-client-secret-dari-google-cloud
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Pastikan placeholder berikut juga tersedia di `.env.example`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

---

## 4. Alur Kerja Sistem & Pengamanan

1. Peserta mengklik **"Masuk dengan Google"** di `/masuk` atau `/daftar`.
2. Controller `App\Http\Controllers\Auth\GoogleAuthController` mengirim permintaan ke Google dengan scope `['openid', 'email', 'profile']`.
3. Setelah persetujuan pengguna, Google mengirim kode otorisasi ke `/auth/google/callback`.
4. Sistem memvalidasi bahwa email Google sudah diverifikasi (`email_verified == true`).
5. Akun baru **selalu diberi role `participant`** dan tidak dapat mengakses `/admin`.
6. Jika email sudah ada sebelumnya, akun Google ditautkan tanpa menaikkan atau mengubah role yang sudah ada (mencegah eskalasi hak akses OAuth).
7. Sesi login diperbarui (`session()->regenerate()`) untuk mencegah serangan *session fixation*.
