<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kegiatan & Status')
                    ->description('Tentukan kegiatan bimtek yang diikuti dan status kelayakan pendaftar.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('event_id')
                                ->relationship('event', 'title')
                                ->label('Kegiatan Bimtek')
                                ->helperText('Pilih kegiatan bimtek yang diikuti peserta.')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('code')
                                ->label('Kode Registrasi')
                                ->helperText('Kode unik otomatis yang dibuat sistem.')
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(1),

                            Select::make('status')
                                ->label('Status Pendaftaran')
                                ->helperText('Ubah status menjadi Diterima/Menunggu/Ditolak.')
                                ->options(Registration::statusLabels())
                                ->default('pending')
                                ->required()
                                ->columnSpan(1),

                            Select::make('participant_type')
                                ->label('Kategori Peserta')
                                ->helperText('Kategori asal atau latar belakang peserta.')
                                ->options(Registration::participantTypeLabels())
                                ->required()
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Data Pribadi Peserta')
                    ->description('Informasi kontak dan domisili pendaftar di Kabupaten Brebes.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('full_name')
                                ->label('Nama Lengkap')
                                ->placeholder('Contoh: Ahmad Fauzi')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('phone')
                                ->label('Nomor WhatsApp')
                                ->placeholder('Contoh: 081234567890')
                                ->helperText('Nomor aktif untuk konfirmasi bimtek.')
                                ->required()
                                ->maxLength(30)
                                ->columnSpan(1),

                            TextInput::make('email')
                                ->label('Alamat Email')
                                ->placeholder('nama@email.com')
                                ->email()
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('district')
                                ->label('Kecamatan')
                                ->placeholder('Contoh: Wanasari')
                                ->required()
                                ->maxLength(100)
                                ->columnSpan(1),

                            TextInput::make('village')
                                ->label('Desa / Kelurahan')
                                ->placeholder('Contoh: Kaliluwih')
                                ->maxLength(100)
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Catatan Tambahan')
                    ->description('Keterangan khusus dari pendaftar atau catatan panitia.')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan Pendaftar')
                            ->placeholder('Tuliskan catatan bila ada...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
