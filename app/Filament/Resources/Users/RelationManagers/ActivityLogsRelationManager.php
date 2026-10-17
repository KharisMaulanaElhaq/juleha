<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\AuditLog;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    protected static ?string $title = 'Riwayat Aktivitas & Perubahan Akun';

    protected static ?string $modelLabel = 'Log Aktivitas';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Perubahan')
                    ->dateTime('d M Y H:i:s WIB')
                    ->sortable(),

                TextColumn::make('actor.name')
                    ->label('Dilakukan Oleh')
                    ->default(fn (AuditLog $record): string => $record->actor?->name ?? 'Sistem / Otomatis')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('action')
                    ->label('Jenis Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Akun Dibuat',
                        'updated' => 'Data Diperbarui',
                        'role_changed' => 'Peran Diubah',
                        'suspended' => 'Dinonaktifkan',
                        'activated' => 'Diaktifkan Kembali',
                        'password_reset' => 'Reset Kata Sandi',
                        'anonymized' => 'Dianonimkan (UU PDP)',
                        'deleted' => 'Dihapus (Soft Delete)',
                        'restored' => 'Dipulihkan',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'created', 'activated', 'restored' => 'success',
                        'role_changed', 'password_reset' => 'warning',
                        'suspended', 'anonymized', 'deleted' => 'danger',
                        default => 'info',
                    }),

                TextColumn::make('meta_summary')
                    ->label('Rincian Perubahan')
                    ->state(function (AuditLog $record): string {
                        $meta = $record->meta ?? [];
                        if (empty($meta)) {
                            return '–';
                        }

                        $parts = [];
                        if (isset($meta['old_role'], $meta['new_role'])) {
                            $parts[] = "Peran: {$meta['old_role']} → {$meta['new_role']}";
                        }
                        if (isset($meta['reason'])) {
                            $parts[] = "Alasan: {$meta['reason']}";
                        }
                        if (isset($meta['password_option'])) {
                            $parts[] = "Opsi sandi: {$meta['password_option']}";
                        }
                        if (isset($meta['name'])) {
                            $parts[] = "Nama: {$meta['name']}";
                        }
                        if (isset($meta['email'])) {
                            $parts[] = "Email: {$meta['email']}";
                        }

                        return ! empty($parts) ? implode(' • ', $parts) : json_encode($meta, JSON_UNESCAPED_UNICODE);
                    })
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Catatan Aktivitas')
            ->emptyStateDescription('Perubahan peran, penonaktifan, atau tindakan pengurus terhadap akun ini akan dicatat di sini.');
    }
}
