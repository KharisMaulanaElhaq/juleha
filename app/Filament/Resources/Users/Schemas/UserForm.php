<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Akun Pengguna')
                ->description('Kelola data akun pengurus atau peserta DPD JULEHA Brebes.')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Ahmad Fauzi, S.Pt')
                        ->columnSpan(1),

                    TextInput::make('email')
                        ->label('Alamat Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->disabled(fn (?User $record): bool => $record && filled($record->google_id))
                        ->helperText(fn (?User $record): ?string => $record && filled($record->google_id)
                            ? 'Email terhubung dengan Akun Google OAuth dan tidak dapat diubah dari sini.'
                            : 'Email resmi untuk login dan komunikasi sistem.')
                        ->columnSpan(1),

                    Select::make('role')
                        ->label('Peran Akun (Hak Akses)')
                        ->required()
                        ->options([
                            User::ROLE_ADMIN_KEGIATAN => 'Admin Kegiatan (Bimtek, Presensi & Sertifikat)',
                            User::ROLE_EDITOR_KONTEN => 'Editor Konten (Berita, Galeri & Publikasi)',
                            User::ROLE_SUPER_ADMIN => 'Super Admin (Akses Penuh Semua Modul)',
                            User::ROLE_PARTICIPANT => 'Peserta (Portal Pelatihan Bimtek)',
                        ])
                        ->default(User::ROLE_ADMIN_KEGIATAN)
                        ->helperText('Super Admin memiliki kendali penuh termasuk manajemen pengguna.')
                        ->columnSpan(1),

                    Toggle::make('is_active')
                        ->label('Status Akun Aktif')
                        ->default(true)
                        ->helperText('Jika dinonaktifkan, seluruh sesi login diputus dan akun tidak dapat masuk ke sistem.')
                        ->visible(fn (string $operation): bool => $operation === 'edit')
                        ->columnSpan(1),

                    Textarea::make('suspended_reason')
                        ->label('Alasan Penonaktifan')
                        ->placeholder('Tuliskan alasan penonaktifan akun ini...')
                        ->visible(fn (Get $get, string $operation): bool => $operation === 'edit' && ! (bool) $get('is_active'))
                        ->required(fn (Get $get, string $operation): bool => $operation === 'edit' && ! (bool) $get('is_active'))
                        ->columnSpanFull(),
                ]),

            Section::make('Pengaturan Kata Sandi Awal')
                ->description('Pilih cara penyediaan kata sandi untuk akun pengurus yang baru dibuat.')
                ->icon('heroicon-o-key')
                ->visible(fn (string $operation): bool => $operation === 'create')
                ->schema([
                    Radio::make('password_option')
                        ->label('Opsi Kata Sandi')
                        ->options([
                            'email_link' => 'Kirim tautan atur kata sandi lewat email (Direkomendasikan)',
                            'generate_random' => 'Buat kata sandi acak otomatis (akan ditampilkan sekali kepada Anda)',
                            'custom' => 'Tentukan kata sandi manual sekarang',
                        ])
                        ->default('email_link')
                        ->live(),

                    TextInput::make('password')
                        ->label('Kata Sandi Manual')
                        ->password()
                        ->revealable()
                        ->visible(fn (Get $get): bool => $get('password_option') === 'custom')
                        ->required(fn (Get $get): bool => $get('password_option') === 'custom')
                        ->rules([Password::min(8)->letters()->numbers()])
                        ->helperText('Minimal 8 karakter, wajib kombinasi huruf dan angka.')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
