<?php

namespace App\Filament\Resources\EventSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Kegiatan Bimtek')
                    ->relationship('event', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Nama / Judul Sesi')
                    ->placeholder('Contoh: Sesi 1 – Fiqih Penyembelihan & Anatomi Leher Hewan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                DateTimePicker::make('starts_at')
                    ->label('Waktu Mulai')
                    ->required(),

                DateTimePicker::make('ends_at')
                    ->label('Waktu Selesai (Estimasi)')
                    ->nullable(),

                TextInput::make('location')
                    ->label('Lokasi Sesi')
                    ->placeholder('Contoh: Gedung Islamic Centre Brebes')
                    ->nullable()
                    ->columnSpanFull(),

                Toggle::make('attendance_open')
                    ->label('Buka Presensi Sekarang')
                    ->helperText('Jika aktif, peserta dapat melakukan absensi secara mandiri dari portal peserta.')
                    ->default(false)
                    ->columnSpanFull(),
            ]);
    }
}
