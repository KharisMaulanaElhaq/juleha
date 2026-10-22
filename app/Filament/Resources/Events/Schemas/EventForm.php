<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventType;
use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Bimtek')
                    ->description('Nama kegiatan pelatihan dan penjelasan materi yang akan dipelajari.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Nama Kegiatan Bimtek')
                                ->placeholder('Contoh: Bimtek Juru Sembelih Halal Angkatan 13')
                                ->helperText('Nama resmi kegiatan yang muncul pada pengumuman dan sertifikat.')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? '')))
                                ->columnSpan(2),

                            TextInput::make('short_title')
                                ->label('Nama Singkat Kegiatan')
                                ->placeholder('Contoh: Bimtek Angk. 14')
                                ->helperText('Dipakai di grafik dan tempat sempit. Contoh: Bimtek Angk. 14')
                                ->maxLength(40)
                                ->nullable()
                                ->columnSpan(1),

                            TextInput::make('slug')
                                ->label('Slug URL Website')
                                ->placeholder('otomatis-terisi-dari-nama')
                                ->helperText('Alamat URL unik untuk halaman pendaftaran online.')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpan(1),

                            Select::make('type')
                                ->label('Jenis Kegiatan')
                                ->options(collect(EventType::cases())->mapWithKeys(fn (EventType $type) => [$type->value => $type->label()]))
                                ->default(EventType::Bimtek->value)
                                ->required()
                                ->helperText('Pilih salah satu dari 3 kategori kegiatan resmi.')
                                ->columnSpan(1),

                            Select::make('program_id')
                                ->label('Program Kerja Terkait')
                                ->relationship('program', 'title')
                                ->searchable()
                                ->preload()
                                ->placeholder('Pilih program kerja induk...')
                                ->helperText('Pilih program induk yang menaungi kegiatan ini.')
                                ->columnSpan(1),

                            Textarea::make('description')
                                ->label('Deskripsi & Silabus Pelatihan')
                                ->placeholder('Tuliskan rincian materi, persyaratan peserta, dan fasilitas yang didapatkan...')
                                ->helperText('Penjelasan lengkap mengenai materi teori dan praktik sembelih.')
                                ->rows(5)
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Waktu & Lokasi Pelaksanaan')
                    ->description('Jadwal tanggal pelaksanaan, batas pendaftaran, dan tempat acara di wilayah Brebes.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)->schema([
                            DateTimePicker::make('starts_at')
                                ->label('Waktu Mulai')
                                ->helperText('Tanggal dan jam pembukaan kegiatan.')
                                ->required()
                                ->columnSpan(1),

                            DateTimePicker::make('ends_at')
                                ->label('Waktu Selesai Kegiatan')
                                ->helperText('Estimasi penutupan acara (harus setelah waktu mulai).')
                                ->nullable()
                                ->after('starts_at')
                                ->columnSpan(1),

                            DateTimePicker::make('registration_deadline')
                                ->label('Batas Pendaftaran')
                                ->helperText('Batas akhir pendaftaran online. Setelah waktu ini, pendaftaran otomatis ditutup.')
                                ->nullable()
                                ->columnSpan(1),

                            TextInput::make('location')
                                ->label('Lokasi Kegiatan')
                                ->placeholder('Contoh: Aula Kemenag Brebes / RPH Jatibarang')
                                ->helperText('Nama gedung, alamat, atau tempat praktik.')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Kuota, Biaya & Status Pendaftaran')
                    ->description('Pengaturan pendaftaran online dan ketersediaan kursi peserta.')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('quota')
                                ->label('Kuota Maksimal Peserta')
                                ->placeholder('30 (kosongkan jika tanpa batas)')
                                ->helperText('Kosongkan untuk kuota tanpa batas. Kuota tidak boleh lebih kecil dari peserta terdaftar.')
                                ->numeric()
                                ->minValue(1)
                                ->nullable()
                                ->rules([
                                    fn (?Event $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                        if ($record && filled($value)) {
                                            $used = $record->usedSeats();
                                            if ((int) $value < $used) {
                                                $fail("Kuota tidak boleh lebih kecil dari jumlah pendaftaran yang sudah terpakai (saat ini: {$used} peserta).");
                                            }
                                        }
                                    },
                                ])
                                ->columnSpan(1),

                            TextInput::make('fee')
                                ->label('Biaya Kontribusi (Rp)')
                                ->placeholder('0')
                                ->helperText('Ketik 0 jika kegiatan gratis / disubsidi.')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->required()
                                ->columnSpan(1),

                            Select::make('status')
                                ->label('Status Pendaftaran')
                                ->helperText('Hanya status "Dibuka" yang dapat menerima pendaftar di web.')
                                ->options([
                                    'draft' => 'Draft (Belum Ditampilkan)',
                                    'open' => 'Dibuka (Menerima Pendaftar)',
                                    'closed' => 'Ditutup (Penuh / Selesai)',
                                    'done' => 'Selesai Dilaksanakan',
                                ])
                                ->default('open')
                                ->required()
                                ->columnSpan(1),
                        ]),
                    ]),
            ]);
    }
}
