<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Foto & Dokumentasi Kegiatan')
                    ->description('Unggah dokumentasi foto kegiatan pelatihan, sertifikasi, atau syiar JULEHA.')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Judul Dokumentasi Foto')
                                ->placeholder('Contoh: Praktik Asah Bilah & Sembelih Halal Angkatan 12')
                                ->helperText('Keterangan singkat momen foto kegiatan.')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),

                            Select::make('category')
                                ->label('Kategori Album')
                                ->options([
                                    'Bimtek & Pelatihan' => 'Bimtek & Pelatihan',
                                    'Uji Kompetensi' => 'Uji Kompetensi BNSP',
                                    'Pengawasan Kurban' => 'Pengawasan Daging Kurban',
                                    'Silaturahmi & Raker' => 'Silaturahmi & Rakerda',
                                ])
                                ->helperText('Kelompokkan foto ke dalam album yang sesuai.')
                                ->required()
                                ->columnSpan(1),

                            FileUpload::make('image')
                                ->label('Berkas Foto')
                                ->image()
                                ->imageEditor()
                                ->directory('gallery')
                                ->maxSize(4096)
                                ->helperText('Unggah foto resolusi baik (JPG, PNG, WebP maks. 4MB). Pratinjau akan langsung terlihat.')
                                ->required()
                                ->columnSpanFull(),

                            Select::make('event_id')
                                ->relationship('event', 'title')
                                ->label('Kaitkan ke Jadwal Bimtek (Opsional)')
                                ->placeholder('-- Pilih Kegiatan Bimtek Terkait --')
                                ->helperText('Pilih jika foto ini merupakan bagian dari agenda bimtek tertentu.')
                                ->columnSpan(1),

                            TextInput::make('sort_order')
                                ->label('Nomor Urut Tampil')
                                ->numeric()
                                ->default(0)
                                ->helperText('Semakin kecil angka (0, 1, 2...), semakin awal foto tampil di galeri.')
                                ->columnSpan(1),

                            Textarea::make('caption')
                                ->label('Deskripsi / Cerita Dokumentasi')
                                ->placeholder('Tuliskan rincian lokasi, peserta, atau suasana dalam foto...')
                                ->helperText('Keterangan tambahan yang muncul saat pengunjung mengklik foto.')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
