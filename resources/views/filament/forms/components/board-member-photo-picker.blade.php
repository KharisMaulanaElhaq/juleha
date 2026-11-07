@php
    $apiKey = (string) config('services.google_picker.api_key', '');
    $clientId = (string) config('services.google_picker.client_id', '');
    $appId = (string) config('services.google_picker.app_id', '');

    $isGoogleDriveConfigured = filled($apiKey) && filled($clientId) && filled($appId);
@endphp

<div
    x-data="boardMemberPhotoPicker({
        apiKey: {{ \Illuminate\Support\Js::from($apiKey) }},
        clientId: {{ \Illuminate\Support\Js::from($clientId) }},
        appId: {{ \Illuminate\Support\Js::from($appId) }},
        isConfigured: {{ \Illuminate\Support\Js::from($isGoogleDriveConfigured) }}
    })"
    class="w-full"
    style="margin-bottom: 0.75rem;"
>
    <!-- Baris Tombol Pemilihan Foto Sejajar (Filament Native Buttons) -->
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
        <!-- Tombol 1: Pilih dari Komputer -->
        <x-filament::button
            type="button"
            color="gray"
            icon="heroicon-m-computer-desktop"
            x-on:click="openComputerPicker()"
        >
            Pilih dari Komputer
        </x-filament::button>

        @if ($isGoogleDriveConfigured)
            <!-- Tombol 2: Pilih dari Google Drive -->
            <x-filament::button
                type="button"
                color="success"
                x-on:click="pickFromGoogleDrive()"
                ::disabled="isLoading"
            >
                <x-slot name="icon">
                    <template x-if="!isLoading">
                        <svg width="18" height="18" style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; display: inline-block; vertical-align: middle;" viewBox="0 0 87.3 78" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8H0c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
                            <path d="M43.65 25 29.9 1.2c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44C.4 49.9 0 51.45 0 53h27.45z" fill="#00ac47"/>
                            <path d="M73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5H59.85l6.1 10.55z" fill="#ea4335"/>
                            <path d="M43.65 25 57.4 1.2C56.05.4 54.5 0 52.95 0H34.35c-1.55 0-3.1.4-4.45 1.2z" fill="#00832d"/>
                            <path d="M59.85 53H27.45L13.75 76.8c1.35.8 2.9 1.2 4.45 1.2h50.9c1.55 0 3.1-.4 4.45-1.2z" fill="#2684fc"/>
                            <path d="M73.4 26.5 60.7 4.5C59.9 3.1 58.75 2 57.4 1.2L43.65 25l16.2 28h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/>
                        </svg>
                    </template>
                </x-slot>
                <span x-text="isLoading ? statusMessage : 'Pilih dari Google Drive'"></span>
            </x-filament::button>
        @else
            <!-- Keterangan jika Google Drive belum dikonfigurasi -->
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #b45309; background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 0.5rem; padding: 0.5rem 0.75rem;">
                <svg width="16" height="16" style="width: 16px; height: 16px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>Fitur Google Drive belum dikonfigurasi, gunakan Pilih dari komputer.</span>
            </div>
        @endif
    </div>

    <!-- Kotak Notifikasi / Error dengan Tombol Coba Lagi -->
    <div
        x-show="errorMessage"
        x-cloak
        style="margin-top: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #fecaca; background-color: #fef2f2; color: #991b1b; font-size: 0.875rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;"
        role="alert"
    >
        <div style="display: flex; align-items: flex-start; gap: 0.625rem;">
            <svg width="20" height="20" style="width: 20px; height: 20px; flex-shrink: 0; color: #ef4444; margin-top: 2px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <div>
                <span style="font-weight: 600;" x-text="errorMessage"></span>
                <p style="font-size: 0.75rem; color: #dc2626; margin-top: 2px;" x-show="errorDetail" x-text="errorDetail"></p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <x-filament::button
                type="button"
                color="danger"
                size="xs"
                x-on:click="retryAction()"
            >
                Coba Lagi
            </x-filament::button>
            <x-filament::button
                type="button"
                color="gray"
                size="xs"
                x-on:click="errorMessage = null; errorDetail = null"
            >
                Tutup
            </x-filament::button>
        </div>
    </div>
</div>

