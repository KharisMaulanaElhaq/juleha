<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainGeneratedPassword = null;

    protected ?string $chosenPasswordOption = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->chosenPasswordOption = $data['password_option'] ?? 'email_link';

        if ($this->chosenPasswordOption === 'generate_random') {
            $plain = Str::password(12);
            $this->plainGeneratedPassword = $plain;
            $data['password'] = Hash::make($plain);
        } elseif ($this->chosenPasswordOption === 'custom' && ! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Default: email_link -> set kata sandi acak kuat sementara
            $data['password'] = Hash::make(Str::random(32));
        }

        unset($data['password_option']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        AuditLog::logUserActivity('created', $record->id, [
            'name' => $record->name,
            'email' => $record->email,
            'role' => $record->role,
            'password_option' => $this->chosenPasswordOption,
        ]);

        if ($this->chosenPasswordOption === 'generate_random' && $this->plainGeneratedPassword) {
            Notification::make()
                ->title('Akun Pengguna Berhasil Dibuat')
                ->body("Kata sandi acak sementara: **{$this->plainGeneratedPassword}** (Harap salin dan simpan sekarang, pesan ini hanya ditampilkan sekali demi keamanan).")
                ->warning()
                ->persistent()
                ->send();
        } elseif ($this->chosenPasswordOption === 'email_link') {
            Password::sendResetLink(['email' => $record->email]);

            Notification::make()
                ->title('Akun Pengguna Berhasil Dibuat')
                ->body("Tautan pengaturan kata sandi baru telah otomatis dikirimkan ke {$record->email}.")
                ->success()
                ->send();
        }
    }
}
