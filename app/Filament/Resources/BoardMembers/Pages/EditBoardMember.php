<?php

namespace App\Filament\Resources\BoardMembers\Pages;

use App\Filament\Resources\BoardMembers\BoardMemberResource;
use App\Services\BoardMemberPhotoService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBoardMember extends EditRecord
{
    protected static string $resource = BoardMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_crop')
                ->label('Atur Ulang Pemotongan')
                ->icon('heroicon-o-arrows-pointing-in')
                ->color('gray')
                ->visible(fn (): bool => ! empty($this->record->photo) && app(BoardMemberPhotoService::class)->hasOriginal($this->record->photo))
                ->requiresConfirmation()
                ->modalHeading('Atur Ulang Pemotongan Foto')
                ->modalDescription('Sistem akan memotong ulang foto dari berkas asli yang tersimpan di disk privat ke rasio 4:5 (800x1000 dan 400x500 WebP). Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Potong Ulang')
                ->action(function () {
                    try {
                        $service = app(BoardMemberPhotoService::class);
                        $result = $service->reprocessFromOriginal($this->record->photo);
                        $this->record->update([
                            'photo' => $result['main'],
                            'photo_version' => $result['version'],
                        ]);
                        $this->fillForm();
                        Notification::make()
                            ->title('Foto Berhasil Dipotong Ulang')
                            ->body('Versi utama dan thumbnail WebP telah diperbarui dari berkas asli privat.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Mengatur Ulang Pemotongan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('delete_photo')
                ->label('Hapus Foto')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Foto Pengurus')
                ->modalDescription('Apakah Anda yakin ingin menghapus foto pengurus ini beserta seluruh berkas WebP turunannya dan cadangan privat?')
                ->modalSubmitActionLabel('Ya, Hapus Foto')
                ->visible(fn (): bool => ! empty($this->record->photo))
                ->action(function () {
                    app(BoardMemberPhotoService::class)->deletePhotoFiles($this->record->photo);
                    $this->record->update([
                        'photo' => null,
                        'photo_consent_at' => null,
                        'photo_consent_note' => null,
                        'photo_version' => time(),
                    ]);
                    $this->fillForm();
                    Notification::make()
                        ->title('Foto pengurus berhasil dihapus')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['photo']) && ! empty($this->record->photo)) {
            // Jika foto dihapus dari form FileUpload
            app(BoardMemberPhotoService::class)->deletePhotoFiles($this->record->photo);
            $data['photo_consent_at'] = null;
            $data['photo_consent_note'] = null;
            $data['photo_version'] = time();
        } elseif (! empty($data['photo']) && $data['photo'] !== $this->record->photo) {
            $data['photo_version'] = time();
        }

        unset($data['google_drive_url']);

        return $data;
    }
}
