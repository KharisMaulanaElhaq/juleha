<?php

namespace App\Filament\Resources\Certificates\Schemas;

use App\Models\Certificate;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Hasil Asesmen & Sertifikat')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('participant_name')
                            ->label('Nama Peserta')
                            ->content(fn (?Certificate $record): string => $record?->user?->name ?? '-'),

                        Placeholder::make('event_title')
                            ->label('Kegiatan Sertifikasi')
                            ->content(fn (?Certificate $record): string => $record?->event?->title ?? '-'),

                        Select::make('result')
                            ->label('Hasil Asesmen Kompetensi')
                            ->options([
                                Certificate::RESULT_KOMPETEN => 'Kompeten',
                                Certificate::RESULT_BELUM_KOMPETEN => 'Belum Kompeten',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpan(1),

                        TextInput::make('scheme_name')
                            ->label('Nama Skema / Unit Kompetensi')
                            ->required()
                            ->placeholder('Contoh: Juru Sembelih Halal (Juleha) Level 2')
                            ->helperText('Diisi nama skema atau unit kompetensi resmi dari LSP.')
                            ->columnSpan(1),

                        TextInput::make('issuer_name')
                            ->label('Lembaga Penerbit Sertifikat (LSP)')
                            ->default(fn () => Setting::get('cert_issuer_name', 'LSP Pertanian RI'))
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('certificate_number')
                            ->label('Nomor Sertifikat')
                            ->placeholder('Contoh: 001/LSP-P/BNSP/2026')
                            ->required(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                            ->visible(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                            ->unique(Certificate::class, 'certificate_number', ignoreRecord: true)
                            ->columnSpan(1),

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
                            })
                            ->columnSpan(1),

                        DatePicker::make('valid_until')
                            ->label('Masa Berlaku Sampai')
                            ->visible(fn ($get) => $get('result') === Certificate::RESULT_KOMPETEN)
                            ->helperText('Dihitung 3 tahun sejak tanggal terbit (dapat dikoreksi admin).')
                            ->columnSpan(1),

                        Textarea::make('internal_note')
                            ->label('Catatan Internal Panitia (Opsional)')
                            ->placeholder('Catatan asesmen, nomor batch LSP, dsb.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status Dokumen Digital')
                    ->columns(2)
                    ->visible(fn (?Certificate $record) => $record && $record->isCompetent())
                    ->schema([
                        Placeholder::make('file_status')
                            ->label('Status Berkas PDF')
                            ->content(fn (?Certificate $record): string => $record?->file_path ? 'Sudah Diunggah ('.$record->file_name.')' : 'Belum Ada Berkas'),

                        Placeholder::make('file_size_formatted')
                            ->label('Ukuran Berkas')
                            ->content(fn (?Certificate $record): string => $record?->file_size ? number_format($record->file_size / 1024, 1).' KB' : '-'),

                        Placeholder::make('file_hash')
                            ->label('SHA-256 Hash')
                            ->content(fn (?Certificate $record): string => $record?->file_sha256 ?? '-')
                            ->columnSpanFull(),

                        Placeholder::make('public_url')
                            ->label('Tautan Verifikasi Publik')
                            ->content(fn (?Certificate $record): string => $record?->public_code ? url('/verifikasi/'.$record->public_code) : '-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Pencabutan')
                    ->visible(fn (?Certificate $record) => $record && $record->isRevoked())
                    ->schema([
                        Placeholder::make('revoked_at_display')
                            ->label('Dicabut Pada')
                            ->content(fn (?Certificate $record): string => $record?->revoked_at?->format('d M Y H:i') ?? '-'),

                        Placeholder::make('revoked_reason_display')
                            ->label('Alasan Pencabutan')
                            ->content(fn (?Certificate $record): string => $record?->revoked_reason ?? '-'),
                    ]),
            ]);
    }
}
