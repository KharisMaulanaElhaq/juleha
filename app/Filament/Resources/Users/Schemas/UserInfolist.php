<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Akun')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label('Nama Lengkap')
                        ->weight('bold'),

                    TextEntry::make('email')
                        ->label('Alamat Email')
                        ->copyable(),

                    TextEntry::make('role')
                        ->label('Peran Akun')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            User::ROLE_SUPER_ADMIN => 'Super Admin',
                            User::ROLE_ADMIN_KEGIATAN, 'event_admin' => 'Admin Kegiatan',
                            User::ROLE_EDITOR_KONTEN, 'content_editor' => 'Editor Konten',
                            default => 'Peserta',
                        })
                        ->color(fn (string $state): string => match ($state) {
                            User::ROLE_SUPER_ADMIN => 'danger',
                            User::ROLE_ADMIN_KEGIATAN, 'event_admin' => 'primary',
                            User::ROLE_EDITOR_KONTEN, 'content_editor' => 'info',
                            default => 'gray',
                        }),

                    TextEntry::make('is_active')
                        ->label('Status Akun')
                        ->badge()
                        ->formatStateUsing(fn (bool $state, User $record): string => $record->trashed() ? 'Dihapus (Soft Delete)' : ($state ? 'Aktif' : 'Dinonaktifkan'))
                        ->color(fn (bool $state, User $record): string => $record->trashed() ? 'danger' : ($state ? 'success' : 'warning')),

                    TextEntry::make('login_method')
                        ->label('Cara Masuk')
                        ->badge()
                        ->state(fn (User $record): string => filled($record->google_id) ? 'Google OAuth' : 'Email & Kata Sandi')
                        ->color(fn (string $state): string => str_contains($state, 'Google') ? 'info' : 'gray'),

                    TextEntry::make('last_login_at')
                        ->label('Login Terakhir')
                        ->dateTime('d M Y H:i WIB')
                        ->placeholder('Belum pernah masuk'),

                    TextEntry::make('created_at')
                        ->label('Tanggal Bergabung')
                        ->dateTime('d M Y H:i WIB'),

                    TextEntry::make('suspended_reason')
                        ->label('Alasan Penonaktifan')
                        ->visible(fn (User $record): bool => ! $record->is_active && filled($record->suspended_reason))
                        ->columnSpanFull()
                        ->color('danger'),
                ]),

            Section::make('Profil Peserta & Data Sertifikat')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->visible(fn (User $record): bool => $record->isParticipant())
                ->schema([
                    TextEntry::make('profile.full_name_ktp')
                        ->label('Nama Sesuai KTP')
                        ->placeholder('Belum diisi'),

                    TextEntry::make('profile.masked_nik')
                        ->label('NIK (Tersamar)')
                        ->badge()
                        ->color('gray')
                        ->placeholder('Belum diisi')
                        ->suffixAction(
                            Action::make('view_nik')
                                ->label('Tampilkan NIK Lengkap')
                                ->icon('heroicon-o-eye')
                                ->color('warning')
                                ->visible(fn (): bool => auth()->user()?->isAdminKegiatan() === true)
                                ->requiresConfirmation()
                                ->modalHeading('Peringatan Privasi: Buka NIK')
                                ->modalDescription('Pembukaan NIK lengkap ini dicatat secara resmi di log audit untuk kepatuhan perlindungan data pribadi (UU PDP). Lanjutkan?')
                                ->modalSubmitActionLabel('Buka NIK Lengkap')
                                ->action(function (User $record): void {
                                    $rawNik = $record->profile?->nik;

                                    AuditLog::log('view_nik', 'User', $record->id, [
                                        'target_name' => $record->name,
                                        'source' => 'user_infolist',
                                    ]);

                                    Notification::make()
                                        ->title('Informasi NIK Peserta')
                                        ->body('NIK Lengkap: '.($rawNik ?: 'Belum diisi').' ('.$record->name.')')
                                        ->warning()
                                        ->persistent()
                                        ->send();
                                })
                        ),

                    TextEntry::make('profile.phone')
                        ->label('Nomor WhatsApp')
                        ->url(fn (User $record): ?string => $record->profile?->phone ? 'https://wa.me/'.$record->profile->phone : null, shouldOpenInNewTab: true)
                        ->placeholder('Belum diisi'),

                    TextEntry::make('profile.district')
                        ->label('Kecamatan (Domisili)')
                        ->placeholder('Belum diisi'),

                    TextEntry::make('profile.village')
                        ->label('Desa / Kelurahan')
                        ->placeholder('Belum diisi'),

                    TextEntry::make('profile.institution')
                        ->label('Instansi / Asal')
                        ->placeholder('Mandiri / Perorangan'),

                    TextEntry::make('profile.status')
                        ->label('Status Profesi')
                        ->placeholder('Belum diisi'),
                ]),
        ]);
    }
}
