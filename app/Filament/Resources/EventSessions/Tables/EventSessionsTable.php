<?php

namespace App\Filament\Resources\EventSessions\Tables;

use App\Models\EventSession;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nama Sesi')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (EventSession $record): string => $record->location ? 'Lokasi: '.$record->location : ''),

                TextColumn::make('event.title')
                    ->label('Kegiatan Bimtek')
                    ->limit(28)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('formatted_schedule')
                    ->label('Jadwal (WIB)')
                    ->sortable(['starts_at']),

                TextColumn::make('hadir_count')
                    ->label('Kehadiran')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (EventSession $record): string => $record->hadir_count.' Hadir'),

                ToggleColumn::make('attendance_open')
                    ->label('Presensi Dibuka')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Filter Kegiatan')
                    ->relationship('event', 'title'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('screen')
                        ->label('Buka Layar Absen')
                        ->icon('heroicon-m-qr-code')
                        ->color('primary')
                        ->url(fn (EventSession $record): string => route('admin.attendance.screen', $record), shouldOpenInNewTab: true),

                    Action::make('matrix')
                        ->label('Rekap Presensi')
                        ->icon('heroicon-m-chart-bar')
                        ->color('gray')
                        ->url(fn (EventSession $record): ?string => $record->event ? route('admin.attendance.matrix', $record->event) : null),

                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Sesi Pelatihan')
            ->emptyStateDescription('Buat sesi baru untuk jadwal kegiatan bimtek atau sertifikasi kompetensi.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('starts_at', 'desc');
    }
}
