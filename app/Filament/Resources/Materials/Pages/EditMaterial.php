<?php

namespace App\Filament\Resources\Materials\Pages;

use App\Filament\Resources\Materials\MaterialResource;
use App\Services\MaterialService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMaterial extends EditRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if (($this->data['send_notification'] ?? false) && $this->record->is_published) {
            $count = MaterialService::notifyActiveParticipants($this->record);
            if ($count > 0) {
                Notification::make()
                    ->title("Notifikasi materi dikirim ke {$count} peserta aktif.")
                    ->success()
                    ->send();
            }
        }
    }
}
