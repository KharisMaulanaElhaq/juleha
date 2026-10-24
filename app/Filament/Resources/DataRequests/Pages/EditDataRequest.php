<?php

namespace App\Filament\Resources\DataRequests\Pages;

use App\Filament\Resources\DataRequests\DataRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDataRequest extends EditRecord
{
    protected static string $resource = DataRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['handled_by'])) {
            $data['handled_by'] = auth()->id();
        }

        if (($data['status'] ?? null) === 'selesai' && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        return $data;
    }
}
