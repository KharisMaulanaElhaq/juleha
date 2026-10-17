<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\EnrollmentStatus;
use App\Models\Attendance;
use App\Models\EventSession;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Sesi & Presensi Kegiatan';

    protected static ?string $modelLabel = 'Sesi Pelatihan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Nama Sesi Pelatihan')
                    ->placeholder('Contoh: Sesi 1 – Fiqih & Anatomi 3 Saluran Sembelih')
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
                    ->placeholder('Contoh: Aula Islamic Centre / RPH Brebes')
                    ->nullable()
                    ->columnSpanFull(),

                Toggle::make('attendance_open')
                    ->label('Buka Presensi Sekarang (Manual Override)')
                    ->helperText('Jika diaktifkan, peserta dapat scan presensi meskipun waktu belum memasuki jam mulai.')
                    ->default(false)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Nama Sesi')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('formatted_schedule')
                    ->label('Jadwal Sesi (WIB)'),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('–')
                    ->visibleFrom('md'),

                TextColumn::make('attendances_count')
                    ->counts('attendances')
                    ->label('Hadir')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                ToggleColumn::make('attendance_open')
                    ->label('Presensi Dibuka')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Sesi Baru')
                    ->modalHeading('Tambah Sesi Pelatihan'),

                Action::make('matrix_report')
                    ->label('Rekap & Ekspor Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->url(fn ($livewire): string => route('admin.attendance.matrix', $livewire->ownerRecord->id), shouldOpenInNewTab: true),
            ])
            ->actions([
                Action::make('open_screen')
                    ->label('Layar QR Presensi')
                    ->tooltip('Buka Layar Presensi (Proyektor)')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('primary')
                    ->button()
                    ->size('xs')
                    ->url(fn (EventSession $record): string => route('admin.attendance.screen', $record->id), shouldOpenInNewTab: true),

                ActionGroup::make([

                    Action::make('manual_attendance')
                        ->label('Presensi Manual')
                        ->icon('heroicon-o-user-plus')
                        ->color('warning')
                        ->modalHeading(fn (EventSession $record): string => "Presensi Manual: {$record->title}")
                        ->modalDescription('Tandai kehadiran peserta secara manual (hadir, izin, sakit, atau alpa).')
                        ->schema([
                            Select::make('user_id')
                                ->label('Pilih Peserta')
                                ->options(function (EventSession $record): array {
                                    return $record->event->enrollments()
                                        ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
                                        ->with('user.profile')
                                        ->get()
                                        ->mapWithKeys(function ($en) {
                                            $u = $en->user;
                                            $p = $u?->profile;
                                            $label = ($p?->full_name_ktp ?: ($u?->name ?: '-')).' ('.($p?->phone ?: $u?->email).')';

                                            return [$u->id => $label];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->required(),

                            Select::make('status')
                                ->label('Status Kehadiran')
                                ->options([
                                    'hadir' => 'Hadir',
                                    'izin' => 'Izin',
                                    'sakit' => 'Sakit',
                                    'alpa' => 'Alpa / Tidak Hadir',
                                ])
                                ->default('hadir')
                                ->required(),
                        ])
                        ->action(function (EventSession $record, array $data): void {
                            Attendance::updateOrCreate(
                                [
                                    'event_session_id' => $record->id,
                                    'user_id' => $data['user_id'],
                                ],
                                [
                                    'status' => $data['status'],
                                    'source' => 'manual',
                                    'recorded_by' => auth()->id(),
                                    'attended_at' => now(),
                                ]
                            );

                            Notification::make()
                                ->title('Presensi manual peserta berhasil disimpan')
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
