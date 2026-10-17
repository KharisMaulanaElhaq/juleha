<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pendaftaran Peserta')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('participant_name')
                            ->label('Nama Peserta (KTP)')
                            ->content(fn (?Enrollment $record): string => $record?->user?->profile?->full_name_ktp ?? $record?->user?->name ?? '-'),

                        Placeholder::make('participant_email')
                            ->label('Email Peserta')
                            ->content(fn (?Enrollment $record): string => $record?->user?->email ?? '-'),

                        Placeholder::make('participant_phone')
                            ->label('Nomor WhatsApp')
                            ->content(fn (?Enrollment $record): string => $record?->user?->profile?->formatted_phone ?? '-'),

                        Placeholder::make('participant_nik_masked')
                            ->label('NIK (Tersamar)')
                            ->content(fn (?Enrollment $record): string => $record?->user?->profile?->masked_nik ?? '-'),

                        Placeholder::make('event_title')
                            ->label('Kegiatan yang Didaftar')
                            ->content(fn (?Enrollment $record): string => $record?->event?->title ?? '-'),

                        Placeholder::make('event_type')
                            ->label('Jenis Kegiatan')
                            ->content(fn (?Enrollment $record): string => $record?->event?->type?->label() ?? '-'),

                        Select::make('status')
                            ->label('Status Pendaftaran')
                            ->options(collect(EnrollmentStatus::cases())->mapWithKeys(fn (EnrollmentStatus $status) => [$status->value => $status->label()]))
                            ->required(),

                        Textarea::make('admin_notes')
                            ->label('Catatan Panitia / Admin')
                            ->placeholder('Tambahkan catatan atau alasan penolakan/persetujuan...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
