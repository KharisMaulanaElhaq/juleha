<?php

namespace App\Filament\Resources\BoardMembers\Pages;

use App\Filament\Resources\BoardMembers\BoardMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBoardMember extends CreateRecord
{
    protected static string $resource = BoardMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['photo'])) {
            $data['photo_version'] = time();
        }

        unset($data['google_drive_url']);

        return $data;
    }
}
