<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Mail\EnrollmentStatusChangedNotification;
use App\Models\AuditLog;
use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.profile.full_name_ktp')
                    ->label('Nama Peserta')
                    ->default(fn (Enrollment $record): string => $record->user?->name ?? '-')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Enrollment $record): string => ($record->user?->profile?->district ? 'Kec. '.$record->user?->profile?->district.' • ' : '').($record->user?->email ?? '')),

                TextColumn::make('user.profile.masked_nik')
                    ->label('NIK (Tersamar)')
                    ->badge()
                    ->color('gray')
                    ->visibleFrom('md'),

                TextColumn::make('user.profile.formatted_phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->visibleFrom('md')
                    ->url(fn (Enrollment $record): ?string => $record->user?->profile?->phone ? 'https://wa.me/'.$record->user?->profile?->phone : null, shouldOpenInNewTab: true),

                TextColumn::make('event.title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->limit(24)
                    ->sortable(),

                TextColumn::make('event.type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?EventType $state): string => $state?->label() ?? '-')
                    ->color(fn (?EventType $state): string => match ($state) {
                        EventType::Bimtek => 'success',
                        EventType::SertifikasiKompetensi => 'info',
                        EventType::Tot => 'warning',
                        default => 'gray',
                    })
                    ->visibleFrom('md'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn (EnrollmentStatus $state): string => match ($state) {
                        EnrollmentStatus::Menunggu => 'heroicon-m-clock',
                        EnrollmentStatus::Aktif => 'heroicon-m-check-circle',
                        EnrollmentStatus::Ditolak => 'heroicon-m-x-circle',
                        EnrollmentStatus::Selesai => 'heroicon-m-check-badge',
                    })
                    ->formatStateUsing(fn (EnrollmentStatus $state): string => $state->label())
                    ->color(fn (EnrollmentStatus $state): string => $state->filamentColor()),

                TextColumn::make('created_at')
                    ->label('Waktu Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->relationship('event', 'title')
                    ->label('Filter Kegiatan'),

                SelectFilter::make('event_type')
                    ->label('Filter Jenis Kegiatan')
                    ->options(collect(EventType::cases())->mapWithKeys(fn (EventType $type) => [$type->value => $type->label()]))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('event', fn (Builder $q) => $q->where('type', $data['value']));
                    }),

                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options(collect(EnrollmentStatus::cases())->mapWithKeys(fn (EnrollmentStatus $status) => [$status->value => $status->label()])),
            ])
            ->recordActions([
                ActionGroup::make([
                    // 1. Konfirmasi Langsung
                    Action::make('confirm')
                        ->label('Konfirmasi Peserta')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Enrollment $record): bool => $record->status !== EnrollmentStatus::Aktif)
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pendaftar')
                        ->modalDescription(fn (Enrollment $record): string => "Setujui pendaftaran {$record->user?->name} untuk kegiatan {$record->event?->title}?")
                        ->modalSubmitActionLabel('Ya, Konfirmasi')
                        ->action(function (Enrollment $record): void {
                            $record->update(['status' => EnrollmentStatus::Aktif]);

                            try {
                                if ($record->user?->email) {
                                    Mail::to($record->user->email)->queue(new EnrollmentStatusChangedNotification($record));
                                }
                            } catch (\Exception $e) {
                            }

                            Notification::make()
                                ->title('Pendaftaran Dikonfirmasi')
                                ->success()
                                ->send();
                        }),

                    // 2. Tolak Pendaftaran
                    Action::make('reject')
                        ->label('Tolak Pendaftaran')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Enrollment $record): bool => $record->status !== EnrollmentStatus::Ditolak)
                        ->form([
                            Textarea::make('admin_notes')
                                ->label('Alasan Penolakan')
                                ->placeholder('Contoh: Kuota telah penuh atau data persyaratan belum memenuhi...')
                                ->required(),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            $record->update([
                                'status' => EnrollmentStatus::Ditolak,
                                'admin_notes' => $data['admin_notes'],
                            ]);

                            try {
                                if ($record->user?->email) {
                                    Mail::to($record->user->email)->queue(new EnrollmentStatusChangedNotification($record));
                                }
                            } catch (\Exception $e) {
                            }

                            Notification::make()
                                ->title('Pendaftaran Ditolak')
                                ->danger()
                                ->send();
                        }),

                    // 3. Tampilkan NIK Lengkap (Tercatat di Audit Log)
                    Action::make('view_nik')
                        ->label('Tampilkan NIK')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->visible(fn (): bool => auth()->user()?->isAdminKegiatan() === true)
                        ->requiresConfirmation()
                        ->modalHeading('Peringatan Privasi: Buka NIK')
                        ->modalDescription('Pembukaan NIK lengkap ini dicatat di tabel audit resmi demi kepatuhan terhadap UU Perlindungan Data Pribadi. Lanjutkan?')
                        ->modalSubmitActionLabel('Buka NIK Lengkap')
                        ->action(function (Enrollment $record): void {
                            $rawNik = $record->user?->profile?->nik;

                            // Catat ke audit log
                            AuditLog::log('view_nik', 'User', $record->user_id, [
                                'enrollment_id' => $record->id,
                                'target_name' => $record->user?->name,
                            ]);

                            Notification::make()
                                ->title('Informasi NIK Peserta')
                                ->body('NIK Lengkap: '.($rawNik ?: 'Belum diisi').' ('.($record->user?->profile?->full_name_ktp ?? $record->user?->name).')')
                                ->warning()
                                ->persistent()
                                ->send();
                        }),

                    // 4. Hubungi WhatsApp
                    Action::make('whatsapp')
                        ->label('Hubungi WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(fn (Enrollment $record): ?string => $record->user?->profile?->phone ? 'https://wa.me/'.$record->user?->profile?->phone : null, shouldOpenInNewTab: true),

                    EditAction::make(),
                ])->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                // Ekspor Standar Tanpa NIK
                Action::make('export_csv')
                    ->label('Ekspor Data Peserta (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (): StreamedResponse {
                        $records = Enrollment::with(['user.profile', 'event'])->latest()->get();
                        $filename = 'pendaftar-kegiatan-juleha-'.date('Y-m-d-His').'.csv';

                        $headers = [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fwrite($handle, "\xEF\xBB\xBF");

                            fputcsv($handle, [
                                'ID Pendaftaran',
                                'Nama Lengkap (KTP)',
                                'Email',
                                'Nomor WhatsApp',
                                'NIK (Tersamar)',
                                'Kecamatan',
                                'Desa/Kelurahan',
                                'Instansi/Asal',
                                'Status Profesi',
                                'Kegiatan',
                                'Jenis Kegiatan',
                                'Status Pendaftaran',
                                'Catatan Admin',
                                'Waktu Mendaftar',
                            ]);

                            foreach ($records as $item) {
                                $profile = $item->user?->profile;
                                fputcsv($handle, [
                                    $item->id,
                                    $profile?->full_name_ktp ?? $item->user?->name ?? '-',
                                    $item->user?->email ?? '-',
                                    $profile?->phone ?? '-',
                                    $profile?->masked_nik ?? '-',
                                    $profile?->district ?? '-',
                                    $profile?->village ?? '-',
                                    $profile?->institution ?? '-',
                                    $profile?->status ?? '-',
                                    $item->event?->title ?? '-',
                                    $item->event?->type?->label() ?? '-',
                                    $item->status->label(),
                                    $item->admin_notes ?? '-',
                                    $item->created_at?->format('Y-m-d H:i:s'),
                                ]);
                            }

                            fclose($handle);
                        };

                        return response()->streamDownload($callback, $filename, $headers);
                    }),

                // Ekspor Khusus Super Admin dengan NIK Lengkap (Tercatat di Audit Log)
                Action::make('export_csv_nik')
                    ->label('Ekspor dengan NIK (Super Admin)')
                    ->icon('heroicon-o-shield-check')
                    ->color('danger')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() === true)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Ekspor Data Sensitif (NIK)')
                    ->modalDescription('Anda akan mengunduh seluruh data pendaftar beserta NIK lengkap untuk keperluan sertifikasi resmi. Ekspor ini akan dicatat secara permanen di Log Audit. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Ekspor dengan NIK')
                    ->action(function (): StreamedResponse {
                        $records = Enrollment::with(['user.profile', 'event'])->latest()->get();

                        AuditLog::log('export_nik', 'Enrollment', null, [
                            'total_records' => $records->count(),
                        ]);

                        $filename = 'pendaftar-juleha-lengkap-nik-'.date('Y-m-d-His').'.csv';
                        $headers = [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fwrite($handle, "\xEF\xBB\xBF");

                            fputcsv($handle, [
                                'ID Pendaftaran',
                                'Nama Lengkap (KTP)',
                                'NIK Lengkap',
                                'Email',
                                'Nomor WhatsApp',
                                'Kecamatan',
                                'Desa/Kelurahan',
                                'Instansi/Asal',
                                'Status Profesi',
                                'Kegiatan',
                                'Jenis Kegiatan',
                                'Status Pendaftaran',
                                'Waktu Mendaftar',
                            ]);

                            foreach ($records as $item) {
                                $profile = $item->user?->profile;
                                fputcsv($handle, [
                                    $item->id,
                                    $profile?->full_name_ktp ?? $item->user?->name ?? '-',
                                    $profile?->nik ?? '-', // NIK Lengkap didekripsi via model cast
                                    $item->user?->email ?? '-',
                                    $profile?->phone ?? '-',
                                    $profile?->district ?? '-',
                                    $profile?->village ?? '-',
                                    $profile?->institution ?? '-',
                                    $profile?->status ?? '-',
                                    $item->event?->title ?? '-',
                                    $item->event?->type?->label() ?? '-',
                                    $item->status->label(),
                                    $item->created_at?->format('Y-m-d H:i:s'),
                                ]);
                            }

                            fclose($handle);
                        };

                        return response()->streamDownload($callback, $filename, $headers);
                    }),

                BulkActionGroup::make([
                    BulkAction::make('bulk_confirm')
                        ->label('Konfirmasi Terpilih')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Massal Pendaftar')
                        ->modalDescription('Ubah status semua peserta terpilih menjadi Aktif/Terkonfirmasi?')
                        ->modalSubmitActionLabel('Ya, Konfirmasi Semua')
                        ->action(function (Collection $records): void {
                            $records->each(function (Enrollment $record): void {
                                $record->update(['status' => EnrollmentStatus::Aktif]);
                                try {
                                    if ($record->user?->email) {
                                        Mail::to($record->user->email)->queue(new EnrollmentStatusChangedNotification($record));
                                    }
                                } catch (\Exception $e) {
                                }
                            });

                            Notification::make()
                                ->title('Peserta Terpilih Berhasil Dikonfirmasi')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulk_reject')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Massal Pendaftar')
                        ->modalDescription('Ubah status semua peserta terpilih menjadi Ditolak?')
                        ->modalSubmitActionLabel('Ya, Tolak Semua')
                        ->action(function (Collection $records): void {
                            $records->each(function (Enrollment $record): void {
                                $record->update(['status' => EnrollmentStatus::Ditolak]);
                            });

                            Notification::make()
                                ->title('Peserta Terpilih Telah Ditolak')
                                ->danger()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->visible(fn (): bool => auth()->user()?->isSuperAdmin() === true),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Pendaftaran Masuk')
            ->emptyStateDescription('Peserta yang mendaftar kegiatan melalui portal peserta akan ditampilkan di sini.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
