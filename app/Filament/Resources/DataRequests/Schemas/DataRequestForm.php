<?php

namespace App\Filament\Resources\DataRequests\Schemas;

use App\Models\DataRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DataRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Permohonan Hak Data')
                    ->description('Data pemohon dan kategori hak subjek data yang diajukan.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('request_number')
                                ->label('Nomor Registrasi')
                                ->disabled()
                                ->columnSpan(1),

                            Select::make('request_type')
                                ->label('Jenis Hak Data')
                                ->options(DataRequest::typeLabels())
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('name')
                                ->label('Nama Pemohon')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('contact')
                                ->label('Kontak Pemohon')
                                ->required()
                                ->columnSpan(1),

                            DateTimePicker::make('due_at')
                                ->label('Batas Waktu Tindak Lanjut (SLA)')
                                ->required()
                                ->columnSpan(1),

                            Select::make('status')
                                ->label('Status Permohonan')
                                ->options(DataRequest::statusLabels())
                                ->required()
                                ->columnSpan(1),

                            Textarea::make('description')
                                ->label('Uraian Permohonan')
                                ->rows(4)
                                ->disabled()
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Tindak Lanjut & Jawaban')
                    ->description('Catat hasil penanganan dan konfirmasi jawaban kepada pemohon.')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Textarea::make('resolution_notes')
                            ->label('Catatan Jawaban / Tindakan Petugas')
                            ->placeholder('Tuliskan penjelasan tindak lanjut yang diberikan kepada pemohon...')
                            ->rows(4)
                            ->columnSpanFull(),

                        DateTimePicker::make('resolved_at')
                            ->label('Waktu Selesai Ditangani')
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
