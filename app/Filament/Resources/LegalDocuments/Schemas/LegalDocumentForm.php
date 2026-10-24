<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use App\Models\LegalDocument;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Dokumen Hukum')
                    ->description('Kelola dokumen hukum, kebijakan privasi, dan ketentuan kepesertaan DPD JULEHA Brebes.')
                    ->icon('heroicon-o-scale')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Judul Dokumen')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: Kebijakan Privasi & Perlindungan Data Pribadi')
                                ->columnSpan(1),

                            Select::make('slug')
                                ->label('Identifier Dokumen (Slug / URL)')
                                ->options([
                                    LegalDocument::SLUG_KEBIJAKAN_PRIVASI => 'kebijakan-privasi (/kebijakan-privasi)',
                                    LegalDocument::SLUG_SYARAT_KETENTUAN => 'syarat-ketentuan (/syarat-ketentuan)',
                                    LegalDocument::SLUG_PENYIMPANAN_PERANGKAT => 'penyimpanan-perangkat (/penyimpanan-perangkat)',
                                    LegalDocument::SLUG_DISCLAIMER => 'disclaimer (/disclaimer)',
                                    LegalDocument::SLUG_HAK_CIPTA => 'hak-cipta-dan-atribusi (/hak-cipta)',
                                ])
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->helperText('Menentukan rute publik halaman dokumen.')
                                ->columnSpan(1),

                            TextInput::make('version')
                                ->label('Versi Dokumen')
                                ->default('1.0')
                                ->required()
                                ->placeholder('1.0')
                                ->helperText('Format versi angka atau desimal (mis. 1.0, 1.1, 2.0).')
                                ->columnSpan(1),

                            DatePicker::make('effective_date')
                                ->label('Tanggal Berlaku Efektif')
                                ->default(now())
                                ->required()
                                ->columnSpan(1),

                            Select::make('status')
                                ->label('Status Publikasi')
                                ->options([
                                    LegalDocument::STATUS_DRAFT => 'Draf (TIDAK tampil di publik)',
                                    LegalDocument::STATUS_PUBLISHED => 'Terbit (Tampil di publik)',
                                ])
                                ->default(LegalDocument::STATUS_DRAFT)
                                ->required()
                                ->helperText('Dokumen berstatus Draf tidak dapat diakses publik atau bot mesin pencari (404).')
                                ->columnSpan(1),

                            Toggle::make('is_mandatory_consent')
                                ->label('Wajib Disetujui Peserta (Consent Wajib)')
                                ->helperText('Jika aktif, saat versi baru diterbitkan, seluruh peserta diminta persetujuan ulang saat masuk portal.')
                                ->default(false)
                                ->columnSpan(1),

                            Textarea::make('change_summary')
                                ->label('Ringkasan Perubahan Versi')
                                ->placeholder('Tuliskan ringkasan pembaruan pasal atau kebijakan...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Naskah Lengkap Dokumen')
                    ->description('Teks dokumen hukum lengkap dalam Bahasa Indonesia yang mudah dipahami.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('content')
                            ->label('Isi Dokumen (Mendukung Tag HTML / Paragraf)')
                            ->rows(20)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
