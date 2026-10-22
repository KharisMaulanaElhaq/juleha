<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use App\Services\EnrollmentService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Event $record): string => ($record->starts_at?->format('d M Y') ?? '-').($record->location ? ' • '.$record->location : '')),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?EventType $state): string => $state?->label() ?? '-')
                    ->color(fn (?EventType $state): string => match ($state) {
                        EventType::Bimtek => 'success',
                        EventType::SertifikasiKompetensi => 'info',
                        EventType::Tot => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('starts_at')
                    ->label('Jadwal Pelaksanaan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(30)
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('quota')
                    ->label('Kuota')
                    ->state(fn (Event $record): string => $record->usedSeats().'/'.($record->quota ?? '∞'))
                    ->description(fn (Event $record): string => is_null($record->quota) ? 'Tanpa batas' : 'Sisa '.$record->seatsLeft().' kursi')
                    ->badge()
                    ->color(fn (Event $record): string => match (true) {
                        $record->isFull() => 'danger',
                        $record->seatsLeft() !== null && $record->seatsLeft() <= 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable()
                    ->visibleFrom('sm'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Event $record): string => $record->registrationStateLabel())
                    ->icon(fn (Event $record): string => $record->registrationState()->filamentIcon())
                    ->color(fn (Event $record): string => $record->registrationState()->filamentColor()),
                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Pendaftar')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filter Jenis Kegiatan')
                    ->options(collect(EventType::cases())->mapWithKeys(fn (EventType $type) => [$type->value => $type->label()])),
                SelectFilter::make('registration_state')
                    ->label('Filter Status Pendaftaran')
                    ->options([
                        'open' => 'Dibuka',
                        'full' => 'Penuh',
                        'closed' => 'Ditutup',
                        'finished' => 'Selesai',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $state = $data['value'];
                        $now = Carbon::now('Asia/Jakarta');

                        return match ($state) {
                            'open' => $query->where('status', 'open')
                                ->where(fn ($q) => $q->whereNull('registration_deadline')->orWhere('registration_deadline', '>', $now))
                                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now))
                                ->where(fn ($q) => $q->whereNull('quota')->orWhereRaw('(SELECT COUNT(*) FROM enrollments WHERE enrollments.event_id = events.id AND enrollments.status IN ("menunggu", "aktif", "selesai")) < events.quota')),
                            'closed' => $query->where('status', '!=', 'open')->where('status', '!=', 'done'),
                            'finished' => $query->where(fn ($q) => $q->where('status', 'done')->orWhere('ends_at', '<=', $now)),
                            'full' => $query->where('status', 'open')->whereNotNull('quota')
                                ->whereRaw('(SELECT COUNT(*) FROM enrollments WHERE enrollments.event_id = events.id AND enrollments.status IN ("menunggu", "aktif", "selesai")) >= events.quota'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('quick_screen')
                    ->label('Layar QR Presensi')
                    ->tooltip('Buka Layar Presensi QR untuk Proyektor')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('warning')
                    ->button()
                    ->size('xs')
                    ->visible(fn (Event $record): bool => $record->sessions()->exists())
                    ->url(function (Event $record): ?string {
                        $todaySession = $record->sessions()
                            ->whereDate('starts_at', today('Asia/Jakarta'))
                            ->first();
                        $targetSession = $todaySession ?? $record->sessions()->first();

                        return $targetSession ? route('admin.attendance.screen', $targetSession->id) : null;
                    }, shouldOpenInNewTab: true),

                ActionGroup::make([
                    Action::make('manual_enroll_bypass')
                        ->label('Tambah Peserta (Lewati Kuota)')
                        ->icon('heroicon-o-user-plus')
                        ->color('warning')
                        ->visible(fn (): bool => auth()->user()?->isAdminKegiatan() ?? false)
                        ->form([
                            Select::make('user_id')
                                ->label('Pilih Peserta')
                                ->searchable()
                                ->preload()
                                ->options(fn (Event $record) => User::participants()
                                    ->whereDoesntHave('enrollments', fn ($q) => $q->where('event_id', $record->id))
                                    ->pluck('name', 'id'))
                                ->required()
                                ->helperText('Hanya menampilkan akun peserta yang belum terdaftar pada kegiatan ini.'),
                            Textarea::make('admin_notes')
                                ->label('Alasan / Catatan Penambahan')
                                ->placeholder('Contoh: Penambahan kuota khusus dispensasi dinas / pengurus DKM.')
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Tambah Peserta (Lewati Kuota)')
                        ->modalDescription(fn (Event $record): string => "Peringatan: Aksi ini akan mendaftarkan peserta secara langsung ke kegiatan '{$record->title}' meskipun kuota telah penuh ({$record->usedSeats()}/".($record->quota ?? '∞').'). Kuota kegiatan akan terlampaui dan aksi ini akan dicatat dalam log audit pengurus.')
                        ->modalSubmitActionLabel('Tetap Daftarkan Peserta')
                        ->action(function (Event $record, array $data): void {
                            $user = User::findOrFail($data['user_id']);
                            app(EnrollmentService::class)->manualEnrollBypassingQuota(
                                $user,
                                $record,
                                $data['admin_notes'],
                                auth()->id()
                            );

                            Notification::make()
                                ->title('Peserta Berhasil Ditambahkan')
                                ->body("Peserta {$user->name} berhasil didaftarkan ke kegiatan {$record->title} melalui kuota khusus.")
                                ->success()
                                ->send();
                        }),

                    Action::make('view_public')
                        ->label('Lihat di Website')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('gray')
                        ->url(fn (Event $record): string => route('bimtek.show', $record->slug), shouldOpenInNewTab: true),
                    Action::make('attendance_matrix')
                        ->label('Rekap Presensi')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->url(fn (Event $record): string => route('admin.attendance.matrix', $record->id), shouldOpenInNewTab: true),
                    EditAction::make(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Jadwal Bimtek')
                        ->modalDescription('Apakah Anda yakin ingin menghapus kegiatan ini?'),
                ])->tooltip('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Jadwal Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Jadwal Bimtek')
            ->emptyStateDescription('Buat jadwal pelatihan baru agar masyarakat dan pengurus DKM dapat mendaftar online.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Tambah Jadwal Bimtek')
                    ->url(route('filament.admin.resources.events.create'))
                    ->button()
                    ->color('primary'),
            ]);
    }
}
