<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
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

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Pertanyaan FAQ')
                    ->searchable()
                    ->sortable()
                    ->limit(55)
                    ->description(fn (Faq $record): string => $record->category),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Bimbingan Teknis' => 'primary',
                        'Fiqih Sembelih' => 'success',
                        'Kurban & Jasa' => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->visibleFrom('md'),
                IconColumn::make('is_active')
                    ->label('Status Tampil')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'Umum & Organisasi' => 'Umum & Organisasi',
                        'Bimbingan Teknis' => 'Bimbingan Teknis',
                        'Fiqih Sembelih' => 'Fiqih Sembelih',
                        'Kurban & Jasa' => 'Kurban & Jasa',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status Tayang')
                    ->options([
                        '1' => 'Aktif Ditampilkan',
                        '0' => 'Nonaktif / Draf',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_faq')
                        ->label('Lihat di Website')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (): string => route('faq'))
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->label('Ubah FAQ'),
                    DeleteAction::make()
                        ->label('Hapus FAQ')
                        ->modalHeading('Hapus Tanya Jawab FAQ')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pertanyaan ini dari daftar FAQ?')
                        ->modalSubmitActionLabel('Ya, Hapus Sekarang'),
                ])->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus FAQ Terpilih')
                        ->modalDescription('Yakin ingin menghapus seluruh FAQ yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Tanya Jawab (FAQ)')
            ->emptyStateDescription('Buat pertanyaan umum seputar bimtek, fiqih sembelih, atau layanan JULEHA Brebes.')
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Tambah Tanya Jawab')
                    ->url(route('filament.admin.resources.faqs.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
