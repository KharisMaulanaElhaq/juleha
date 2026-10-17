<?php

namespace App\Filament\Widgets;

use App\Models\EventSession;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class TodaySessionsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
    ];

    protected string $view = 'filament.widgets.today-sessions-widget';

    public function getTodaySessions()
    {
        return EventSession::query()
            ->whereDate('starts_at', today())
            ->with(['event', 'attendances'])
            ->orderBy('starts_at')
            ->get();
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        // Hanya untuk super_admin dan admin_kegiatan
        return $user && $user->isAdminKegiatan();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Sesi Hari Ini')
            ->description('Jadwal sesi bimtek/pelatihan dan rekap kehadiran hari ini.')
            ->query(
                EventSession::query()
                    ->whereDate('starts_at', today())
                    ->with(['event', 'attendances'])
                    ->orderBy('starts_at')
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Sesi')
                    ->weight('bold')
                    ->description(fn (EventSession $record): string => $record->location ? 'Lokasi: '.$record->location : ''),

                TextColumn::make('event.title')
                    ->label('Kegiatan Bimtek')
                    ->limit(28)
                    ->tooltip(fn ($record) => $record->event?->title),

                TextColumn::make('starts_at')
                    ->label('Jam (WIB)')
                    ->formatStateUsing(function (EventSession $record): string {
                        if (! $record->starts_at) {
                            return '-';
                        }
                        $start = $record->starts_at->timezone('Asia/Jakarta')->format('H:i');
                        $end = $record->ends_at ? $record->ends_at->timezone('Asia/Jakarta')->format('H:i') : null;

                        return $start.($end ? ' – '.$end : '').' WIB';
                    }),

                TextColumn::make('hadir_count')
                    ->label('Hadir')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (EventSession $record): string => $record->hadir_count.' Orang'),
            ])
            ->actions([
                Action::make('screen')
                    ->label('Buka Layar Absen')
                    ->icon('heroicon-m-qr-code')
                    ->color('primary')
                    ->button()
                    ->size('sm')
                    ->url(fn (EventSession $record): string => route('admin.attendance.screen', $record), shouldOpenInNewTab: true),

                Action::make('matrix')
                    ->label('Rekap')
                    ->icon('heroicon-m-chart-bar-square')
                    ->color('gray')
                    ->button()
                    ->size('sm')
                    ->url(fn (EventSession $record): ?string => $record->event ? route('admin.attendance.matrix', $record->event) : null),
            ])
            ->emptyStateHeading('Tidak ada sesi hari ini')
            ->emptyStateDescription('Tidak ada sesi pelatihan atau bimtek yang dijadwalkan berlangsung pada hari ini.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateActions([
                Action::make('next_schedule')
                    ->label('Lihat Jadwal Kegiatan')
                    ->icon('heroicon-m-calendar')
                    ->url(route('filament.admin.resources.events.index'))
                    ->button()
                    ->color('primary'),
            ])
            ->paginated(false);
    }
}
