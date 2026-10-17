<?php

namespace App\Filament\Resources\Materials\Tables;

use App\Models\Material;
use App\Services\MaterialService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MaterialsTable
{
    public static function configure(Table $table, bool $insideRelationManager = false): Table
    {
        $columns = [
            TextColumn::make('sort_order')
                ->label('Urutan')
                ->sortable()
                ->alignCenter()
                ->width('60px'),

            TextColumn::make('title')
                ->label('Judul Materi')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->description(fn (Material $record): string => Str::limit($record->description ?? '', 60)),
        ];

        if (! $insideRelationManager) {
            $columns[] = TextColumn::make('event.title')
                ->label('Kegiatan')
                ->searchable()
                ->sortable()
                ->limit(30)
                ->visibleFrom('md');
        }

        $columns = array_merge($columns, [
            TextColumn::make('kind')
                ->label('Tipe')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'file' => 'Berkas Privat',
                    'link' => 'Tautan Daring',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'file' => 'primary',
                    'link' => 'info',
                    default => 'gray',
                }),

            TextColumn::make('formatted_size')
                ->label('Ukuran')
                ->alignCenter()
                ->visibleFrom('lg'),

            TextColumn::make('downloads_count')
                ->counts('downloads')
                ->label('Unduhan')
                ->badge()
                ->color('gray')
                ->alignCenter(),

            ToggleColumn::make('is_published')
                ->label('Terbit')
                ->alignCenter(),

            TextColumn::make('published_at')
                ->label('Tgl Terbit')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->visibleFrom('xl'),
        ]);

        $filters = [];
        if (! $insideRelationManager) {
            $filters[] = SelectFilter::make('event_id')
                ->label('Filter Kegiatan')
                ->relationship('event', 'title')
                ->searchable()
                ->preload();
        }

        $filters[] = SelectFilter::make('kind')
            ->label('Filter Tipe')
            ->options([
                'file' => 'Berkas Privat',
                'link' => 'Tautan Daring',
            ]);

        $filters[] = SelectFilter::make('is_published')
            ->label('Status Terbit')
            ->options([
                '1' => 'Terbit',
                '0' => 'Draf (Belum Terbit)',
            ]);

        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns($columns)
            ->filters($filters)
            ->actions([
                ActionGroup::make([
                    Action::make('notify_participants')
                        ->label('Kirim Email Notifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('purple')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Notifikasi Materi ke Peserta')
                        ->modalDescription(fn (Material $record): string => "Kirimkan email notifikasi berkas '{$record->title}' ke seluruh peserta aktif kegiatan ini?")
                        ->visible(fn (Material $record): bool => $record->is_published)
                        ->action(function (Material $record): void {
                            $count = MaterialService::notifyActiveParticipants($record);
                            Notification::make()
                                ->title("Notifikasi materi berhasil dikirim ke {$count} peserta aktif.")
                                ->success()
                                ->send();
                        }),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Materi Pembelajaran')
            ->emptyStateDescription('Unggah modul, slide presentasi, atau SOP juru sembelih halal untuk peserta.')
            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Unggah Materi Baru')
                    ->url(route('filament.admin.resources.materials.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
