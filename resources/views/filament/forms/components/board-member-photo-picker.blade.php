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
    class="w-full space-y-3"
>
    <!-- Baris Tombol Pemilihan Foto Sejajar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
        <!-- Tombol 1: Pilih dari Komputer -->
        <button
            type="button"
            x-on:click="openComputerPicker()"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg shadow-sm border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-primary-500"
        >
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3" />
            </svg>
            <span>Pilih dari Komputer</span>
        </button>

        @if ($isGoogleDriveConfigured)
            <!-- Tombol 2: Pilih dari Google Drive -->
            <button
                type="button"
                x-on:click="pickFromGoogleDrive()"
                :disabled="isLoading"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg shadow-sm border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 bg-emerald-600 text-white border-transparent hover:bg-emerald-700 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <template x-if="!isLoading">
                    <!-- Google Drive Icon -->
                    <svg class="w-5 h-5" viewBox="0 0 87.3 78" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8H0c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
                        <path d="M43.65 25 29.9 1.2c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44C.4 49.9 0 51.45 0 53h27.45z" fill="#00ac47"/>
                        <path d="M73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5H59.85l6.1 10.55z" fill="#ea4335"/>
                        <path d="M43.65 25 57.4 1.2C56.05.4 54.5 0 52.95 0H34.35c-1.55 0-3.1.4-4.45 1.2z" fill="#00832d"/>
                        <path d="M59.85 53H27.45L13.75 76.8c1.35.8 2.9 1.2 4.45 1.2h50.9c1.55 0 3.1-.4 4.45-1.2z" fill="#2684fc"/>
                        <path d="M73.4 26.5 60.7 4.5C59.9 3.1 58.75 2 57.4 1.2L43.65 25l16.2 28h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/>
                    </svg>
                </template>
                <template x-if="isLoading">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </template>
                <span x-text="isLoading ? statusMessage : 'Pilih dari Google Drive'"></span>
            </button>
        @else
            <!-- Keterangan jika Google Drive belum dikonfigurasi -->
            <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-lg px-3 py-2">
                <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
        class="p-3.5 rounded-lg border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-950/40 text-red-800 dark:text-red-300 text-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm"
        role="alert"
    >
        <div class="flex items-start gap-2.5">
            <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <div>
                <span class="font-medium" x-text="errorMessage"></span>
                <p class="text-xs text-red-600 dark:text-red-400 mt-0.5" x-show="errorDetail" x-text="errorDetail"></p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <button
                type="button"
                x-on:click="retryAction()"
                class="px-2.5 py-1 text-xs font-semibold rounded bg-red-600 hover:bg-red-700 text-white transition-colors shadow-xs"
            >
                Coba Lagi
            </button>
            <button
                type="button"
                x-on:click="errorMessage = null; errorDetail = null"
                class="px-2 py-1 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                Tutup
            </button>
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

            // Cari input file di container photo upload
            const inputEl = document.querySelector('.board-member-photo-upload input[type="file"]');
            if (inputEl) {
                inputEl.click();
            } else {
                this.showError('Komponen upload foto belum siap.', 'Silakan muat ulang halaman.');
            }
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

                // DocsView khusus gambar JPG, PNG, WebP
                const docsView = new window.google.picker.DocsView(window.google.picker.ViewId.DOCS_IMAGES)
                    .setMimeTypes('image/jpeg,image/png,image/webp')
                    .setMode(window.google.picker.DocsViewMode.LIST);

                const builder = new window.google.picker.PickerBuilder()
                    .addView(docsView)
                    .setOAuthToken(accessToken)
                    .setDeveloperKey(this.apiKey)
                    .setLocale('id')
                    .setTitle('Pilih Foto Profil Pengurus (JPG, PNG, WebP)')
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
            if (!inputEl) {
                throw new Error('Input unggah foto belum siap.');
            }

            // Dapatkan instance FilePond
            let pond = window.FilePond ? window.FilePond.find(inputEl) : null;
            if (!pond) {
                // Tunggu 200ms jika FilePond sedang inisialisasi
                await new Promise((r) => setTimeout(r, 200));
                pond = window.FilePond ? window.FilePond.find(inputEl) : null;
            }

            if (!pond) {
                throw new Error('Komponen pengunggah foto (FilePond) belum aktif.');
            }

            // Bersihkan berkas sebelumnya jika ada
            pond.removeFiles();

            // Tambahkan file ke FilePond
            const addedItem = await pond.addFile(file);

            // Buka jendela pemotong foto (Cropper) rasio 4:5 secara langsung
            const pondAlpineEl = uploadWrapper.querySelector('[x-data]');
            if (pondAlpineEl && window.Alpine) {
                const alpineData = window.Alpine.$data(pondAlpineEl);
                if (alpineData && typeof alpineData.loadEditor === 'function' && !alpineData.isEditorOpen) {
                    alpineData.loadEditor(file);
                }
            }
        }
    };
}
</script>

