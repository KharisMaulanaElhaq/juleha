<?php

namespace App\Filament\Resources\LegalDocuments\Tables;

use App\Models\AuditLog;
use App\Models\LegalDocument;
use App\Models\LegalDocumentVersion;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Dokumen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (LegalDocument $record): string => route('legal.show', $record->slug, false)),

                TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LegalDocument::STATUS_PUBLISHED => 'success',
                        LegalDocument::STATUS_DRAFT => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        LegalDocument::STATUS_PUBLISHED => 'Terbit (Publik)',
                        LegalDocument::STATUS_DRAFT => 'Draf (Tersembunyi)',
                        default => $state,
                    }),

                TextColumn::make('effective_date')
                    ->label('Berlaku Sejak')
                    ->date('d M Y')
                    ->sortable(),

                IconColumn::make('is_mandatory_consent')
                    ->label('Consent Wajib')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('published_at')
                    ->label('Diterbitkan Pada')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('Belum terbit')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Publikasi')
                    ->options([
                        LegalDocument::STATUS_PUBLISHED => 'Terbit',
                        LegalDocument::STATUS_DRAFT => 'Draf',
                    ]),
            ])
            ->actions([
                EditAction::make(),

                Action::make('publish_version')
                    ->label('Terbitkan Versi Baru')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (LegalDocument $record): string => "Terbitkan Versi Baru: {$record->title}")
                    ->modalDescription('Aksi ini akan mengarsipkan versi saat ini ke riwayat perubahan dan menerbitkan versi baru ke publik.')
                    ->form([
                        TextInput::make('new_version')
                            ->label('Nomor Versi Baru')
                            ->required()
                            ->placeholder('Contoh: 1.1 atau 2.0')
                            ->helperText('Nomor versi harus lebih tinggi dari versi saat ini.'),
                        DatePicker::make('effective_date')
                            ->label('Tanggal Berlaku Efektif')
                            ->default(now())
                            ->required(),
                        Textarea::make('change_summary')
                            ->label('Ringkasan Perubahan Versi Baru')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan poin pasal atau ketentuan apa saja yang diperbarui pada versi ini...'),
                    ])
                    ->action(function (LegalDocument $record, array $data): void {
                        // 1. Simpan riwayat versi lama jika sudah berstatus terbit
                        if ($record->isPublished()) {
                            LegalDocumentVersion::create([
                                'legal_document_id' => $record->id,
                                'version' => $record->version,
                                'effective_date' => $record->effective_date,
                                'content' => $record->content,
                                'change_summary' => $record->change_summary,
                                'published_by' => $record->published_by,
                                'published_at' => $record->published_at,
                            ]);
                        }

                        // 2. Perbarui dokumen ke versi baru & status published
                        $record->update([
                            'version' => $data['new_version'],
                            'effective_date' => $data['effective_date'],
                            'change_summary' => $data['change_summary'],
                            'status' => LegalDocument::STATUS_PUBLISHED,
                            'published_by' => auth()->id(),
                            'published_at' => now(),
                        ]);

                        // 3. Jika dokumen wajib persetujuan, perbarui versi aktif untuk pemicu re-consent
                        if ($record->is_mandatory_consent) {
                            Setting::set("consent_version_{$record->slug}", $data['new_version']);
                            Setting::set('active_global_consent_version', "v_{$record->slug}_{$data['new_version']}_".time());
                        }

                        AuditLog::log('publish_legal_document_version', 'LegalDocument', $record->id, [
                            'slug' => $record->slug,
                            'new_version' => $data['new_version'],
                        ]);

                        Notification::make()
                            ->title('Versi Baru Berhasil Diterbitkan')
                            ->body("Dokumen {$record->title} kini telah terbit dengan Versi {$data['new_version']}.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
