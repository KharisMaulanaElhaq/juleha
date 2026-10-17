<?php

namespace App\Filament\Resources\EventSessions\Pages;

use App\Filament\Resources\EventSessions\EventSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventSession extends EditRecord
{
    protected static string $resource = EventSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
