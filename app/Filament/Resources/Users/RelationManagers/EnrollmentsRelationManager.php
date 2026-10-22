<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Models\Attendance;
use App\Models\Enrollment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Kegiatan yang Diikuti';

    protected static ?string $modelLabel = 'Pendaftaran Kegiatan';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->isParticipant();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('event.title')
                    ->label('Nama Kegiatan')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('event.type')
                    ->label('Jenis Kegiatan')
                    ->badge()
                    ->formatStateUsing(fn (?EventType $state): string => $state?->label() ?? '-')
                    ->color(fn (?EventType $state): string => match ($state) {
                        EventType::Bimtek => 'success',
                        EventType::SertifikasiKompetensi => 'info',
                        EventType::Tot => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status Pendaftaran')
                    ->badge()
                    ->formatStateUsing(fn (EnrollmentStatus $state): string => $state->label())
                    ->color(fn (EnrollmentStatus $state): string => $state->filamentColor()),

                TextColumn::make('attendance_summary')
                    ->label('Kehadiran Sesi')
                    ->badge()
                    ->state(function (Enrollment $record): string {
                        $event = $record->event;
                        if (! $event) {
                            return '–';
                        }

                        $sessionIds = $event->sessions()->pluck('id');
                        $totalSessions = $sessionIds->count();
                        if ($totalSessions === 0) {
                            return '0 sesi';
                        }

                        $hadirCount = Attendance::where('user_id', $record->user_id)
                            ->whereIn('event_session_id', $sessionIds)
                            ->where('status', AttendanceStatus::Hadir)
                            ->count();

                        $pct = round(($hadirCount / $totalSessions) * 100);

                        return "{$hadirCount} / {$totalSessions} sesi ({$pct}%)";
                    })
                    ->color(function (string $state): string {
                        if (str_contains($state, '100%')) {
                            return 'success';
                        }
                        if (str_contains($state, '0%')) {
                            return 'danger';
                        }

                        return 'warning';
                    }),

                TextColumn::make('created_at')
                    ->label('Waktu Mendaftar')
                    ->dateTime('d M Y H:i WIB')
                    ->sortable(),
            ])
            ->emptyStateHeading('Belum Ada Kegiatan yang Diikuti')
            ->emptyStateDescription('Peserta ini belum terdaftar pada kegiatan bimtek atau sertifikasi apapun.');
    }
}
