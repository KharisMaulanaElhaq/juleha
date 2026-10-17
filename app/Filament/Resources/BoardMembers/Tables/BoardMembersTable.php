<?php

namespace App\Filament\Resources\BoardMembers\Tables;

use App\Models\BoardMember;
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
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class BoardMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->groups([
                Group::make('group')
                    ->label('Kelompok Kepengurusan')
                    ->getTitleFromRecordUsing(fn (BoardMember $record): string => match ($record->group) {
                        BoardMember::GROUP_PELINDUNG_PEMBINA => '1. Pelindung & Pembina (Lembaga)',
                        BoardMember::GROUP_PIMPINAN_HARIAN => '2. Pimpinan Harian (BPH)',
                        BoardMember::GROUP_BIDANG => '3. Bidang-Bidang Kepengurusan',
                        default => 'Lainnya',
                    }),
                Group::make('division')
                    ->label('Bidang Kerja'),
            ])
            ->defaultGroup('group')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('assets/logo-juleha-brebes.png')),

                TextColumn::make('name')
                    ->label('Nama Pengurus / Lembaga')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (BoardMember $record): ?string {
                        if ($record->group === BoardMember::GROUP_BIDANG) {
                            return $record->division ?? 'Bidang Kerja';
                        }

                        return $record->position;
                    }),

                TextColumn::make('group')
                    ->label('Kelompok')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        BoardMember::GROUP_PELINDUNG_PEMBINA => 'Pelindung / Pembina',
                        BoardMember::GROUP_PIMPINAN_HARIAN => 'Pimpinan Harian',
                        BoardMember::GROUP_BIDANG => 'Bidang',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        BoardMember::GROUP_PELINDUNG_PEMBINA => 'primary',
                        BoardMember::GROUP_PIMPINAN_HARIAN => 'success',
                        BoardMember::GROUP_BIDANG => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('division')
                    ->label('Bidang')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('kind')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        BoardMember::KIND_INSTITUTION => 'Lembaga',
                        default => 'Perorangan',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        BoardMember::KIND_INSTITUTION => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Filter Kelompok')
                    ->options([
                        BoardMember::GROUP_PELINDUNG_PEMBINA => 'Pelindung & Pembina',
                        BoardMember::GROUP_PIMPINAN_HARIAN => 'Pimpinan Harian',
                        BoardMember::GROUP_BIDANG => 'Bidang-Bidang',
                    ]),

                SelectFilter::make('division')
                    ->label('Filter Bidang')
                    ->options(function () {
                        return BoardMember::whereNotNull('division')
                            ->distinct()
                            ->pluck('division', 'division')
                            ->toArray();
                    }),

                SelectFilter::make('kind')
                    ->label('Filter Jenis')
                    ->options([
                        BoardMember::KIND_PERSON => 'Perorangan',
                        BoardMember::KIND_INSTITUTION => 'Lembaga',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status Tampil')
                    ->options([
                        '1' => 'Aktif Ditampilkan',
                        '0' => 'Nonaktif / Diarsipkan',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_page')
                        ->label('Lihat di Website')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (): string => route('pengurus'))
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->label('Ubah Data Pengurus'),
                    DeleteAction::make()
                        ->label('Hapus Pengurus')
                        ->modalHeading('Hapus Data Pengurus')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data pengurus ini dari daftar organisasi?')
                        ->modalSubmitActionLabel('Ya, Hapus Sekarang'),
                ])->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Pengurus Terpilih')
                        ->modalDescription('Yakin ingin menghapus seluruh data pengurus yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Pengurus')
            ->emptyStateDescription('Tambahkan struktur Pelindung, Pembina, Pimpinan Harian, dan Bidang DPD JULEHA Brebes.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Tambah Pengurus Baru')
                    ->url(route('filament.admin.resources.board-members.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
