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
            Action::make('delete_photo')
                ->label('Hapus Foto')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Foto Pengurus')
                ->modalDescription('Apakah Anda yakin ingin menghapus foto pengurus ini beserta seluruh berkas WebP turunannya?')
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
        if (! empty($data['google_drive_url']) && (empty($data['photo']) || $data['photo'] === $this->record->photo)) {
            $service = app(BoardMemberPhotoService::class);
            $result = $service->processGoogleDriveUrl($data['google_drive_url'], $this->record->photo);
            $data['photo'] = $result['main'];
            $data['photo_version'] = $result['version'];
            if (empty($data['photo_consent_at'])) {
                $data['photo_consent_at'] = now()->toDateString();
            }
        } elseif (empty($data['photo']) && ! empty($this->record->photo)) {
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
