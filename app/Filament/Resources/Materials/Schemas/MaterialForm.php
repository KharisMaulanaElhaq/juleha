<?php

namespace App\Filament\Resources\Materials\Schemas;

use App\Models\Material;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MaterialForm
{
    public static function configure(Schema $schema, bool $insideRelationManager = false): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Materi Pelatihan')
                    ->description('Tentukan kegiatan, judul materi, dan deskripsi ringkas untuk peserta.')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('event_id')
                                ->label('Kegiatan Bimtek Terkait')
                                ->relationship('event', 'title')
                                ->searchable()
                                ->preload()
                                ->required(! $insideRelationManager)
                                ->hidden($insideRelationManager)
                                ->helperText('Pilih kegiatan pelatihan yang memiliki materi ini.')
                                ->columnSpan(2),

                            TextInput::make('title')
                                ->label('Judul Materi')
                                ->placeholder('Contoh: Modul Fiqih Sembelih Halal & Standar Syariah')
                                ->helperText('Nama modul atau materi yang akan tampil pada peserta.')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            Textarea::make('description')
                                ->label('Deskripsi / Petunjuk Belajar (Opsional)')
                                ->placeholder('Tuliskan ringkasan isi berkas atau panduan membaca materi ini...')
                                ->helperText('Petunjuk singkat bagi peserta sebelum mempelajari atau mengunduh.')
                                ->rows(3)
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Sumber Berkas / Tautan Materi')
                    ->description('Pilih apakah materi berupa berkas unggahan privat atau tautan daring.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        Select::make('kind')
                            ->label('Tipe Sumber Materi')
                            ->options([
                                'file' => 'Berkas Dokumen Privat (PDF, PPT, DOC, XLS, MP4, Gambar)',
                                'link' => 'Tautan Daring Eksternal (YouTube, Google Drive, Zoom, dll.)',
                            ])
                            ->default('file')
                            ->required()
                            ->live(),

                        FileUpload::make('file_path')
                            ->label('Unggah Berkas Privat')
                            ->disk('private')
                            ->directory('materials')
                            ->visibility('private')
                            ->preserveFilenames(false)
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'video/mp4',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(51200)
                            ->helperText('Batas maksimal berkas 50 MB (PDF, PPT/PPTX, DOC/DOCX, XLS/XLSX, MP4, Gambar). Nama file diacak dan tersimpan di disk privat storage/app/private.')
                            ->visible(fn (Get $get) => ($get('kind') ?? 'file') === 'file')
                            ->required(fn (Get $get, ?Material $record) => ($get('kind') ?? 'file') === 'file' && empty($record?->path_or_url))
                            ->columnSpanFull(),

                        TextInput::make('external_url')
                            ->label('Alamat URL Tautan Eksternal')
                            ->placeholder('https://www.youtube.com/watch?v=... atau https://drive.google.com/...')
                            ->helperText('Masukkan URL video YouTube, Google Drive, atau materi daring eksternal.')
                            ->url()
                            ->visible(fn (Get $get) => $get('kind') === 'link')
                            ->required(fn (Get $get) => $get('kind') === 'link')
                            ->columnSpanFull(),
                    ]),

                Section::make('Penerbitan & Notifikasi')
                    ->description('Atur status tayang kepada peserta dan pengiriman email otomatis.')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('sort_order')
                                ->label('Nomor Urutan')
                                ->numeric()
                                ->default(0)
                                ->helperText('Urutan tampil materi (bisa juga diatur drag di tabel).')
                                ->columnSpan(1),

                            Toggle::make('is_published')
                                ->label('Terbitkan ke Peserta')
                                ->helperText('Aktifkan agar dapat dilihat dan diunduh oleh peserta resmi.')
                                ->default(true)
                                ->columnSpan(1),

                            Toggle::make('send_notification')
                                ->label('Kirim Email Notifikasi')
                                ->helperText('Kirim email notifikasi antrean ke peserta aktif saat disimpan.')
                                ->default(true)
                                ->dehydrated(false)
                                ->columnSpan(1),
                        ]),
                    ]),
            ]);
    }
}
