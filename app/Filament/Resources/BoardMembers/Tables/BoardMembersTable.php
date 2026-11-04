<?php

namespace App\Filament\Resources\BoardMembers\Tables;

use App\Models\BoardMember;
use App\Services\BoardMemberPhotoService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoardMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->description(function (): string {
                $total = BoardMember::where('kind', BoardMember::KIND_PERSON)->where('is_active', true)->count();
                $withPhoto = BoardMember::where('kind', BoardMember::KIND_PERSON)->where('is_active', true)->whereNotNull('photo')->count();

                return "{$withPhoto} dari {$total} pengurus sudah berfoto";
            })
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
                    ->disk('public')
                    ->getStateUsing(fn (BoardMember $record): ?string => $record->thumb_path ?? $record->photo)
                    ->defaultImageUrl(asset('assets/logo-juleha-brebes.png'))
                    ->height(45)
                    ->width(36)
                    ->extraImgAttributes(['class' => 'object-cover rounded-md aspect-[4/5]']),

                TextColumn::make('photo_status')
                    ->label('Status Foto')
                    ->badge()
                    ->state(fn (BoardMember $record): string => match (true) {
                        $record->isInstitution() => 'Lembaga',
                        $record->isPhotoMissing() => 'File Tidak Ditemukan',
                        $record->hasPhoto() => 'Foto Siap (4:5)',
                        default => 'Belum Ada Foto',
                    })
                    ->icon(fn (string $state): ?string => match ($state) {
                        'File Tidak Ditemukan' => 'heroicon-o-exclamation-triangle',
                        'Foto Siap (4:5)' => 'heroicon-o-check-circle',
                        'Belum Ada Foto' => 'heroicon-o-camera',
                        default => null,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Foto Siap (4:5)' => 'success',
                        'File Tidak Ditemukan' => 'danger',
                        'Belum Ada Foto' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn (BoardMember $record): ?string => $record->isPhotoMissing()
                        ? 'File foto tercatat di database tetapi tidak ditemukan di penyimpanan server. Silakan unggah ulang foto pengurus ini.'
                        : null
                    ),

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

                SelectFilter::make('photo_status')
                    ->label('Filter Status Foto')
                    ->options([
                        'valid' => 'Foto Valid (Ada di Penyimpanan)',
                        'missing' => 'File Hilang / Rusak (Perlu Diperbaiki)',
                        'without_photo' => 'Belum Ada Foto',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'valid') {
                            $validIds = BoardMember::where('kind', BoardMember::KIND_PERSON)
                                ->whereNotNull('photo')
                                ->get()
                                ->filter(fn ($m) => $m->photoExists())
                                ->pluck('id')
                                ->toArray();
                            $query->whereIn('id', $validIds);
                        } elseif (($data['value'] ?? null) === 'missing') {
                            $missingIds = BoardMember::where('kind', BoardMember::KIND_PERSON)
                                ->whereNotNull('photo')
                                ->get()
                                ->filter(fn ($m) => $m->isPhotoMissing())
                                ->pluck('id')
                                ->toArray();
                            $query->whereIn('id', $missingIds);
                        } elseif (($data['value'] ?? null) === 'without_photo') {
                            $query->where('kind', BoardMember::KIND_PERSON)->whereNull('photo');
                        }
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
                    Action::make('reprocess_crop')
                        ->label('Atur Ulang Pemotongan')
                        ->icon('heroicon-o-scissors')
                        ->color('info')
                        ->visible(fn (BoardMember $record): bool => ! empty($record->photo) && app(BoardMemberPhotoService::class)->hasOriginal($record->photo))
                        ->requiresConfirmation()
                        ->modalHeading('Atur Ulang Pemotongan Foto')
                        ->modalDescription('Proses ulang foto pengurus ke rasio 4:5 menggunakan berkas asli bersih di penyimpanan privat?')
                        ->modalSubmitActionLabel('Ya, Proses Ulang')
                        ->action(function (BoardMember $record) {
                            try {
                                $service = app(BoardMemberPhotoService::class);
                                $result = $service->reprocessFromOriginal($record->photo);
                                $record->update([
                                    'photo' => $result['main'],
                                    'photo_version' => $result['version'],
                                ]);
                                Notification::make()
                                    ->title('Pemotongan foto berhasil diatur ulang')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal mengatur ulang pemotongan')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('delete_photo')
                        ->label('Hapus Foto')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->visible(fn (BoardMember $record): bool => ! empty($record->photo))
                        ->requiresConfirmation()
                        ->modalHeading(fn (BoardMember $record) => "Hapus Foto {$record->name}?")
                        ->modalDescription('Apakah Anda yakin ingin menghapus foto pengurus ini beserta seluruh berkas WebP turunannya?')
                        ->modalSubmitActionLabel('Ya, Hapus Foto')
                        ->action(function (BoardMember $record) {
                            app(BoardMemberPhotoService::class)->deletePhotoFiles($record->photo);
                            $record->update([
                                'photo' => null,
                                'photo_consent_at' => null,
                                'photo_consent_note' => null,
                                'photo_version' => time(),
                            ]);
                            Notification::make()
                                ->title('Foto berhasil dihapus')
                                ->success()
                                ->send();
                        }),
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
