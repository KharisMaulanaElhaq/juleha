<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Models\Registration;
use App\Support\CsvSanitizer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('full_name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Registration $record): string => ($record->district ?? '').($record->phone ? ' • '.$record->phone : '')),
                TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('district')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('event.title')
                    ->label('Bimtek')
                    ->searchable()
                    ->limit(25)
                    ->visibleFrom('md'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Registration::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'attended' => 'info',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->relationship('event', 'title')
                    ->label('Filter Bimtek'),
                SelectFilter::make('status')
                    ->options(Registration::statusLabels())
                    ->label('Filter Status'),
                SelectFilter::make('district')
                    ->label('Filter Kecamatan')
                    ->options([
                        'Banjarharjo' => 'Banjarharjo',
                        'Bantarkawung' => 'Bantarkawung',
                        'Brebes' => 'Brebes',
                        'Bulakamba' => 'Bulakamba',
                        'Bumiayu' => 'Bumiayu',
                        'Jatibarang' => 'Jatibarang',
                        'Kersana' => 'Kersana',
                        'Ketanggungan' => 'Ketanggungan',
                        'Larangan' => 'Larangan',
                        'Losari' => 'Losari',
                        'Paguyangan' => 'Paguyangan',
                        'Salem' => 'Salem',
                        'Sirampog' => 'Sirampog',
                        'Songgom' => 'Songgom',
                        'Tanjung' => 'Tanjung',
                        'Tonjong' => 'Tonjong',
                        'Wanasari' => 'Wanasari',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('confirm_direct')
                        ->label('Konfirmasi Peserta')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn (Registration $record): bool => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pendaftar')
                        ->modalDescription(fn (Registration $record): string => "Setujui {$record->full_name} sebagai peserta resmi bimtek?")
                        ->modalSubmitActionLabel('Ya, Setujui')
                        ->action(function (Registration $record): void {
                            $record->update(['status' => 'confirmed']);
                        }),
                    Action::make('change_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label('Status Baru')
                                ->options(Registration::statusLabels())
                                ->required(),
                        ])
                        ->action(function (Registration $record, array $data): void {
                            $record->update(['status' => $data['status']]);
                        }),
                    Action::make('whatsapp')
                        ->label('Hubungi WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(fn (Registration $record): string => "https://wa.me/{$record->phone}", shouldOpenInNewTab: true),
                    EditAction::make(),
                ])->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                Action::make('export_csv')
                    ->label('Ekspor Excel / CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (): StreamedResponse {
                        $records = Registration::with('event')->latest()->get();
                        $filename = 'registrasi-bimtek-juleha-'.date('Y-m-d-His').'.csv';

                        $headers = [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fwrite($handle, "\xEF\xBB\xBF");

                            fputcsv($handle, CsvSanitizer::sanitizeRow([
                                'Kode Registrasi',
                                'Nama Lengkap',
                                'Nomor WhatsApp',
                                'Email',
                                'Kecamatan',
                                'Desa/Kelurahan',
                                'Kategori Peserta',
                                'Status',
                                'Kegiatan Bimtek',
                                'Waktu Pendaftaran',
                            ]));

                            foreach ($records as $reg) {
                                fputcsv($handle, CsvSanitizer::sanitizeRow([
                                    $reg->code,
                                    $reg->full_name,
                                    $reg->phone,
                                    $reg->email ?? '-',
                                    $reg->district,
                                    $reg->village ?? '-',
                                    $reg->participant_type_label,
                                    $reg->status_label,
                                    $reg->event?->title ?? '-',
                                    $reg->created_at->format('Y-m-d H:i:s'),
                                ]));
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
                        ->modalDescription('Setujui semua peserta terpilih menjadi status terkonfirmasi?')
                        ->modalSubmitActionLabel('Ya, Konfirmasi')
                        ->action(function (Collection $records): void {
                            $records->each(fn ($record) => $record->update(['status' => 'confirmed']));
                        }),
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Pendaftar')
                        ->modalDescription('Data yang dihapus tidak dapat dipulihkan. Lanjutkan?'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Pendaftar')
            ->emptyStateDescription('Pendaftar baru yang mengisi formulir bimbingan teknis di situs akan langsung terdaftar di sini.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
