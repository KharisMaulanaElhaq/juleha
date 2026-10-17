<?php

namespace App\Filament\Resources\Materials\Pages;

use App\Filament\Resources\Materials\MaterialResource;
use App\Services\MaterialService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterial extends CreateRecord
{
    protected static string $resource = MaterialResource::class;

    protected function afterCreate(): void
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
