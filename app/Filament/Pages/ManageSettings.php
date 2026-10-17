<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManageSettings extends Page implements HasForms
{
    use HasUnsavedDataChangesAlert;
    use InteractsWithForms;

    protected static ?string $title = 'Pengaturan Situs';

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        // Hanya Super Admin yang berhak melihat dan mengubah pengaturan situs
        return $user && $user->role === 'super_admin';
    }

    public function mount(): void
    {
        $allSettings = Setting::all()->pluck('value', 'key')->toArray();

        // 1. Ekstraksi nomor WhatsApp (deteksi jika ada dua nomor tersambung)
        $rawWa = $allSettings['whatsapp_number'] ?? '082324448886';
        $secondaryWa = $allSettings['whatsapp_secondary'] ?? '';

        if (str_contains($rawWa, '-')) {
            $parts = explode('-', $rawWa, 2);
            $rawWa = trim($parts[0]);
            if (empty($secondaryWa)) {
                $secondaryWa = trim($parts[1]);
            }
        }

        // Tampilkan format ramah admin (08...)
        $primaryPhoneDisplay = $this->formatPhoneForDisplay($rawWa);
        $secondaryPhoneDisplay = $this->formatPhoneForDisplay($secondaryWa);

        // 2. Ekstraksi Misi (bisa dari format array JSON atau string baris-per-baris lama)
        $rawMission = $allSettings['mission'] ?? '';
        $missionItems = [];

        if (! empty($rawMission)) {
            $decoded = json_decode($rawMission, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_array($item) && isset($item['point'])) {
                        $missionItems[] = ['point' => $item['point']];
                    } elseif (is_string($item) && filled(trim($item))) {
                        $missionItems[] = ['point' => trim($item)];
                    }
                }
            } else {
                // Format teks lama dengan baris baru
                $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $rawMission));
                foreach ($lines as $line) {
                    $cleaned = trim(ltrim($line, "*-•0123456789. \t"));
                    if (filled($cleaned)) {
                        $missionItems[] = ['point' => $cleaned];
                    }
                }
            }
        }

        if (empty($missionItems)) {
            $missionItems = [
                ['point' => 'Membina dan mengedukasi juru sembelih tentang fiqih penyembelihan syar’i Madzhab Syafi’i dan fatwa MUI.'],
                ['point' => 'Menerapkan kaidah kesejahteraan hewan (animal welfare) dan higienitas penanganan daging.'],
                ['point' => 'Menyelenggarakan bimbingan teknis dan fasilitasi uji kompetensi bersertifikat BNSP.'],
                ['point' => 'Membangun sinergi dengan panitia kurban masjid, Rumah Pemotongan Hewan (RPH), dan dinas terkait.'],
            ];
        }

        $this->form->fill([
            'site_name' => $allSettings['site_name'] ?? 'DPD JULEHA Kabupaten Brebes',
            'whatsapp_number' => $primaryPhoneDisplay,
            'whatsapp_secondary' => $secondaryPhoneDisplay,
            'admin_email' => $allSettings['admin_email'] ?? 'dpdjulehabrebesofficial@gmail.com',
            'sk_legalitas' => $allSettings['sk_legalitas'] ?? 'AHU-0000118.AH.01.07.TAHUN 2019',
            'office_hours' => $allSettings['office_hours'] ?? 'Senin – Jumat, 08.00 – 15.00 WIB',
            'office_address' => trim($allSettings['office_address'] ?? 'Jalan Kaliluwih, Wanasari, Brebes, Jawa Tengah'),

            'vision' => $allSettings['vision'] ?? 'Terwujudnya tata kelola dan juru sembelih halal yang profesional, kompeten, dan beradab di Kabupaten Brebes.',
            'mission' => $missionItems,

            'counter_juleha' => (string) ($allSettings['counter_juleha'] ?? '250'),
            'counter_bimtek' => (string) ($allSettings['counter_bimtek'] ?? '12'),
            'counter_districts' => (string) ($allSettings['counter_districts'] ?? '17'),
            'counter_rph' => (string) ($allSettings['counter_rph'] ?? '8'),

            'social_instagram' => $allSettings['social_instagram'] ?? 'https://instagram.com/julehabrebes',
            'social_facebook' => $allSettings['social_facebook'] ?? 'https://facebook.com/julehabrebes',
            'social_youtube' => $allSettings['social_youtube'] ?? '',
            'social_tiktok' => $allSettings['social_tiktok'] ?? '',

            'allow_email_registration' => ($allSettings['allow_email_registration'] ?? '0') === '1',

            'board_period' => $allSettings['board_period'] ?? '2024-2029',
            'board_sk_note' => $allSettings['board_sk_note'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Pengaturan Situs DPD JULEHA')
                    ->persistTab()
                    ->id('settings-tabs')
                    ->tabs([
                        // Tab 1: Kontak & Sekretariat
                        Tab::make('Kontak & Sekretariat')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('Identitas Organisasi & Kontak')
                                    ->description('Informasi utama yang digunakan untuk identitas situs, komunikasi WhatsApp pendaftar, dan alamat resmi.')
                                    ->icon('heroicon-o-identification')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Nama Organisasi / Situs')
                                            ->required()
                                            ->placeholder('Contoh: DPD JULEHA Kabupaten Brebes')
                                            ->helperText('Nama resmi yang tampil di header publik dan judul halaman.')
                                            ->columnSpan(2),

                                        TextInput::make('whatsapp_number')
                                            ->label('WhatsApp Admin Utama (Wajib)')
                                            ->required()
                                            ->tel()
                                            ->placeholder('Contoh: 082324448886')
                                            ->helperText('Nomor WhatsApp resmi untuk tombol konsultasi dan link wa.me.')
                                            ->rules(['regex:/^[0-9+\s\-]+$/'])
                                            ->columnSpan(1),

                                        TextInput::make('whatsapp_secondary')
                                            ->label('WhatsApp Cadangan (Opsional)')
                                            ->tel()
                                            ->placeholder('Contoh: 0816333601')
                                            ->helperText('Nomor cadangan pengurus jika admin utama sedang berhalangan.')
                                            ->rules(['nullable', 'regex:/^[0-9+\s\-]+$/'])
                                            ->columnSpan(1),

                                        TextInput::make('admin_email')
                                            ->label('Email Notifikasi & Korespondensi')
                                            ->email()
                                            ->required()
                                            ->placeholder('Contoh: dpdjulehabrebesofficial@gmail.com')
                                            ->helperText('Email untuk menerima pemberitahuan pendaftaran baru.')
                                            ->columnSpan(1),

                                        TextInput::make('sk_legalitas')
                                            ->label('Dasar Hukum & Nomor SK')
                                            ->placeholder('Contoh: AHU-0000118.AH.01.07.TAHUN 2019')
                                            ->helperText('Nomor SK Kemenkumham atau SK Pengukuhan DPD JULEHA Brebes.')
                                            ->columnSpan(1),

                                        TextInput::make('office_hours')
                                            ->label('Hari & Jam Layanan Sekretariat')
                                            ->placeholder('Contoh: Senin – Jumat, 08.00 – 15.00 WIB')
                                            ->helperText('Jadwal pelayanan sekretariat yang ditampilkan pada top bar dan halaman kontak.')
                                            ->columnSpan(2),

                                        Textarea::make('office_address')
                                            ->label('Alamat Sekretariat Lengkap')
                                            ->rows(3)
                                            ->placeholder('Contoh: Jalan Kaliluwih, Wanasari, Kabupaten Brebes, Jawa Tengah 52252')
                                            ->helperText('Alamat lengkap kantor sekretariat yang dicantumkan pada halaman kontak dan footer.')
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        // Tab 2: Profil & Visi-Misi
                        Tab::make('Profil & Visi-Misi')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Visi Organisasi')
                                    ->description('Cita-cita luhur dan panduan arah masa depan DPD JULEHA Kabupaten Brebes.')
                                    ->icon('heroicon-o-eye')
                                    ->schema([
                                        Textarea::make('vision')
                                            ->label('Rumusan Visi')
                                            ->required()
                                            ->rows(3)
                                            ->placeholder('Tuliskan visi resmi organisasi...')
                                            ->helperText('Ditampilkan pada halaman Tentang Kami dan profil organisasi.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Poin-poin Misi Organisasi')
                                    ->description('Kelola poin misi secara terstruktur. Anda dapat menambah, menghapus, atau mengurutkan poin misi.')
                                    ->icon('heroicon-o-list-bullet')
                                    ->schema([
                                        Repeater::make('mission')
                                            ->label('Daftar Poin Misi')
                                            ->addActionLabel('+ Tambah Poin Misi')
                                            ->reorderable()
                                            ->cloneable(false)
                                            ->defaultItems(4)
                                            ->schema([
                                                TextInput::make('point')
                                                    ->label('Poin Misi')
                                                    ->placeholder('Contoh: Mengedukasi juru sembelih tentang fiqih syar’i...')
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Masa Bakti & SK Kepengurusan')
                                    ->description('Informasi periode kepengurusan resmi yang ditampilkan pada halaman Struktur Pengurus.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('board_period')
                                            ->label('Masa Bakti Kepengurusan')
                                            ->required()
                                            ->placeholder('Contoh: 2024-2029')
                                            ->helperText('Ditampilkan sebagai subjudul di halaman Struktur Pengurus: "Masa bakti 2024-2029".')
                                            ->columnSpan(1),

                                        TextInput::make('board_sk_note')
                                            ->label('Catatan SK Penetapan (Opsional)')
                                            ->placeholder('Contoh: Surat Keputusan DPP JULEHA Indonesia No. 012/SK/DPP/2024')
                                            ->helperText('Nomor atau tanggal SK penetapan kepengurusan. Ditampilkan di bawah daftar jika diisi.')
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        // Tab 3: Statistik Beranda
                        Tab::make('Statistik Beranda')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Angka Counter Beranda')
                                    ->description('Angka capaian yang ditampilkan dengan efek animasi counter pada halaman beranda publik.')
                                    ->icon('heroicon-o-presentation-chart-line')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('counter_juleha')
                                            ->label('Juleha Bersertifikat')
                                            ->numeric()
                                            ->required()
                                            ->placeholder('250')
                                            ->helperText('Jumlah juru sembelih yang telah terbina / bersertifikat.')
                                            ->columnSpan(1),

                                        TextInput::make('counter_bimtek')
                                            ->label('Angkatan Bimtek')
                                            ->numeric()
                                            ->required()
                                            ->placeholder('12')
                                            ->helperText('Jumlah angkatan bimbingan teknis yang telah selesai.')
                                            ->columnSpan(1),

                                        TextInput::make('counter_districts')
                                            ->label('Kecamatan Terjangkau')
                                            ->numeric()
                                            ->required()
                                            ->placeholder('17')
                                            ->helperText('Jumlah kecamatan jangkauan di Brebes (maksimal 17).')
                                            ->columnSpan(1),

                                        TextInput::make('counter_rph')
                                            ->label('Mitra RPH / TPH')
                                            ->numeric()
                                            ->required()
                                            ->placeholder('8')
                                            ->helperText('Jumlah mitra Rumah Pemotongan / Tempat Pemotongan Hewan.')
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        // Tab 4: Media Sosial
                        Tab::make('Media Sosial')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make('Tautan Akun Resmi Media Sosial')
                                    ->description('Tautan media sosial resmi DPD JULEHA Brebes yang ditampilkan di footer situs.')
                                    ->icon('heroicon-o-globe-alt')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('social_instagram')
                                            ->label('Instagram URL')
                                            ->url()
                                            ->placeholder('https://instagram.com/dpdjulehabrebes')
                                            ->helperText('URL profil Instagram resmi.')
                                            ->columnSpan(1),

                                        TextInput::make('social_facebook')
                                            ->label('Facebook URL')
                                            ->url()
                                            ->placeholder('https://facebook.com/dpdjulehabrebes')
                                            ->helperText('URL halaman / akun Facebook resmi.')
                                            ->columnSpan(1),

                                        TextInput::make('social_youtube')
                                            ->label('YouTube URL')
                                            ->url()
                                            ->placeholder('https://youtube.com/@julehabrebes')
                                            ->helperText('URL kanal YouTube resmi.')
                                            ->columnSpan(1),

                                        TextInput::make('social_tiktok')
                                            ->label('TikTok URL')
                                            ->url()
                                            ->placeholder('https://tiktok.com/@julehabrebes')
                                            ->helperText('URL akun TikTok resmi.')
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        // Tab 5: Portal Peserta
                        Tab::make('Portal Peserta')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make('Autentikasi & Pendaftaran Peserta')
                                    ->description('Pengaturan akses pendaftaran dan masuk ke Portal Peserta DPD JULEHA Brebes.')
                                    ->icon('heroicon-o-shield-check')
                                    ->schema([
                                        Toggle::make('allow_email_registration')
                                            ->label('Izinkan daftar dengan email dan password sebagai cadangan')
                                            ->helperText('Secara bawaan nonaktif (peserta wajib mendaftar via Google). Jika diaktifkan, formulir pendaftaran dengan email dan password biasa akan ditampilkan di portal peserta.')
                                            ->default(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment(Alignment::End)
                            ->sticky()
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->submit('save'),
        ];
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_site')
                ->label('Lihat Situs')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('home'))
                ->openUrlInNewTab(),

            Action::make('save_top')
                ->label('Simpan Perubahan')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // 1. Simpan Nama & Email & Operasional
        Setting::set('site_name', trim($state['site_name']));
        Setting::set('admin_email', trim($state['admin_email']));
        Setting::set('sk_legalitas', trim($state['sk_legalitas'] ?? ''));
        Setting::set('office_hours', trim($state['office_hours'] ?? ''));
        Setting::set('office_address', trim($state['office_address'] ?? ''));

        // 2. Normalisasi nomor WhatsApp ke 62xxxxxxxxxx
        $primaryPhone = $this->normalizePhoneNumber($state['whatsapp_number']);
        Setting::set('whatsapp_number', $primaryPhone);

        if (filled($state['whatsapp_secondary'] ?? null)) {
            $secondaryPhone = $this->normalizePhoneNumber($state['whatsapp_secondary']);
            Setting::set('whatsapp_secondary', $secondaryPhone);
        } else {
            Setting::set('whatsapp_secondary', '');
        }

        // 3. Simpan Visi & Misi
        Setting::set('vision', trim($state['vision'] ?? ''));

        // Simpan misi sebagai JSON dan juga format newline untuk kompatibilitas penuh
        $missionPoints = [];
        if (isset($state['mission']) && is_array($state['mission'])) {
            foreach ($state['mission'] as $item) {
                if (isset($item['point']) && filled(trim($item['point']))) {
                    $missionPoints[] = trim($item['point']);
                }
            }
        }
        Setting::set('mission', json_encode($missionPoints, JSON_UNESCAPED_UNICODE));

        // 4. Simpan Counter
        Setting::set('counter_juleha', (string) ($state['counter_juleha'] ?? '250'));
        Setting::set('counter_bimtek', (string) ($state['counter_bimtek'] ?? '12'));
        Setting::set('counter_districts', (string) ($state['counter_districts'] ?? '17'));
        Setting::set('counter_rph', (string) ($state['counter_rph'] ?? '8'));

        // 5. Simpan Media Sosial
        Setting::set('social_instagram', trim($state['social_instagram'] ?? ''));
        Setting::set('social_facebook', trim($state['social_facebook'] ?? ''));
        Setting::set('social_youtube', trim($state['social_youtube'] ?? ''));
        Setting::set('social_tiktok', trim($state['social_tiktok'] ?? ''));

        // 6. Simpan Pengaturan Portal Peserta
        Setting::set('allow_email_registration', ! empty($state['allow_email_registration']) ? '1' : '0');

        // 7. Simpan Pengaturan Struktur Pengurus
        Setting::set('board_period', trim($state['board_period'] ?? '2024-2029'), 'profile');
        Setting::set('board_sk_note', trim($state['board_sk_note'] ?? ''), 'profile');

        $this->rememberData();
        Setting::clearCache();

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Semua perubahan informasi situs telah diperbarui ke sistem.')
            ->success()
            ->send();
    }

    /**
     * Normalisasi nomor telepon ke format internasional Indonesia (628xxxxxxxxxx).
     */
    protected function normalizePhoneNumber(?string $phone): string
    {
        if (blank($phone)) {
            return '';
        }

        // Ambil hanya digit
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62'.$digits;
    }

    /**
     * Format nomor telepon untuk tampilan yang akrab bagi admin (08xxxxxxxxxx).
     */
    protected function formatPhoneForDisplay(?string $phone): string
    {
        if (blank($phone)) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '628')) {
            return '08'.substr($digits, 3);
        }

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }
}
