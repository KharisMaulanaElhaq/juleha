<?php

namespace App\Filament\Resources\DataRequests\Tables;

use App\Mail\DataRequestResolvedMail;
use App\Models\AuditLog;
use App\Models\DataRequest;
use App\Services\UserAnonymizationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DataRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('No. Registrasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact')
                    ->label('Kontak')
                    ->searchable(),

                TextColumn::make('request_type')
                    ->label('Jenis Hak')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => DataRequest::typeLabels()[$state] ?? $state),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DataRequest::STATUS_NEW => 'warning',
                        DataRequest::STATUS_VERIFICATION => 'gray',
                        DataRequest::STATUS_PROCESSING => 'primary',
                        DataRequest::STATUS_COMPLETED => 'success',
                        DataRequest::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => DataRequest::statusLabels()[$state] ?? $state),

                TextColumn::make('due_at')
                    ->label('Tenggat SLA')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->badge(fn (DataRequest $record): bool => $record->isOverdue())
                    ->color(fn (DataRequest $record): string => $record->isOverdue() ? 'danger' : 'gray')
                    ->description(fn (DataRequest $record): ?string => $record->isOverdue() ? 'Lewat Tenggat!' : null),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Permohonan')
                    ->options(DataRequest::statusLabels()),

                SelectFilter::make('request_type')
                    ->label('Jenis Hak Data')
                    ->options(DataRequest::typeLabels()),

                Filter::make('overdue')
                    ->label('Lewat Tenggat Waktu (Overdue)')
                    ->query(fn (Builder $query): Builder => $query->where('due_at', '<', now())
                        ->whereNotIn('status', [DataRequest::STATUS_COMPLETED, DataRequest::STATUS_REJECTED])),
            ])
            ->actions([
                EditAction::make(),

                // Aksi Selesaikan Permintaan
                Action::make('resolve')
                    ->label('Selesaikan Permintaan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (DataRequest $record): bool => ! in_array($record->status, [DataRequest::STATUS_COMPLETED, DataRequest::STATUS_REJECTED], true))
                    ->form([
                        Textarea::make('resolution_notes')
                            ->label('Catatan Jawaban / Tindakan Petugas')
                            ->required()
                            ->rows(3)
                            ->placeholder('Tuliskan rincian tindakan atau hasil konfirmasi yang diberikan kepada pemohon...'),
                    ])
                    ->action(function (DataRequest $record, array $data): void {
                        $record->update([
                            'status' => DataRequest::STATUS_COMPLETED,
                            'resolution_notes' => $data['resolution_notes'],
                            'handled_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);

                        AuditLog::log('data_request_resolved', 'DataRequest', $record->id, [
                            'request_number' => $record->request_number,
                            'status' => 'selesai',
                        ]);

                        // Kirim email notifikasi jika kontak berupa email
                        if (str_contains($record->contact, '@')) {
                            try {
                                Mail::to($record->contact)->send(new DataRequestResolvedMail($record));
                            } catch (\Throwable $e) {
                                Log::warning('Gagal kirim email penyelesaian: '.$e->getMessage());
                            }
                        }

                        Notification::make()
                            ->title('Permohonan Telah Diselesaikan')
                            ->body("Permohonan {$record->request_number} telah ditandai selesai.")
                            ->success()
                            ->send();
                    }),

                // Aksi Khusus: Anonimkan Akun Peserta Terdaftar (untuk permohonan hapus)
                Action::make('anonymize_user')
                    ->label('Anonimkan Akun Peserta')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn (DataRequest $record): bool => $record->request_type === DataRequest::TYPE_ERASURE && $record->user_id !== null && ! in_array($record->status, [DataRequest::STATUS_COMPLETED, DataRequest::STATUS_REJECTED], true))
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Anonimisasi Akun Pemohon')
                    ->modalDescription('Aksi ini akan menghapus permanen seluruh data sensitif (Nama, NIK, WhatsApp, Domisili, Email) dan menggantinya dengan akun anonim. Berkas fisik PDF sertifikat akan dihapus dan catatan nomor sertifikat dipertahankan tanpa identitas.')
                    ->action(function (DataRequest $record, UserAnonymizationService $service): void {
                        $user = $record->user;
                        if (! $user) {
                            Notification::make()->title('Pengguna tidak ditemukan')->danger()->send();

                            return;
                        }

                        $service->anonymize($user, "Permohonan Hak Data PDP [{$record->request_number}]", auth()->id());

                        $record->update([
                            'status' => DataRequest::STATUS_COMPLETED,
                            'resolution_notes' => "Akun dan seluruh data pribadi peserta telah berhasil dianonimkan secara permanen sesuai ketentuan UU PDP atas permohonan {$record->request_number}.",
                            'handled_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);

                        AuditLog::log('data_request_anonymized_executed', 'DataRequest', $record->id, [
                            'request_number' => $record->request_number,
                        ]);

                        Notification::make()
                            ->title('Akun Berhasil Dianonimkan')
                            ->body("Data pribadi pemohon {$record->name} telah dihapus permanen dan permohonan ditandai selesai.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
