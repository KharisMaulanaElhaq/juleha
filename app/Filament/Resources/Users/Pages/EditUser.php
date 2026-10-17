<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\AccountStatusChangedNotification;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected array $originalAttributes = [];

    protected function beforeFill(): void
    {
        $this->originalAttributes = [
            'role' => $this->record->role,
            'is_active' => $this->record->is_active,
            'email' => $this->record->email,
        ];
    }

    protected function beforeSave(): void
    {
        $authId = auth()->id();
        $record = $this->record;
        $newData = $this->data;

        // 1. Pengaman Akun Sendiri
        if ($record->id === $authId) {
            if (isset($newData['role']) && $newData['role'] !== $record->role) {
                Notification::make()
                    ->title('Aksi Ditolak')
                    ->body('Anda tidak dapat mengubah peran akun Anda sendiri.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            if (isset($newData['is_active']) && ! (bool) $newData['is_active']) {
                Notification::make()
                    ->title('Aksi Ditolak')
                    ->body('Anda tidak dapat menonaktifkan akun Anda sendiri.')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        // 2. Pengaman Super Admin Terakhir
        if ($record->isSuperAdmin()) {
            $isDemoting = isset($newData['role']) && $newData['role'] !== User::ROLE_SUPER_ADMIN;
            $isDeactivating = isset($newData['is_active']) && ! (bool) $newData['is_active'];

            if ($isDemoting || $isDeactivating) {
                $count = User::where('role', User::ROLE_SUPER_ADMIN)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->count();

                if ($count <= 1) {
                    Notification::make()
                        ->title('Aksi Ditolak')
                        ->body('Tidak dapat menurunkan atau menonaktifkan Super Admin terakhir di sistem.')
                        ->danger()
                        ->send();

                    $this->halt();
                }
            }
        }
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $wasActive = $this->originalAttributes['is_active'] ?? true;
        $nowActive = (bool) $record->is_active;

        // Jika status akun berubah
        if ($wasActive && ! $nowActive) {
            // Hapus sesi aktif pengguna
            DB::table('sessions')->where('user_id', $record->id)->delete();
            $record->forceFill(['remember_token' => null])->saveQuietly();

            AuditLog::logUserActivity('suspended', $record->id, [
                'reason' => $record->suspended_reason ?? 'Dinonaktifkan via edit pengguna',
            ]);

            try {
                if ($record->email) {
                    Mail::to($record->email)->queue(new AccountStatusChangedNotification($record, false));
                }
            } catch (\Exception $e) {
            }
        } elseif (! $wasActive && $nowActive) {
            AuditLog::logUserActivity('activated', $record->id);

            try {
                if ($record->email) {
                    Mail::to($record->email)->queue(new AccountStatusChangedNotification($record, true));
                }
            } catch (\Exception $e) {
            }
        }

        // Catat jika ada perubahan peran
        $oldRole = $this->originalAttributes['role'] ?? $record->role;
        if ($oldRole !== $record->role) {
            AuditLog::logUserActivity('role_changed', $record->id, [
                'old_role' => $oldRole,
                'new_role' => $record->role,
            ]);
        } else {
            AuditLog::logUserActivity('updated', $record->id, [
                'name' => $record->name,
                'email' => $record->email,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->record->hasHistory()),
        ];
    }
}
