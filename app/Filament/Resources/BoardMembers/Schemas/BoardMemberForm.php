<?php

namespace App\Filament\Resources\BoardMembers\Schemas;

use App\Models\BoardMember;
use App\Services\BoardMemberPhotoService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class BoardMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Struktur & Keanggotaan Pengurus')
                    ->description('Kelola data lembaga pelindung/pembina, pimpinan harian, dan tim bidang kepengurusan.')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Radio::make('kind')
                            ->label('Jenis Pengurus')
                            ->options([
                                BoardMember::KIND_PERSON => 'Perorangan (Personal)',
                                BoardMember::KIND_INSTITUTION => 'Lembaga / Instansi',
                            ])
                            ->default(BoardMember::KIND_PERSON)
                            ->required()
                            ->inline()
                            ->live()
                            ->columnSpanFull(),

                        Select::make('group')
                            ->label('Kelompok Kepengurusan')
                            ->options([
                                BoardMember::GROUP_PELINDUNG_PEMBINA => 'Pelindung & Pembina (Lembaga)',
                                BoardMember::GROUP_PIMPINAN_HARIAN => 'Pimpinan Harian (Ketua, Sekretaris, Bendahara)',
                                BoardMember::GROUP_BIDANG => 'Bidang-Bidang Kepengurusan',
                            ])
                            ->default(BoardMember::GROUP_BIDANG)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === BoardMember::GROUP_PELINDUNG_PEMBINA) {
                                    $set('kind', BoardMember::KIND_INSTITUTION);
                                    $set('division', null);
                                } elseif ($state === BoardMember::GROUP_PIMPINAN_HARIAN) {
                                    $set('kind', BoardMember::KIND_PERSON);
                                    $set('division', null);
                                } else {
                                    $set('kind', BoardMember::KIND_PERSON);
                                    $set('position', null);
                                }
                            })
                            ->columnSpan(1),

                        Select::make('division')
                            ->label('Nama Bidang')
                            ->options(function () {
                                $defaults = [
                                    'Bidang Pendidikan' => 'Bidang Pendidikan',
                                    "Bidang Da'wah" => "Bidang Da'wah",
                                    'Bidang Pengembangan Program' => 'Bidang Pengembangan Program',
                                    'Bidang Tim Ahli' => 'Bidang Tim Ahli',
                                    'Bidang Usaha' => 'Bidang Usaha',
                                    'Bidang Humas' => 'Bidang Humas',
                                    'Bidang Adat' => 'Bidang Adat',
                                    'Bidang Perlengkapan/Umum' => 'Bidang Perlengkapan/Umum',
                                    'Bidang Sosial & Kebencanaan' => 'Bidang Sosial & Kebencanaan',
                                ];

                                $fromDb = BoardMember::whereNotNull('division')
                                    ->distinct()
                                    ->pluck('division', 'division')
                                    ->toArray();

                                return array_merge($defaults, $fromDb);
                            })
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('new_division')
                                    ->label('Nama Bidang Baru')
                                    ->required()
                                    ->placeholder('Contoh: Bidang Advokasi & Hukum'),
                            ])
                            ->createOptionUsing(fn (array $data): string => $data['new_division'])
                            ->helperText('Pilih rumpun bidang kerja atau tambahkan bidang baru.')
                            ->visible(fn ($get) => $get('group') === BoardMember::GROUP_BIDANG)
                            ->required(fn ($get) => $get('group') === BoardMember::GROUP_BIDANG)
                            ->columnSpan(1),

                        TextInput::make('position')
                            ->label('Kedudukan / Jabatan')
                            ->placeholder('Contoh: Pelindung, Ketua, Sekretaris I, Bendahara I')
                            ->helperText('Untuk lembaga isi kedudukan (mis. Pelindung). Untuk anggota bidang boleh dikosongkan.')
                            ->required(fn ($get) => in_array($get('group'), [BoardMember::GROUP_PELINDUNG_PEMBINA, BoardMember::GROUP_PIMPINAN_HARIAN], true))
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Nama Lengkap / Nama Lembaga')
                            ->placeholder('Tulis nama lengkap pengurus dengan gelar / nama instansi')
                            ->helperText('Tulis lengkap dengan gelar sesuai SK')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('sort_order')
                            ->label('Nomor Urut Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka urutan tampilan di daftar susunan pengurus.')
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Status Aktif Ditampilkan')
                            ->helperText('Aktifkan agar pengurus tampil di halaman website Struktur Pengurus.')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Foto Profil Pengurus')
                    ->description('Pilih foto dari komputer atau Google Drive (rasio 4:5 format WebP, metadata EXIF otomatis dibersihkan).')
                    ->icon('heroicon-o-camera')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('kind') !== BoardMember::KIND_INSTITUTION)
                    ->schema([
                        View::make('filament.forms.components.board-member-photo-picker')
                            ->columnSpanFull(),

                        FileUpload::make('photo')
                            ->label('Unggah & Potong Foto (Rasio 4:5)')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('4:5')
                            ->imageEditorAspectRatios(['4:5'])
                            ->imageAspectRatio('4:5')
                            ->automaticallyOpenImageEditorForAspectRatio()
                            ->disk('public')
                            ->directory('board-members/temp')
                            ->maxSize(8192)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->extraAttributes(['class' => 'board-member-photo-upload'])
                            ->helperText('Petunjuk: Geser dan perbesar sampai wajah berada di tengah atas (rasio 4:5). Nyaman di layar sentuh. Sisi terpendek minimal 400 px, batas ukuran 8 MB (atau batas PHP hosting jika lebih kecil).')
                            ->live()
                            ->saveUploadedFileUsing(function ($file, ?BoardMember $record) {
                                $photoService = app(BoardMemberPhotoService::class);
                                $result = $photoService->processAndStore($file, $record?->photo);

                                return $result['main'];
                            })
                            ->columnSpanFull(),

                        DatePicker::make('photo_consent_at')
                            ->label('Tanggal Persetujuan Foto')
                            ->default(now()->toDateString())
                            ->required(fn ($get) => filled($get('photo')))
                            ->validationMessages([
                                'required' => 'Tanggal persetujuan wajib diisi saat foto profil diunggah.',
                            ])
                            ->helperText('Isi dengan tanggal pengurus menyetujui publikasi foto.')
                            ->columnSpan(1),

                        TextInput::make('photo_consent_note')
                            ->label('Catatan Bukti Persetujuan')
                            ->placeholder('Diminta sendiri via WhatsApp, 28 Okt 2026')
                            ->required(fn ($get) => filled($get('photo')))
                            ->validationMessages([
                                'required' => 'Catatan bukti persetujuan wajib diisi saat foto profil diunggah.',
                            ])
                            ->helperText('Wajib diisi bila ada foto. Catatan administratif media persetujuan (tidak terisi otomatis).')
                            ->maxLength(255)
                            ->columnSpan(1),
                    ]),

                Section::make('Catatan Internal (Rahasia)')
                    ->description('Catatan rahasia/internal kepengurusan, TIDAK AKAN PERNAH tampil di halaman publik.')
                    ->icon('heroicon-o-lock-closed')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Textarea::make('internal_note')
                            ->label('Catatan Internal')
                            ->placeholder('Contoh: Perlu dikonfirmasi: orang yang sama?')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
