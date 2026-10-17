<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Filament\Resources\Materials\Schemas\MaterialForm;
use App\Filament\Resources\Materials\Tables\MaterialsTable;
use App\Models\Material;
use App\Services\MaterialService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $title = 'Materi Pembelajaran';

    protected static ?string $modelLabel = 'Materi Pembelajaran';

    public function form(Schema $schema): Schema
    {
        return MaterialForm::configure($schema, insideRelationManager: true);
    }

    public function table(Table $table): Table
    {
        return MaterialsTable::configure($table, insideRelationManager: true)
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Materi')
                    ->modalHeading('Tambah Materi Kegiatan')
                    ->after(function (Material $record, array $data): void {
                        if (($data['send_notification'] ?? false) && $record->is_published) {
                            $count = MaterialService::notifyActiveParticipants($record);
                            if ($count > 0) {
                                Notification::make()
                                    ->title("Notifikasi materi dikirim ke {$count} peserta aktif.")
                                    ->success()
                                    ->send();
                            }
                        }
                    }),
            ]);
    }
}
