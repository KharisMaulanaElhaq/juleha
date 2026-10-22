<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Setting;
use App\Services\CertificateService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Hasil dan Sertifikat';

    protected static ?string $modelLabel = 'Hasil dan Sertifikat Peserta';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Event && $ownerRecord->type === EventType::SertifikasiKompetensi;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
                ->with(['certificate', 'user.profile'])
            )
            ->columns([
                TextColumn::make('user.profile.full_name_ktp')
                    ->label('Nama Peserta')
                    ->default(fn (Enrollment $record): string => $record->user?->name ?? '-')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Enrollment $record): string => ($record->user?->profile?->phone ? 'WA: '.$record->user->profile->phone.' • ' : '').($record->user?->email ?? '')),

                TextColumn::make('status')
                    ->label('Pendaftaran')
                    ->badge()
                    ->formatStateUsing(fn (EnrollmentStatus $state): string => $state->label())
                    ->color(fn (EnrollmentStatus $state): string => $state->filamentColor()),

                TextColumn::make('certificate_result')
                    ->label('Hasil Asesmen')
                    ->state(function (Enrollment $record): string {
                        if (! $record->certificate) {
                            return 'Belum Dicatat';
                        }

                        return $record->certificate->isCompetent() ? 'Kompeten' : 'Belum Kompeten';
                    })
                    ->badge()
                    ->color(function (Enrollment $record): string {
                        if (! $record->certificate) {
                            return 'warning';
                        }

                        return $record->certificate->isCompetent() ? 'success' : 'gray';
                    }),

                TextColumn::make('certificate.certificate_number')
                    ->label('Nomor Sertifikat')
                    ->default('-')
                    ->badge(fn (Enrollment $record): bool => (bool) $record->certificate?->certificate_number)
                    ->color('gray'),

                TextColumn::make('certificate_status')
                    ->label('Status Sertifikat')
                    ->state(function (Enrollment $record): string {
                        if (! $record->certificate) {
                            return 'Belum Dicatat';
                        }

                        return $record->certificate->status_label;
                    })
                    ->badge()
                    ->color(function (Enrollment $record): string {
                        if (! $record->certificate) {
                            return 'gray';
                        }

                        return $record->certificate->status_filament_color;
                    }),

                TextColumn::make('certificate_file')
                    ->label('Berkas PDF')
                    ->state(fn (Enrollment $record): string => $record->certificate?->file_path ? 'Tersedia' : 'Belum Ada')
                    ->badge()
                    ->color(fn (Enrollment $record): string => $record->certificate?->file_path ? 'success' : 'gray'),
            ])
            ->actions([
                ActionGroup::make([
                    // Aksi 1: Catat / Perbarui Hasil Kompetensi
                    Action::make('record_result')
                        ->label(fn (Enrollment $record): string => $record->certificate ? 'Ubah Hasil Asesmen' : 'Catat Hasil Asesmen')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->fillForm(fn (Enrollment $record): array => [
                            'result' => $record->certificate?->result ?? Certificate::RESULT_KOMPETEN,
                            'scheme_name' => $record->certificate?->scheme_name ?? ($this->getOwnerRecord()->title ?? 'Juru Sembelih Halal (Juleha)'),
                            'certificate_number' => $record->certificate?->certificate_number ?? '',
                            'issued_at' => $record->certificate?->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                            'valid_until' => $record->certificate?->valid_until?->format('Y-m-d') ?? now()->addYears((int) Setting::get('cert_validity_years', 3))->format('Y-m-d'),
                            'internal_note' => $record->certificate?->internal_note ?? '',
                        ])
                        ->form([
                            Select::make('result')
                                ->label('Hasil Asesmen')
                                ->options([
                                    Certificate::RESULT_KOMPETEN => 'Kompeten',
                                    Certificate::RESULT_BELUM_KOMPETEN => 'Belum Kompeten',
                                ])
                                ->required()
                                ->reactive(),

                            TextInput::make('scheme_name')
                                ->label('Nama Skema / Unit Kompetensi (LSP)')
                                ->required()
                                ->helperText('Diisi nama skema sesuai sertifikat resmi dari LSP.')
                                ->columnSpanFull(),

                            TextInput::make('certificate_number')
                                ->label('Nomor Sertifikat BNSP')
                                ->placeholder('Contoh: 001/LSP-P/BNSP/2026')
                                ->required(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                                ->visible(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN),

                            DatePicker::make('issued_at')
                                ->label('Tanggal Terbit')
                                ->maxDate(now())
                                ->required(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                                ->visible(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $years = (int) Setting::get('cert_validity_years', 3);
                                        $set('valid_until', Carbon::parse($state)->addYears($years)->format('Y-m-d'));
                                    }
                                }),

                            DatePicker::make('valid_until')
                                ->label('Masa Berlaku Sampai')
                                ->visible(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                                ->helperText('Dihitung 3 tahun sejak tanggal terbit (dapat dikoreksi admin).'),

                            Textarea::make('internal_note')
                                ->label('Catatan Panitia (Opsional)')
                                ->placeholder('Catatan asesor, nomor batch, dsb.')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            try {
                                CertificateService::recordResult($record, $data);
                                Notification::make()
                                    ->title('Hasil Asesmen Tersimpan')
                                    ->body('Hasil kompetensi peserta telah dicatat ke sistem.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal Menyimpan Hasil')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Aksi 2: Unggah Sertifikat (PDF)
                    Action::make('upload_pdf')
                        ->label('Unggah Sertifikat (PDF)')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('success')
                        ->visible(fn (Enrollment $record): bool => $record->certificate?->isCompetent() && empty($record->certificate?->file_path))
                        ->form([
                            FileUpload::make('pdf_file')
                                ->label('Berkas Sertifikat LSP (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->helperText('Hanya file PDF asli (Maks. 5 MB). File disimpan di disk privat aman.')
                                ->required(),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            /** @var UploadedFile $file */
                            $file = $data['pdf_file'];
                            try {
                                CertificateService::uploadPdf($record->certificate, $file);
                                Notification::make()
                                    ->title('Sertifikat Berhasil Diunggah')
                                    ->body('File PDF tersimpan secara privat dan status pendaftaran ditandai selesai.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal Mengunggah Berkas')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Aksi 3: Ganti File
                    Action::make('replace_pdf')
                        ->label('Ganti File PDF')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (Enrollment $record): bool => $record->certificate?->isCompetent() && filled($record->certificate?->file_path))
                        ->form([
                            FileUpload::make('pdf_file')
                                ->label('File Sertifikat Pengganti (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->helperText('Mengganti file lama dengan file baru. File lama akan dihapus permanen.')
                                ->required(),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            /** @var UploadedFile $file */
                            $file = $data['pdf_file'];
                            try {
                                CertificateService::uploadPdf($record->certificate, $file, isReplacement: true);
                                Notification::make()
                                    ->title('File Sertifikat Diperbarui')
                                    ->body('File lama berhasil digantikan.')
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

                    // Aksi 4: Unduh Berkas (Admin)
                    Action::make('download_pdf')
                        ->label('Unduh Sertifikat (Admin)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn (Enrollment $record): bool => filled($record->certificate?->file_path))
                        ->action(fn (Enrollment $record): StreamedResponse => CertificateService::downloadForAdmin($record->certificate)),

                    // Aksi 5: Cabut Sertifikat (Dua Langkah)
                    Action::make('revoke')
                        ->label('Cabut Sertifikat')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Enrollment $record): bool => $record->certificate && ! $record->certificate->isRevoked() && $record->certificate->isCompetent())
                        ->form([
                            Textarea::make('reason')
                                ->label('Alasan Pencabutan')
                                ->placeholder('Tuliskan alasan pencabutan sertifikat...')
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pencabutan Sertifikat')
                        ->modalDescription('Apakah Anda yakin ingin mencabut sertifikat ini? Status sertifikat akan menjadi Dicabut.')
                        ->modalSubmitActionLabel('Ya, Cabut')
                        ->action(function (Enrollment $record, array $data): void {
                            CertificateService::revoke($record->certificate, $data['reason']);
                            Notification::make()
                                ->title('Sertifikat Telah Dicabut')
                                ->warning()
                                ->send();
                        }),

                    // Aksi 6: Pulihkan
                    Action::make('restore_revoked')
                        ->label('Pulihkan Sertifikat')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('info')
                        ->visible(fn (Enrollment $record): bool => (bool) $record->certificate?->isRevoked())
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pemulihan')
                        ->modalDescription('Pulihkan status sertifikat yang dicabut menjadi aktif kembali?')
                        ->modalSubmitActionLabel('Ya, Pulihkan')
                        ->action(function (Enrollment $record): void {
                            CertificateService::restoreRevoked($record->certificate);
                            Notification::make()
                                ->title('Sertifikat Dipulihkan')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                // Ekspor Data Peserta untuk LSP (Khusus Super Admin)
                Action::make('export_for_lsp')
                    ->label('Ekspor Data Peserta untuk LSP')
                    ->icon('heroicon-o-shield-check')
                    ->color('danger')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() === true)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Ekspor Data Peserta untuk LSP')
                    ->modalDescription('Anda akan mengunduh data peserta kegiatan sertifikasi ini yang memuat data pribadi sensitif (Nama sesuai KTP, NIK lengkap, Kontak WhatsApp, Email, dan Domisili) untuk diteruskan kepada pihak LSP Pertanian RI. Aksi ini akan dicatat dalam Log Audit resmi. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Ekspor Data untuk LSP')
                    ->action(function (): StreamedResponse {
                        /** @var Event $event */
                        $event = $this->getOwnerRecord();
                        $enrollments = $event->enrollments()
                            ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
                            ->with(['user.profile'])
                            ->get();

                        AuditLog::log('exported_for_lsp', 'Event', $event->id, [
                            'event_title' => $event->title,
                            'total_records' => $enrollments->count(),
                        ]);

                        $filename = 'data-peserta-lsp-'.date('Y-m-d-His').'.csv';
                        $headers = [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($enrollments) {
                            $handle = fopen('php://output', 'w');
                            fwrite($handle, "\xEF\xBB\xBF");

                            // Header kolom yang dikonfigurasi
                            fputcsv($handle, [
                                'No',
                                'Nama Sesuai KTP',
                                'NIK',
                                'Nomor WhatsApp',
                                'Email',
                                'Kecamatan',
                                'Desa/Kelurahan',
                            ]);

                            $no = 1;
                            foreach ($enrollments as $enrollment) {
                                $profile = $enrollment->user?->profile;

                                fputcsv($handle, [
                                    $no++,
                                    $profile?->full_name_ktp ?? $enrollment->user?->name ?? '-',
                                    $profile?->nik ?? '-', // NIK didekripsi via model cast
                                    $profile?->phone ?? '-',
                                    $enrollment->user?->email ?? '-',
                                    $profile?->district ?? '-',
                                    $profile?->village ?? '-',
                                ]);
                            }

                            fclose($handle);
                        };

                        return response()->streamDownload($callback, $filename, $headers);
                    }),
            ]);
    }
}
