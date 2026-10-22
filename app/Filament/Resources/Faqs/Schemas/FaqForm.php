<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tanya Jawab (FAQ)')
                    ->description('Pertanyaan yang sering diajukan oleh masyarakat atau calon peserta bimtek.')
                    ->icon('heroicon-o-question-mark-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('category')
                                ->label('Kategori Tanya Jawab')
                                ->options([
                                    'Umum & Organisasi' => 'Umum & Organisasi JULEHA',
                                    'Bimbingan Teknis' => 'Bimbingan Teknis & Pelatihan',
                                    'Fiqih Sembelih' => 'Fiqih Sembelih & Syariat',
                                    'Kurban & Jasa' => 'Kurban & Jasa Penyembelihan',
                                    'Sertifikasi BNSP' => 'Sertifikasi Kompetensi BNSP',
                                ])
                                ->helperText('Kelompokkan pertanyaan ke dalam topik yang relevan.')
                                ->default('Umum & Organisasi')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('sort_order')
                                ->label('Nomor Urut Tampil')
                                ->numeric()
                                ->default(0)
                                ->helperText('Urutan tampilan di halaman Tanya Jawab (0, 1, 2, ...).')
                                ->columnSpan(1),

                            TextInput::make('question')
                                ->label('Pertanyaan yang Sering Diajukan')
                                ->placeholder('Contoh: Apa saja syarat mengikuti Bimtek Juru Sembelih Halal Brebes?')
                                ->helperText('Tuliskan kalimat tanya yang singkat dan jelas.')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Textarea::make('answer')
                                ->label('Jawaban Penjelasan')
                                ->placeholder('Tuliskan jawaban yang ramah, informatif, dan mudah dipahami...')
                                ->helperText('Berikan penjelasan yang menjawab pertanyaan secara tuntas.')
                                ->rows(6)
                                ->columnSpanFull()
                                ->required(),

                            Toggle::make('is_active')
                                ->label('Aktif Ditampilkan di Halaman FAQ Website')
                                ->helperText('Aktifkan agar pertanyaan dan jawaban ini dapat dibaca oleh publik.')
                                ->default(true)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
