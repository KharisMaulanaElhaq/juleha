<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('assets/logo-juleha-brebes.png')),
                TextColumn::make('title')
                    ->label('Judul Artikel')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->description(fn (Post $record): string => ($record->author_name ? 'Oleh: '.$record->author_name : '')),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Fiqih Sembelih' => 'success',
                        'Teknis & Alat' => 'warning',
                        'Edukasi Konsumen' => 'info',
                        default => 'primary',
                    })
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('published_at')
                    ->label('Waktu Rilis')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'Fiqih Sembelih' => 'Fiqih Sembelih',
                        'Teknis & Alat' => 'Teknis & Alat',
                        'Edukasi Konsumen' => 'Edukasi Konsumen',
                        'Warta Organisasi' => 'Warta Organisasi',
                    ]),
                SelectFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->options([
                        '1' => 'Diterbitkan (Online)',
                        '0' => 'Draf (Belum Tayang)',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_public')
                        ->label('Buka di Website')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Post $record): string => route('berita.show', $record->slug))
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->label('Ubah Artikel'),
                    DeleteAction::make()
                        ->label('Hapus Artikel')
                        ->modalHeading('Hapus Artikel Berita')
                        ->modalDescription('Apakah Anda yakin ingin menghapus artikel ini? Data yang terhapus tidak dapat dipulihkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Sekarang'),
                ])->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Artikel Terpilih')
                        ->modalDescription('Yakin ingin menghapus seluruh artikel yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Artikel atau Berita')
            ->emptyStateDescription('Mulai bagikan warta kegiatan organisasi, panduan fiqih sembelih halal, atau edukasi masyarakat Brebes.')
            ->emptyStateIcon('heroicon-o-newspaper')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Tulis Artikel Baru')
                    ->url(route('filament.admin.resources.posts.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
