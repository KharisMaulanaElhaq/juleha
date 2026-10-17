<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Models\Program;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->width('70px'),

                TextColumn::make('title')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Program $record): string => $record->category),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Layanan Masyarakat' => 'success',
                        'Pendidikan & Sertifikasi' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('icon')
                    ->label('Ikon')
                    ->badge()
                    ->color('gray')
                    ->visibleFrom('md'),

                TextColumn::make('lsp_name')
                    ->label('LSP Mitra')
                    ->placeholder('–')
                    ->visibleFrom('lg'),

                TextColumn::make('cta_type')
                    ->label('Aksi CTA')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'bimtek' => 'warning',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'bimtek' => 'Bimtek',
                        default => 'WhatsApp',
                    })
                    ->visibleFrom('md'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'Layanan Masyarakat' => 'Layanan Masyarakat',
                        'Pendidikan & Sertifikasi' => 'Pendidikan & Sertifikasi',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status Tampil')
                    ->options([
                        '1' => 'Aktif (Tayang)',
                        '0' => 'Nonaktif (Draf)',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Program Kerja')
            ->emptyStateDescription('Buat program kerja layanan masyarakat atau pendidikan sembelih halal.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Tambah Program Kerja')
                    ->url(route('filament.admin.resources.programs.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
