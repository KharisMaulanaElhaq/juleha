<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Mail\EnrollmentStatusChangedNotification;
use App\Models\Certificate;
use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PendingRegistrationsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
    ];

    public static function canView(): bool
    {
        $user = Auth::user();

        // Hanya untuk super_admin dan admin_kegiatan; editor_konten tidak dapat melihat
        return $user && $user->isAdminKegiatan();
    }

    public function table(Table $table): Table
    {
        $expiringCertCount = Certificate::expiringSoon(90)->count();
        $unrecordedCertCount = Enrollment::whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
            ->whereHas('event', function ($q) {
                $q->where('type', EventType::SertifikasiKompetensi)
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', now());
            })
            ->whereDoesntHave('certificate')
            ->count();

        $desc = 'Pendaftaran kegiatan berstatus menunggu diperiksa pengurus.';
        if ($expiringCertCount > 0 || $unrecordedCertCount > 0) {
            $desc .= " • Perhatian: {$expiringCertCount} sertifikat segera berakhir (90 hari), {$unrecordedCertCount} hasil sertifikasi selesai belum dicatat.";
        }

        return $table
            ->heading('Perlu Tindakan')
            ->description($desc)
            ->headerActions([
                Action::make('expiring_certificates')
                    ->label(fn (): string => "Sertifikat segera berakhir (90 hari): {$expiringCertCount}")
                    ->icon('heroicon-m-clock')
                    ->color($expiringCertCount > 0 ? 'warning' : 'gray')
                    ->badge(fn (): ?string => $expiringCertCount > 0 ? (string) $expiringCertCount : null)
                    ->url(route('filament.admin.resources.certificates.index', ['tableFilters[status][value]' => 'segera_berakhir']))
                    ->visible(fn (): bool => auth()->user()?->isAdminKegiatan() === true),

                Action::make('unrecorded_certificates')
                    ->label(fn (): string => "Hasil belum dicatat: {$unrecordedCertCount}")
                    ->icon('heroicon-m-exclamation-triangle')
                    ->color($unrecordedCertCount > 0 ? 'danger' : 'gray')
                    ->badge(fn (): ?string => $unrecordedCertCount > 0 ? (string) $unrecordedCertCount : null)
                    ->url(route('filament.admin.resources.events.index', ['tableFilters[event_type][value]' => 'sertifikasi_kompetensi']))
                    ->visible(fn (): bool => auth()->user()?->isAdminKegiatan() === true),

                Action::make('view_all')
                    ->label('Lihat Semua Pendaftaran')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(route('filament.admin.resources.enrollments.index'))
                    ->color('gray'),
            ])
            ->query(
                Enrollment::query()
                    ->where('status', EnrollmentStatus::Menunggu)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('user.profile.full_name_ktp')
                    ->label('Nama Peserta')
                    ->default(fn (Enrollment $record): string => $record->user?->name ?? '-')
                    ->weight('bold')
                    ->description(fn (Enrollment $record): string => $record->user?->profile?->phone ? 'WA: '.$record->user->profile->phone : ($record->user?->email ?? '')),

                TextColumn::make('event.title')
                    ->label('Kegiatan')
                    ->limit(28)
                    ->tooltip(fn ($record) => $record->event?->title),

                TextColumn::make('created_at')
                    ->label('Waktu Daftar')
                    ->since(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Terima')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Terima Pendaftaran')
                    ->modalDescription(fn (Enrollment $record): string => "Setujui pendaftaran {$record->user?->name} untuk kegiatan {$record->event?->title}?")
                    ->modalSubmitActionLabel('Ya, Terima')
                    ->action(function (Enrollment $record): void {
                        $record->update(['status' => EnrollmentStatus::Aktif]);

                        try {
                            if ($record->user?->email) {
                                Mail::to($record->user->email)->queue(new EnrollmentStatusChangedNotification($record));
                            }
                        } catch (\Throwable $e) {
                            // Abaikan kegagalan mailer lokal jika tidak ada koneksi smtp
                        }

                        Notification::make()
                            ->title('Pendaftaran Diterima')
                            ->body("Pendaftaran {$record->user?->name} telah disetujui.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->button()
                    ->size('sm')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Alasan Penolakan')
                            ->placeholder('Contoh: Kuota telah penuh atau data persyaratan belum memenuhi...')
                            ->required(),
                    ])
                    ->modalHeading('Tolak Pendaftaran')
                    ->modalSubmitActionLabel('Tolak Pendaftaran')
                    ->action(function (Enrollment $record, array $data): void {
                        $record->update([
                            'status' => EnrollmentStatus::Ditolak,
                            'admin_notes' => $data['admin_notes'],
                        ]);

                        try {
                            if ($record->user?->email) {
                                Mail::to($record->user->email)->queue(new EnrollmentStatusChangedNotification($record));
                            }
                        } catch (\Throwable $e) {
                        }

                        Notification::make()
                            ->title('Pendaftaran Ditolak')
                            ->body("Pendaftaran {$record->user?->name} telah ditolak.")
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Semua pendaftar sudah diperiksa')
            ->emptyStateDescription('Tidak ada antrean pendaftaran kegiatan yang menunggu verifikasi saat ini.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
