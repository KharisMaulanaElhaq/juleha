<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Enums\EventType;
use App\Models\Certificate;
use App\Services\CertificateService;
use App\Support\CsvSanitizer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Peserta')
                    ->description(fn (Certificate $record): string => $record->user?->email ?? '-')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('event.title')
                    ->label('Kegiatan')
                    ->limit(24)
                    ->tooltip(fn (Certificate $record): ?string => $record->event?->title)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scheme_name')
                    ->label('Skema LSP')
                    ->limit(22)
                    ->tooltip(fn (Certificate $record): ?string => $record->scheme_name)
                    ->searchable(),

                TextColumn::make('certificate_number')
                    ->label('Nomor Sertifikat')
                    ->default('-')
                    ->searchable()
                    ->badge(fn (Certificate $record): bool => (bool) $record->certificate_number)
                    ->color('gray'),

                TextColumn::make('issued_at')
                    ->label('Terbit')
                    ->date('d/m/Y')
                    ->default('-')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->date('d/m/Y')
                    ->default('-')
                    ->sortable(),

                TextColumn::make('computed_status')
                    ->label('Status')
                    ->state(fn (Certificate $record): string => $record->status_label)
                    ->badge()
                    ->icon(fn (Certificate $record): string => $record->status_filament_icon)
                    ->color(fn (Certificate $record): string => $record->status_filament_color),

                TextColumn::make('file_path')
                    ->label('Berkas PDF')
                    ->formatStateUsing(fn ($state) => $state ? 'Tersedia' : 'Belum Ada')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        Certificate::STATUS_AKTIF => 'Aktif',
                        Certificate::STATUS_SEGERA_BERAKHIR => 'Segera Berakhir (<= 90 Hari)',
                        Certificate::STATUS_KEDALUWARSA => 'Kedaluwarsa',
                        Certificate::STATUS_DICABUT => 'Dicabut',
                        Certificate::STATUS_BELUM_KOMPETEN => 'Belum Kompeten',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            Certificate::STATUS_AKTIF => $query->active(),
                            Certificate::STATUS_SEGERA_BERAKHIR => $query->expiringSoon(90),
                            Certificate::STATUS_KEDALUWARSA => $query->expired(),
                            Certificate::STATUS_DICABUT => $query->revoked(),
                            Certificate::STATUS_BELUM_KOMPETEN => $query->where('result', Certificate::RESULT_BELUM_KOMPETEN),
                            default => $query,
                        };
                    }),

                SelectFilter::make('event_id')
                    ->label('Filter Kegiatan')
                    ->relationship('event', 'title', fn (Builder $q) => $q->where('type', EventType::SertifikasiKompetensi)),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),

                    // Aksi Unggah Berkas PDF (Jika belum ada berkas dan hasil kompeten)
                    Action::make('upload_pdf')
                        ->label('Unggah Sertifikat (PDF)')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('primary')
                        ->visible(fn (Certificate $record): bool => $record->isCompetent() && empty($record->file_path))
                        ->form([
                            FileUpload::make('pdf_file')
                                ->label('File Sertifikat Digital LSP (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->helperText('Hanya file PDF asli (Maks. 5 MB). File disimpan di storage privat aman.')
                                ->required(),
                        ])
                        ->action(function (Certificate $record, array $data): void {
                            /** @var UploadedFile $file */
                            $file = $data['pdf_file'];
                            try {
                                CertificateService::uploadPdf($record, $file);
                                Notification::make()
                                    ->title('Sertifikat Berhasil Diunggah')
                                    ->body('File PDF sertifikat LSP telah disimpan secara privat dan hash SHA-256 telah diverifikasi.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal Mengunggah')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Aksi Ganti Berkas PDF
                    Action::make('replace_pdf')
                        ->label('Ganti File PDF')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (Certificate $record): bool => $record->isCompetent() && filled($record->file_path))
                        ->form([
                            FileUpload::make('pdf_file')
                                ->label('File Sertifikat Pengganti (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->helperText('Mengganti file lama dengan file baru. File lama akan dihapus permanen.')
                                ->required(),
                        ])
                        ->action(function (Certificate $record, array $data): void {
                            /** @var UploadedFile $file */
                            $file = $data['pdf_file'];
                            try {
                                CertificateService::uploadPdf($record, $file, isReplacement: true);
                                Notification::make()
                                    ->title('File Sertifikat Diperbarui')
                                    ->body('File sertifikat lama telah diganti dengan file baru.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal Mengganti File')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Aksi Unduh Berkas untuk Admin
                    Action::make('download_pdf')
                        ->label('Unduh Sertifikat (Admin)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->visible(fn (Certificate $record): bool => filled($record->file_path))
                        ->action(fn (Certificate $record): StreamedResponse => CertificateService::downloadForAdmin($record)),

                    // Aksi Cabut Sertifikat (Dua Langkah)
                    Action::make('revoke')
                        ->label('Cabut Sertifikat')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Certificate $record): bool => ! $record->isRevoked() && $record->isCompetent())
                        ->form([
                            Textarea::make('reason')
                                ->label('Alasan Pencabutan Sertifikat')
                                ->placeholder('Contoh: Terjadi pelanggaran kode etik / pembatalan oleh LSP...')
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pencabutan Sertifikat')
                        ->modalDescription('Apakah Anda yakin ingin mencabut sertifikat ini? Status di verifikasi publik akan berubah menjadi "Dicabut" dan peserta tidak dapat lagi mengunduh berkas.')
                        ->modalSubmitActionLabel('Ya, Cabut Sertifikat')
                        ->action(function (Certificate $record, array $data): void {
                            CertificateService::revoke($record, $data['reason']);
                            Notification::make()
                                ->title('Sertifikat Telah Dicabut')
                                ->body('Sertifikat berhasil ditandai sebagai dicabut.')
                                ->warning()
                                ->send();
                        }),

                    // Aksi Pulihkan Sertifikat yang Dicabut
                    Action::make('restore_revoked')
                        ->label('Pulihkan Sertifikat')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('info')
                        ->visible(fn (Certificate $record): bool => $record->isRevoked())
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pemulihan Sertifikat')
                        ->modalDescription('Pulihkan status sertifikat ini kembali ke status aktif normal?')
                        ->modalSubmitActionLabel('Ya, Pulihkan')
                        ->action(function (Certificate $record): void {
                            CertificateService::restoreRevoked($record);
                            Notification::make()
                                ->title('Sertifikat Dipulihkan')
                                ->body('Status pencabutan telah dihapus.')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('Hapus Catatan')
                        ->visible(fn (): bool => auth()->user()?->isSuperAdmin() === true),
                ]),
            ])
            ->toolbarActions([
                // Ekspor Excel (CSV UTF-8 dengan BOM) TANPA NIK
                Action::make('export_excel')
                    ->label('Ekspor Excel (Tanpa NIK)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (): StreamedResponse {
                        $records = Certificate::with(['user', 'event'])->latest()->get();
                        $filename = 'daftar-sertifikat-juleha-'.date('Y-m-d-His').'.csv';

                        $headers = [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            // Tulis BOM UTF-8 agar karakter Indonesia terbuka rapi di Microsoft Excel
                            fwrite($handle, "\xEF\xBB\xBF");

                            fputcsv($handle, CsvSanitizer::sanitizeRow([
                                'ID Sertifikat',
                                'Nama Peserta',
                                'Email',
                                'Kegiatan',
                                'Skema LSP',
                                'Nomor Sertifikat',
                                'Penerbit Sertifikat',
                                'Hasil',
                                'Tanggal Terbit',
                                'Berlaku Sampai',
                                'Status',
                                'Tersedia Berkas',
                                'Kode Verifikasi Publik',
                            ]));

                            foreach ($records as $item) {
                                fputcsv($handle, CsvSanitizer::sanitizeRow([
                                    $item->id,
                                    $item->user?->name ?? '-',
                                    $item->user?->email ?? '-',
                                    $item->event?->title ?? '-',
                                    $item->scheme_name,
                                    $item->certificate_number ?? '-',
                                    $item->issuer_name,
                                    $item->result === Certificate::RESULT_KOMPETEN ? 'Kompeten' : 'Belum Kompeten',
                                    $item->issued_at?->format('Y-m-d') ?? '-',
                                    $item->valid_until?->format('Y-m-d') ?? '-',
                                    $item->status_label,
                                    $item->file_path ? 'Ya' : 'Tidak',
                                    $item->public_code,
                                ]));
                            }

                            fclose($handle);
                        };

                        return response()->streamDownload($callback, $filename, $headers);
                    }),
            ]);
    }
}
