<?php

namespace App\Filament\Resources\BoardMembers\Pages;

use App\Filament\Resources\BoardMembers\BoardMemberResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoardMembers extends ListRecords
{
    protected static string $resource = BoardMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public')
                ->label('Lihat Halaman Publik')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('pengurus'))
                ->openUrlInNewTab(),
            CreateAction::make()
                ->label('Tambah Pengurus Baru'),
        ];
    }
}
