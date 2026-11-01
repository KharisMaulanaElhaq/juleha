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
                        $record->hasPhoto() => 'Ada Foto',
                        default => 'Belum Ada',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Ada Foto' => 'success',
                        'Belum Ada' => 'warning',
                        default => 'gray',
                    }),

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
                    ->label('Filter Foto')
                    ->options([
                        'with_photo' => 'Sudah Berfoto',
                        'without_photo' => 'Belum Ada Foto',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'with_photo') {
                            $query->where('kind', BoardMember::KIND_PERSON)->whereNotNull('photo');
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
                    Action::make('quick_drive_photo')
                        ->label('Pasang Foto Google Drive')
                        ->icon('heroicon-o-camera')
                        ->color('primary')
                        ->visible(fn (BoardMember $record): bool => $record->kind === BoardMember::KIND_PERSON)
                        ->form([
                            \Filament\Forms\Components\TextInput::make('google_drive_url')
                                ->label('Tautan Foto Google Drive')
                                ->placeholder('https://drive.google.com/file/d/.../view')
                                ->required()
                                ->helperText('Pastikan akses file diatur ke "Siapa saja yang memiliki link".'),
                            \Filament\Forms\Components\DatePicker::make('photo_consent_at')
                                ->label('Tanggal Izin/Persetujuan')
                                ->default(now()->toDateString())
                                ->required(),
                            \Filament\Forms\Components\TextInput::make('photo_consent_note')
                                ->label('Catatan Izin')
                                ->default('Diambil dari Google Drive ' . now()->format('d M Y')),
                        ])
                        ->action(function (BoardMember $record, array $data) {
                            try {
                                $service = app(BoardMemberPhotoService::class);
                                $result = $service->processGoogleDriveUrl($data['google_drive_url'], $record->photo);
                                $record->update([
                                    'photo' => $result['main'],
                                    'photo_version' => $result['version'],
                                    'photo_consent_at' => $data['photo_consent_at'],
                                    'photo_consent_note' => $data['photo_consent_note'],
                                ]);
                                Notification::make()
                                    ->title("Foto {$record->name} berhasil dipasang")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal Memasang Foto')
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
