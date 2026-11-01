<?php

namespace App\Filament\Resources\BoardMembers\Pages;

use App\Filament\Resources\BoardMembers\BoardMemberResource;
use App\Services\BoardMemberPhotoService;
use Filament\Resources\Pages\CreateRecord;

class CreateBoardMember extends CreateRecord
{
    protected static string $resource = BoardMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['google_drive_url']) && empty($data['photo'])) {
            $service = app(BoardMemberPhotoService::class);
            $result = $service->processGoogleDriveUrl($data['google_drive_url']);
            $data['photo'] = $result['main'];
            $data['photo_version'] = $result['version'];
            if (empty($data['photo_consent_at'])) {
                $data['photo_consent_at'] = now()->toDateString();
            }
        } elseif (! empty($data['photo'])) {
            $data['photo_version'] = time();
        }

        unset($data['google_drive_url']);

        return $data;
    }
}
