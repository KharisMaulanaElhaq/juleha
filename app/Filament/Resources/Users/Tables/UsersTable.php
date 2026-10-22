<?php

namespace App\Filament\Resources\Users\Tables;

use App\Http\Controllers\Participant\ProfileController;
use App\Mail\AccountStatusChangedNotification;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama & Email')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        User::ROLE_SUPER_ADMIN => 'heroicon-m-shield-check',
                        User::ROLE_ADMIN_KEGIATAN, 'event_admin' => 'heroicon-m-calendar-days',
                        User::ROLE_EDITOR_KONTEN, 'content_editor' => 'heroicon-m-pencil-square',
                        default => 'heroicon-m-user',
                    })
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

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state, User $record): string => $record->trashed()
                        ? 'Dihapus'
                        : ($state ? 'Aktif' : 'Dinonaktifkan'))
                    ->color(fn (bool $state, User $record): string => $record->trashed()
                        ? 'danger'
                        : ($state ? 'success' : 'warning'))
                    ->icon(fn (bool $state, User $record): string => $record->trashed()
                        ? 'heroicon-m-trash'
                        : ($state ? 'heroicon-m-check-circle' : 'heroicon-m-no-symbol')),

                TextColumn::make('login_method')
                    ->label('Cara Masuk')
                    ->badge()
                    ->state(fn (User $record): string => filled($record->google_id) ? 'Google' : 'Email')
                    ->color(fn (string $state): string => $state === 'Google' ? 'info' : 'gray')
                    ->icon(fn (string $state): string => $state === 'Google' ? 'heroicon-m-globe-alt' : 'heroicon-m-key')
                    ->visibleFrom('md'),

                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->formatStateUsing(fn (?Carbon $state): string => $state ? $state->diffForHumans() : 'Belum pernah')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('lg'),

                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Kegiatan')
                    ->formatStateUsing(fn ($state, User $record): string => $record->isParticipant() ? $state.' kegiatan' : '–')
                    ->toggleable()
                    ->visibleFrom('lg'),

                TextColumn::make('profile_complete')
                    ->label('Profil')
                    ->badge()
                    ->state(function (User $record): string {
                        if (! $record->isParticipant()) {
                            return 'Pengurus';
                        }
                        $profile = $record->profile;

                        return ($profile && filled($profile->nik) && filled($profile->phone)) ? 'Lengkap' : 'Belum lengkap';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Lengkap' => 'success',
                        'Belum lengkap' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable()
                    ->visibleFrom('lg'),

                TextColumn::make('created_at')
                    ->label('Tanggal Bergabung')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran Akun')
                    ->options([
                        User::ROLE_SUPER_ADMIN => 'Super Admin',
                        User::ROLE_ADMIN_KEGIATAN => 'Admin Kegiatan',
                        User::ROLE_EDITOR_KONTEN => 'Editor Konten',
                        User::ROLE_PARTICIPANT => 'Peserta',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status Akun')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Dinonaktifkan',
                    ]),

                SelectFilter::make('login_method')
                    ->label('Cara Masuk')
                    ->options([
                        'google' => 'Google OAuth',
                        'password' => 'Email & Kata Sandi',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $data['value'] === 'google'
                            ? $query->whereNotNull('google_id')
                            : $query->whereNull('google_id');
                    }),

                SelectFilter::make('profile_status')
                    ->label('Kelengkapan Profil Peserta')
                    ->options([
                        'complete' => 'Profil Lengkap (NIK & WA Ada)',
                        'incomplete' => 'Profil Belum Lengkap',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'complete') {
                            return $query->whereHas('profile', fn (Builder $q) => $q->whereNotNull('nik')->whereNotNull('phone'));
                        }

                        return $query->whereDoesntHave('profile', fn (Builder $q) => $q->whereNotNull('nik')->whereNotNull('phone'));
                    }),

                SelectFilter::make('district')
                    ->label('Kecamatan (Brebes)')
                    ->options(collect(ProfileController::BREBES_DISTRICTS)->mapWithKeys(fn (string $d) => [$d => $d]))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('profile', fn (Builder $q) => $q->where('district', $data['value']));
                    }),

                Filter::make('never_logged_in')
                    ->label('Belum Pernah Login')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_login_at')),

                Filter::make('created_at')
                    ->label('Rentang Tanggal Bergabung')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    // 1. Ubah Peran (Change Role)
                    Action::make('change_role')
                        ->label('Ubah Peran')
                        ->icon('heroicon-o-shield-check')
                        ->color('warning')
                        ->form([
                            Select::make('new_role')
                                ->label('Pilih Peran Baru')
                                ->required()
                                ->options([
                                    User::ROLE_ADMIN_KEGIATAN => 'Admin Kegiatan (Bimtek & Presensi)',
                                    User::ROLE_EDITOR_KONTEN => 'Editor Konten (Berita & Galeri)',
                                    User::ROLE_SUPER_ADMIN => 'Super Admin (Akses Penuh)',
                                    User::ROLE_PARTICIPANT => 'Peserta (Portal Pelatihan)',
                                ])
                                ->default(fn (User $record): string => $record->role),
                        ])
                        ->action(function (User $record, array $data): void {
                            $authId = auth()->id();

                            if ($record->id === $authId) {
                                Notification::make()
                                    ->title('Aksi Ditolak')
                                    ->body('Anda tidak dapat mengubah peran akun Anda sendiri.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $newRole = $data['new_role'];
                            $oldRole = $record->role;

                            // Lindungi Super Admin Terakhir
                            if ($oldRole === User::ROLE_SUPER_ADMIN && $newRole !== User::ROLE_SUPER_ADMIN) {
                                $superAdminCount = User::where('role', User::ROLE_SUPER_ADMIN)
                                    ->where('is_active', true)
                                    ->whereNull('deleted_at')
                                    ->count();

                                if ($superAdminCount <= 1) {
                                    Notification::make()
                                        ->title('Aksi Ditolak')
                                        ->body('Tidak dapat menurunkan peran Super Admin terakhir. Harus ada minimal satu Super Admin aktif.')
                                        ->danger()
                                        ->send();

                                    return;
                                }
                            }

                            $record->update(['role' => $newRole]);

                            AuditLog::logUserActivity('role_changed', $record->id, [
                                'old_role' => $oldRole,
                                'new_role' => $newRole,
                            ]);

                            Notification::make()
                                ->title('Peran Pengguna Diperbarui')
                                ->body("Peran {$record->name} berhasil diubah dari {$oldRole} menjadi {$newRole}.")
                                ->success()
                                ->send();
                        }),

                    // 2. Nonaktifkan / Aktifkan
                    Action::make('toggle_active')
                        ->label(fn (User $record): string => $record->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun')
                        ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                        ->form(fn (User $record): array => $record->is_active ? [
                            Textarea::make('suspended_reason')
                                ->label('Alasan Penonaktifan (Wajib)')
                                ->placeholder('Contoh: Permintaan pemilik akun / indikasi pelanggaran aturan')
                                ->required(),
                        ] : [])
                        ->requiresConfirmation()
                        ->modalHeading(fn (User $record): string => $record->is_active ? 'Konfirmasi Penonaktifan Akun' : 'Konfirmasi Pengaktifan Akun')
                        ->modalDescription(fn (User $record): string => $record->is_active
                            ? "Pengguna {$record->name} akan diputus dari seluruh sesi login aktif dan tidak dapat masuk kembali sampai diaktifkan ulang."
                            : "Pengguna {$record->name} akan dapat masuk kembali ke sistem.")
                        ->modalSubmitActionLabel(fn (User $record): string => $record->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan')
                        ->action(function (User $record, array $data): void {
                            $authId = auth()->id();

                            if ($record->id === $authId) {
                                Notification::make()
                                    ->title('Aksi Ditolak')
                                    ->body('Anda tidak dapat menonaktifkan akun Anda sendiri.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($record->is_active) {
                                // Perlindungan Super Admin Terakhir
                                if ($record->isSuperAdmin()) {
                                    $superAdminCount = User::where('role', User::ROLE_SUPER_ADMIN)
                                        ->where('is_active', true)
                                        ->whereNull('deleted_at')
                                        ->count();

                                    if ($superAdminCount <= 1) {
                                        Notification::make()
                                            ->title('Aksi Ditolak')
                                            ->body('Tidak dapat menonaktifkan Super Admin terakhir di sistem.')
                                            ->danger()
                                            ->send();

                                        return;
                                    }
                                }

                                $reason = $data['suspended_reason'] ?? 'Dinonaktifkan oleh administrator';

                                $record->update([
                                    'is_active' => false,
                                    'suspended_at' => now(),
                                    'suspended_reason' => $reason,
                                ]);

                                // Hapus seluruh sesi login dan remember token pengguna
                                DB::table('sessions')->where('user_id', $record->id)->delete();
                                $record->forceFill(['remember_token' => null])->saveQuietly();

                                AuditLog::logUserActivity('suspended', $record->id, ['reason' => $reason]);

                                try {
                                    if ($record->email) {
                                        Mail::to($record->email)->queue(new AccountStatusChangedNotification($record, false));
                                    }
                                } catch (\Exception $e) {
                                }

                                Notification::make()
                                    ->title('Akun Berhasil Dinonaktifkan')
                                    ->body("Sesi aktif {$record->name} telah diputus dan akses masuk ditutup.")
                                    ->warning()
                                    ->send();
                            } else {
                                $prevReason = $record->suspended_reason;

                                $record->update([
                                    'is_active' => true,
                                    'suspended_at' => null,
                                    'suspended_reason' => null,
                                ]);

                                AuditLog::logUserActivity('activated', $record->id, ['previous_reason' => $prevReason]);

                                try {
                                    if ($record->email) {
                                        Mail::to($record->email)->queue(new AccountStatusChangedNotification($record, true));
                                    }
                                } catch (\Exception $e) {
                                }

                                Notification::make()
                                    ->title('Akun Berhasil Diaktifkan')
                                    ->body("Akun {$record->name} kini telah aktif dan dapat masuk kembali.")
                                    ->success()
                                    ->send();
                            }
                        }),

                    // 3. Reset Kata Sandi (Hanya Akun Berbasis Password)
                    Action::make('reset_password')
                        ->label('Kirim Tautan Kata Sandi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn (User $record): bool => empty($record->google_id) || filled($record->password))
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Tautan Atur Kata Sandi')
                        ->modalDescription(fn (User $record): string => "Kirimkan email berisi tautan pengaturan kata sandi baru ke alamat {$record->email}?")
                        ->modalSubmitActionLabel('Kirim Tautan Email')
                        ->action(function (User $record): void {
                            Password::sendResetLink(['email' => $record->email]);

                            AuditLog::logUserActivity('password_reset', $record->id, ['email' => $record->email]);

                            Notification::make()
                                ->title('Tautan Terkirim')
                                ->body("Tautan atur kata sandi berhasil dikirim ke {$record->email}.")
                                ->success()
                                ->send();
                        }),

                    // 4. Anonimkan Akun (Untuk Akun yang Memiliki Riwayat Bimtek / Kehadiran)
                    Action::make('anonymize')
                        ->label('Anonimkan Akun (UU PDP)')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->visible(fn (User $record): bool => $record->hasHistory() && ! $record->isAnonymized())
                        ->form([
                            Textarea::make('reason')
                                ->label('Alasan Permintaan Anonimisasi')
                                ->placeholder('Contoh: Permintaan penghapusan data pribadi dari peserta sesuai hak UU PDP')
                                ->required(),
                            TextInput::make('confirmation')
                                ->label('Ketik kata KONFIRMASI untuk melanjutkan')
                                ->required()
                                ->rules(['in:KONFIRMASI']),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Anonimisasi Data Pribadi')
                        ->modalDescription('Akun ini memiliki riwayat pendaftaran atau kehadiran bimtek sehingga tidak dapat dihapus permanen. Aksi ini akan menghapus permanen seluruh data sensitif (Nama, NIK, WhatsApp, Domisili, Email) dan menggantinya dengan data anonim. Riwayat pendaftaran & presensi tetap disimpan untuk integritas laporan bimtek.')
                        ->modalSubmitActionLabel('Lakukan Anonimisasi Akun')
                        ->action(function (User $record, array $data): void {
                            $authId = auth()->id();

                            if ($record->id === $authId) {
                                Notification::make()
                                    ->title('Aksi Ditolak')
                                    ->body('Anda tidak dapat menganonimkan akun Anda sendiri.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($record->isSuperAdmin()) {
                                $superAdminCount = User::where('role', User::ROLE_SUPER_ADMIN)
                                    ->where('is_active', true)
                                    ->whereNull('deleted_at')
                                    ->count();

                                if ($superAdminCount <= 1) {
                                    Notification::make()
                                        ->title('Aksi Ditolak')
                                        ->body('Tidak dapat menganonimkan Super Admin terakhir.')
                                        ->danger()
                                        ->send();

                                    return;
                                }
                            }

                            $oldId = $record->id;
                            $reason = $data['reason'];

                            // Anonimkan tabel users
                            $record->update([
                                'name' => 'Pengguna Dihapus #'.$oldId,
                                'email' => 'anonymized_'.$oldId.'_'.time().'@julehabrebes.org',
                                'avatar' => null,
                                'google_id' => null,
                                'password' => Hash::make(Str::random(32)),
                                'is_active' => false,
                                'suspended_at' => now(),
                                'suspended_reason' => 'Akun dianonimkan atas permintaan privasi data: '.$reason,
                                'anonymized_at' => now(),
                            ]);

                            // Anonimkan profil peserta jika ada
                            if ($record->profile) {
                                $record->profile->update([
                                    'full_name_ktp' => null,
                                    'nik' => null,
                                    'nik_hash' => null,
                                    'phone' => null,
                                    'district' => null,
                                    'village' => null,
                                    'institution' => null,
                                    'status' => null,
                                ]);
                            }

                            // Anonimkan berkas sertifikat (Opsi A: Hapus file fisik PDF, pertahankan catatan minimal verifikasi)
                            foreach ($record->certificates as $cert) {
                                if ($cert->file_path && Storage::disk('private')->exists($cert->file_path)) {
                                    Storage::disk('private')->delete($cert->file_path);
                                }
                                $cert->update([
                                    'file_path' => null,
                                    'file_name' => null,
                                    'file_size' => null,
                                    'file_sha256' => null,
                                ]);
                            }

                            // Hapus sesi login
                            DB::table('sessions')->where('user_id', $oldId)->delete();
                            $record->forceFill(['remember_token' => null])->saveQuietly();

                            // Soft delete akun
                            $record->delete();

                            AuditLog::logUserActivity('anonymized', $oldId, ['reason' => $reason]);

                            Notification::make()
                                ->title('Akun Berhasil Dianonimkan')
                                ->body('Seluruh data pribadi telah dihapus permanen. Catatan pendaftaran dan kehadiran dipertahankan secara anonim.')
                                ->warning()
                                ->send();
                        }),

                    // 5. Soft Delete (Hanya Akun Tanpa Riwayat)
                    DeleteAction::make()
                        ->label('Hapus (Soft Delete)')
                        ->visible(fn (User $record): bool => ! $record->hasHistory())
                        ->before(function (User $record, DeleteAction $action): void {
                            if ($record->id === auth()->id()) {
                                Notification::make()
                                    ->title('Aksi Ditolak')
                                    ->body('Anda tidak dapat menghapus akun Anda sendiri.')
                                    ->danger()
                                    ->send();

                                $action->cancel();

                                return;
                            }

                            if ($record->isSuperAdmin()) {
                                $superAdminCount = User::where('role', User::ROLE_SUPER_ADMIN)
                                    ->where('is_active', true)
                                    ->whereNull('deleted_at')
                                    ->count();

                                if ($superAdminCount <= 1) {
                                    Notification::make()
                                        ->title('Aksi Ditolak')
                                        ->body('Tidak dapat menghapus Super Admin terakhir.')
                                        ->danger()
                                        ->send();

                                    $action->cancel();
                                }
                            }
                        })
                        ->after(function (User $record): void {
                            AuditLog::logUserActivity('deleted', $record->id);
                        }),

                    // 6. Restore Action untuk Akun yang Dihapus
                    RestoreAction::make()
                        ->label('Pulihkan Akun')
                        ->after(function (User $record): void {
                            AuditLog::logUserActivity('restored', $record->id);
                        }),

                    // 7. Force Delete (Hanya Akun Tanpa Riwayat yang Sudah Soft-Deleted)
                    ForceDeleteAction::make()
                        ->label('Hapus Permanen')
                        ->visible(fn (User $record): bool => ! $record->hasHistory() && $record->trashed()),
                ])->tooltip('Menu Aksi'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_suspend')
                        ->label('Nonaktifkan Terpilih')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->form([
                            Textarea::make('reason')
                                ->label('Alasan Penonaktifan')
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Pengguna Terpilih')
                        ->modalDescription('Seluruh pengguna terpilih akan dinonaktifkan dan sesi login mereka diputus.')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan Semua')
                        ->action(function (Collection $records, array $data): void {
                            $authId = auth()->id();
                            $reason = $data['reason'];
                            $count = 0;

                            $records->each(function (User $record) use ($authId, $reason, &$count): void {
                                if ($record->id === $authId || ($record->isSuperAdmin() && User::where('role', User::ROLE_SUPER_ADMIN)->where('is_active', true)->whereNull('deleted_at')->count() <= 1)) {
                                    return;
                                }

                                $record->update([
                                    'is_active' => false,
                                    'suspended_at' => now(),
                                    'suspended_reason' => $reason,
                                ]);

                                DB::table('sessions')->where('user_id', $record->id)->delete();
                                $record->forceFill(['remember_token' => null])->saveQuietly();

                                AuditLog::logUserActivity('suspended', $record->id, ['reason' => $reason, 'source' => 'bulk_action']);
                                $count++;
                            });

                            Notification::make()
                                ->title('Penonaktifan Massal Selesai')
                                ->body("{$count} pengguna berhasil dinonaktifkan.")
                                ->warning()
                                ->send();
                        }),

                    BulkAction::make('bulk_activate')
                        ->label('Aktifkan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Pengguna Terpilih')
                        ->modalDescription('Seluruh pengguna terpilih akan diaktifkan kembali.')
                        ->modalSubmitActionLabel('Ya, Aktifkan Semua')
                        ->action(function (Collection $records): void {
                            $count = 0;

                            $records->each(function (User $record) use (&$count): void {
                                $prevReason = $record->suspended_reason;

                                $record->update([
                                    'is_active' => true,
                                    'suspended_at' => null,
                                    'suspended_reason' => null,
                                ]);

                                AuditLog::logUserActivity('activated', $record->id, ['previous_reason' => $prevReason, 'source' => 'bulk_action']);
                                $count++;
                            });

                            Notification::make()
                                ->title('Pengaktifan Massal Selesai')
                                ->body("{$count} pengguna berhasil diaktifkan kembali.")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Pengguna')
            ->emptyStateDescription('Pengguna dan pengurus yang terdaftar akan ditampilkan di sini.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