<script>
function boardMemberPhotoPicker(config) {
    return {
        apiKey: config.apiKey || '',
        clientId: config.clientId || '',
        appId: config.appId || '',
        isConfigured: !!config.isConfigured,
        isLoading: false,
        statusMessage: 'Menghubungkan...',
        errorMessage: null,
        errorDetail: null,
        lastAction: null,

        // Buka pemilih berkas komputer bawaan FilePond
        openComputerPicker() {
            this.errorMessage = null;
            this.errorDetail = null;

            const uploadWrapper = document.querySelector('.board-member-photo-upload');
            const inputEl = uploadWrapper ? uploadWrapper.querySelector('input[type="file"]') : null;
            if (!inputEl) {
                this.showError('Komponen upload foto belum siap.', 'Silakan muat ulang halaman.');
                return;
            }

            // Bersihkan berkas lama jika ada agar pengguna leluasa memilih foto baru
            const alpineEl = uploadWrapper.hasAttribute('x-data')
                ? uploadWrapper
                : (uploadWrapper.querySelector('[x-data]') || uploadWrapper.closest('[x-data]'));
            const alpine = (alpineEl && window.Alpine) ? window.Alpine.$data(alpineEl) : null;
            const pond = alpine?.pond || (window.FilePond ? window.FilePond.find(inputEl) : null);

            if (pond && typeof pond.removeFiles === 'function' && pond.getFiles().length > 0) {
                pond.removeFiles();
            }

            inputEl.click();
        },

        // Coba aksi terakhir jika terjadi galat
        retryAction() {
            this.errorMessage = null;
            this.errorDetail = null;
            if (this.lastAction === 'drive') {
                this.pickFromGoogleDrive();
            } else {
                this.openComputerPicker();
            }
        },

        // Tampilkan pesan kesalahan Bahasa Indonesia
        showError(message, detail = null) {
            this.isLoading = false;
            this.statusMessage = '';
            this.errorMessage = message;
            this.errorDetail = detail;
        },

        // Alur Lengkap Google Identity Services & Google Picker
        async pickFromGoogleDrive() {
            this.lastAction = 'drive';
            this.errorMessage = null;
            this.errorDetail = null;

            if (!this.isConfigured || !this.apiKey || !this.clientId) {
                this.showError('Konfigurasi Google Drive belum lengkap (API Key atau Client ID belum diatur).', 'Gunakan "Pilih dari Komputer".');
                return;
            }

            this.isLoading = true;
            this.statusMessage = 'Memuat Google SDK...';

            try {
                // 1. Muat skrip Google secara LAZY (hanya saat tombol diklik)
                await this.loadGoogleScripts();

                this.statusMessage = 'Menghubungkan Akun...';

                // 2. Minta token akses via Google Identity Services (token client)
                const accessToken = await this.requestAccessToken();

                if (!accessToken) {
                    this.isLoading = false;
                    return;
                }

                this.statusMessage = 'Membuka Google Picker...';

                // 3. Tampilkan Google Picker
                await this.showPicker(accessToken);

            } catch (err) {
                console.error('[GooglePicker Error]', err);
                let message = 'Terjadi kesalahan saat menghubungkan ke Google Drive.';
                let detail = err.message || null;

                if (err.message && err.message.includes('popup_closed_by_user')) {
                    message = 'Jendela pemilihan akun Google ditutup sebelum selesai.';
                    detail = 'Silakan klik "Coba Lagi" untuk masuk kembali.';
                } else if (err.message && err.message.includes('access_denied')) {
                    message = 'Izin akses Google Drive ditolak oleh pengguna.';
                    detail = 'Diperlukan izin drive.file untuk memilih foto.';
                } else if (err.message && (err.message.includes('popup_blocked') || err.message.includes('blocked'))) {
                    message = 'Jendela popup Google diblokir oleh peramban.';
                    detail = 'Izinkan popup di peramban Anda untuk situs ini.';
                }

                this.showError(message, detail);
            }
        },

        // Muat Google Identity Services (GSI) dan Google API (gapi) secara dinamis
        async loadGoogleScripts() {
            const loadScript = (src, id) => {
                return new Promise((resolve, reject) => {
                    if (document.getElementById(id)) {
                        resolve();
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = src;
                    script.id = id;
                    script.async = true;
                    script.defer = true;
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error(`Gagal memuat skrip: ${src}. Periksa koneksi internet.`));
                    document.head.appendChild(script);
                });
            };

            // Muat GIS client dan GAPI client
            await Promise.all([
                loadScript('https://accounts.google.com/gsi/client', 'google-gsi-client'),
                loadScript('https://apis.google.com/js/api.js', 'google-api-client'),
            ]);

            // Muat komponen picker dari GAPI
            await new Promise((resolve, reject) => {
                if (window.gapi && window.gapi.load) {
                    window.gapi.load('picker', {
                        callback: resolve,
                        onerror: () => reject(new Error('Gagal memuat modul Google Picker API.')),
                    });
                } else {
                    reject(new Error('Google API Client (gapi) tidak terdefinisi.'));
                }
            });
        },

        // Dapatkan Access Token hanya di memori browser
        requestAccessToken() {
            return new Promise((resolve, reject) => {
                if (!window.google || !window.google.accounts || !window.google.accounts.oauth2) {
                    reject(new Error('Google Identity Services SDK tidak tersedia.'));
                    return;
                }

                let isCallbackCalled = false;

                const tokenClient = window.google.accounts.oauth2.initTokenClient({
                    client_id: this.clientId,
                    scope: 'https://www.googleapis.com/auth/drive.file',
                    callback: (response) => {
                        isCallbackCalled = true;
                        if (response.error !== undefined) {
                            reject(new Error(response.error_description || response.error));
                            return;
                        }
                        resolve(response.access_token);
                    },
                    error_callback: (err) => {
                        isCallbackCalled = true;
                        reject(new Error(err.message || 'Gagal meminta izin ke Google.'));
                    },
                });

                tokenClient.requestAccessToken({ prompt: '' });
            });
        },

        // Tampilkan modal Google Picker untuk memilih berkas gambar
        showPicker(accessToken) {
            return new Promise((resolve, reject) => {
                if (!window.google || !window.google.picker) {
                    reject(new Error('Google Picker API belum siap.'));
                    return;
                }

                // View 1: Telusuri Folder Drive Saya (bisa membuka folder buatan pengguna)
                const folderView = new window.google.picker.DocsView()
                    .setIncludeFolders(true)
                    .setSelectFolderEnabled(false)
                    .setMimeTypes('image/jpeg,image/png,image/webp,application/vnd.google-apps.folder')
                    .setMode(window.google.picker.DocsViewMode.GRID);

                // View 2: Semua Foto (pencarian cepat seluruh gambar di Drive)
                const allImagesView = new window.google.picker.DocsView(window.google.picker.ViewId.DOCS_IMAGES)
                    .setMode(window.google.picker.DocsViewMode.GRID);

                const builder = new window.google.picker.PickerBuilder()
                    .addView(folderView)
                    .addView(allImagesView)
                    .setOAuthToken(accessToken)
                    .setDeveloperKey(this.apiKey)
                    .setLocale('id')
                    .setTitle('Pilih Foto Profil Pengurus (Buka Folder atau Pilih Foto)')
                    .setCallback(async (data) => {
                        if (data[window.google.picker.Response.ACTION] === window.google.picker.Action.PICKED) {
                            const doc = data[window.google.picker.Response.DOCUMENTS][0];
                            try {
                                await this.processPickedFile(doc, accessToken);
                                resolve();
                            } catch (e) {
                                reject(e);
                            }
                        } else if (data[window.google.picker.Response.ACTION] === window.google.picker.Action.CANCEL) {
                            this.isLoading = false;
                            this.statusMessage = '';
                            // Lepaskan token akses setelah pembatalan
                            this.revokeToken(accessToken);
                            resolve();
                        }
                    });

                if (this.appId) {
                    builder.setAppId(this.appId);
                }

                const picker = builder.build();
                picker.setVisible(true);
            });
        },

        // Unduh berkas di browser via Drive API dan masukkan ke FilePond
        async processPickedFile(doc, accessToken) {
            this.statusMessage = 'Mengunduh foto dari Google Drive...';

            const fileId = doc[window.google.picker.Document.ID];
            const fileName = doc[window.google.picker.Document.NAME] || 'foto-pengurus.jpg';
            const mimeType = doc[window.google.picker.Document.MIME_TYPE] || '';
            const sizeBytes = doc[window.google.picker.Document.SIZE_BYTES] || 0;

            // 1. Validasi MIME type di sisi klien
            const allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (mimeType && !allowedMimes.includes(mimeType)) {
                this.revokeToken(accessToken);
                throw new Error('Berkas yang dipilih bukan berkas gambar yang sah (hanya format JPG, PNG, atau WebP yang didukung).');
            }

            // 2. Validasi ukuran (maksimal 8 MB = 8388608 bytes)
            if (sizeBytes > 8388608) {
                this.revokeToken(accessToken);
                throw new Error('Ukuran berkas melebihi batas 8 MB. Harap pilih foto lain yang lebih kecil.');
            }

            try {
                // 3. Browser mengunduh binary langsung dari Drive API dengan Authorization Bearer
                const response = await fetch(`https://www.googleapis.com/drive/v3/files/${fileId}?alt=media`, {
                    headers: {
                        'Authorization': `Bearer ${accessToken}`,
                    },
                });

                if (!response.ok) {
                    throw new Error(`Gagal mengunduh berkas dari Google Drive (Status HTTP ${response.status}). Periksa koneksi internet atau izin berkas.`);
                }

                const blob = await response.blob();

                // 4. Periksa ukuran berkas hasil unduhan
                if (blob.size > 8388608) {
                    throw new Error('Ukuran berkas foto melebihi batas maksimal 8 MB.');
                }

                // Buat objek File standar browser
                const cleanType = blob.type && allowedMimes.includes(blob.type) ? blob.type : (mimeType || 'image/jpeg');
                const file = new File([blob], fileName, { type: cleanType });

                this.statusMessage = 'Membuka jendela pemotong foto...';

                // 5. Masukkan secara programatik ke FilePond Filament
                await this.injectFileToFilament(file);

                this.isLoading = false;
                this.statusMessage = '';

            } finally {
                // 6. Selalu lepas (revoke) token Google setelah berkas diambil
                this.revokeToken(accessToken);
            }
        },

        // Lepas token akses Google untuk keamanan
        revokeToken(token) {
            try {
                if (token && window.google?.accounts?.oauth2?.revoke) {
                    window.google.accounts.oauth2.revoke(token, () => {
                        // Token berhasil dilepas dari memori
                    });
                }
            } catch (e) {
                // Ignore revoke errors
            }
        },

        // Masukkan objek File ke FilePond Filament dan buka jendela crop
        async injectFileToFilament(file) {
            const uploadWrapper = document.querySelector('.board-member-photo-upload');
            if (!uploadWrapper) {
                throw new Error('Elemen kolom foto tidak ditemukan pada halaman formulir.');
            }

            const inputEl = uploadWrapper.querySelector('input[type="file"]');

            // 1. Cari instance Alpine & FilePond dengan retry jika sedang proses inisialisasi
            let alpine = null;
            let pond = null;

            for (let attempt = 0; attempt < 15; attempt++) {
                const alpineEl = uploadWrapper.hasAttribute('x-data')
                    ? uploadWrapper
                    : (uploadWrapper.querySelector('[x-data]') || uploadWrapper.closest('[x-data]'));

                if (alpineEl && window.Alpine) {
                    alpine = window.Alpine.$data(alpineEl);
                    if (alpine?.pond) {
                        pond = alpine.pond;
                        break;
                    }
                }

                if (window.FilePond && inputEl) {
                    pond = window.FilePond.find(inputEl) || window.FilePond.find(uploadWrapper.querySelector('.filepond--root'));
                    if (pond) {
                        break;
                    }
                }

                await new Promise((r) => setTimeout(r, 100));
            }

            // 2. Jika instance FilePond ditemukan langsung
            if (pond) {
                // Bersihkan berkas sebelumnya jika ada
                if (typeof pond.removeFiles === 'function') {
                    pond.removeFiles();
                }

                // Tambahkan file ke FilePond (tangani jika beforeAddFile menahan untuk buka editor)
                try {
                    await pond.addFile(file);
                } catch (addErr) {
                    console.debug('[GooglePicker] addFile handled by editor:', addErr);
                }

                // Buka jendela pemotong foto (Cropper) rasio 4:5 jika belum otomatis terbuka
                if (alpine && typeof alpine.loadEditor === 'function' && !alpine.isEditorOpen) {
                    alpine.loadEditor(file);
                }
                return;
            }

            // 3. Fallback: simulasikan event pemilihan file via DataTransfer browser standar
            if (inputEl && window.DataTransfer) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    inputEl.files = dt.files;
                    inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                    inputEl.dispatchEvent(new Event('input', { bubbles: true }));

                    // Tunggu sebentar agar FilePond merespons dan buka editor jika belum aktif
                    await new Promise((r) => setTimeout(r, 300));
                    if (alpine && typeof alpine.loadEditor === 'function' && !alpine.isEditorOpen) {
                        alpine.loadEditor(file);
                    }
                    return;
                } catch (dtErr) {
                    console.warn('[GooglePicker] DataTransfer fallback failed:', dtErr);
                }
            }

            throw new Error('Komponen pengunggah foto (FilePond) belum aktif. Silakan muat ulang halaman.');
        }
    };
}
</script>

