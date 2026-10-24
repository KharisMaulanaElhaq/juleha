<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalDocument extends CreateRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === 'published') {
            $data['published_by'] = auth()->id();
            $data['published_at'] = now();
        }

        return $data;
    }
}
