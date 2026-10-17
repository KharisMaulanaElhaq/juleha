<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Isi & Konten Artikel')
                    ->description('Tuliskan naskah berita, panduan fiqih, atau informasi kegiatan DPD.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Judul Artikel')
                                ->placeholder('Contoh: Panduan Fiqih Penyembelihan Hewan Kurban')
                                ->helperText('Judul artikel yang menarik dan jelas untuk pembaca.')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? '')))
                                ->columnSpan(1),

                            TextInput::make('slug')
                                ->label('Slug URL Website')
                                ->placeholder('panduan-fiqih-penyembelihan-hewan-kurban')
                                ->helperText('Alamat tautan ramah mesin pencari (SEO). Terisi otomatis dari judul.')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpan(1),

                            Select::make('category')
                                ->label('Kategori Materi')
                                ->options([
                                    'Fiqih Sembelih' => 'Fiqih Sembelih',
                                    'Teknis & Alat' => 'Teknis & Alat (Pisau, Bilah & Asah)',
                                    'Edukasi Konsumen' => 'Edukasi Konsumen & Daging ASUH',
                                    'Warta Organisasi' => 'Warta Organisasi DPD Brebes',
                                ])
                                ->helperText('Pilih klasifikasi materi yang tepat.')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('author_name')
                                ->label('Nama Penulis / Redaksi')
                                ->placeholder('Contoh: Humas DPD JULEHA Brebes')
                                ->default('Humas DPD JULEHA Brebes')
                                ->helperText('Nama pengurus atau tim yang menyusun materi ini.')
                                ->maxLength(255)
                                ->columnSpan(1),

                            FileUpload::make('cover')
                                ->label('Foto Sampul / Gambar Banner')
                                ->image()
                                ->imageEditor()
                                ->directory('posts')
                                ->maxSize(2048)
                                ->helperText('Unggah foto utama pendukung berita (JPG, PNG, atau WebP, maks. 2MB).')
                                ->columnSpanFull(),

                            Textarea::make('summary')
                                ->label('Ringkasan / Cuplikan Singkat')
                                ->placeholder('Tuliskan 1-2 kalimat ringkasan artikel...')
                                ->helperText('Cuplikan ini akan muncul di halaman depan website dan pratinjau media sosial.')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('body')
                                ->label('Isi Lengkap Artikel')
                                ->placeholder('Tuliskan isi berita atau panduan lengkap di sini...')
                                ->helperText('Tulis materi lengkap dengan pemisahan paragraf yang rapi.')
                                ->rows(12)
                                ->columnSpanFull()
                                ->required(),
                        ]),
                    ]),

                Section::make('Pengaturan Publikasi')
                    ->description('Kendali status penayangan artikel di website resmi.')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_published')
                                ->label('Publikasikan ke Website Sekarang')
                                ->helperText('Jika dinonaktifkan, artikel berstatus Draf dan belum dapat diakses masyarakat.')
                                ->default(true),

                            DateTimePicker::make('published_at')
                                ->label('Waktu Publikasi')
                                ->helperText('Tentukan tanggal dan waktu artikel mulai tayang.')
                                ->default(now()),
                        ]),
                    ]),
            ]);
    }
}
