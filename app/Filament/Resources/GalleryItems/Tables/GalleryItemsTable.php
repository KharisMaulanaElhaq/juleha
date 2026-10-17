<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Models\GalleryItem;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->square()
                    ->size(54)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                TextColumn::make('title')
                    ->label('Judul Dokumentasi')
                    ->searchable()
                    ->sortable()
                    ->description(fn (GalleryItem $record): string => $record->event?->title ? 'Bimtek: '.$record->event->title : ''),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Bimtek & Pelatihan' => 'primary',
                        'Uji Kompetensi' => 'success',
                        'Pengawasan Kurban' => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'Bimtek & Pelatihan' => 'Bimtek & Pelatihan',
                        'Uji Kompetensi' => 'Uji Kompetensi',
                        'Pengawasan Kurban' => 'Pengawasan Kurban',
                        'Silaturahmi & Raker' => 'Silaturahmi & Raker',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_gallery')
                        ->label('Lihat di Galeri Publik')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (): string => route('galeri'))
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->label('Ubah Foto'),
                    DeleteAction::make()
                        ->label('Hapus Foto')
                        ->modalHeading('Hapus Foto Dokumentasi')
                        ->modalDescription('Apakah Anda yakin ingin menghapus foto kegiatan ini dari galeri?')
                        ->modalSubmitActionLabel('Ya, Hapus Sekarang'),
                ])->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Foto Terpilih')
                        ->modalDescription('Yakin ingin menghapus seluruh dokumentasi foto yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Dokumentasi Foto')
            ->emptyStateDescription('Abadikan momen bimtek, uji kompetensi, atau kegiatan kurban DPD JULEHA Brebes.')
            ->emptyStateIcon('heroicon-o-camera')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Unggah Foto Baru')
                    ->url(route('filament.admin.resources.gallery-items.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
