<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\CsvSanitizer;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(User::count()),

            'participants' => Tab::make('Peserta')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->participants())
                ->badge(User::participants()->count()),

            'staff' => Tab::make('Pengurus')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->staff())
                ->badge(User::staff()->count()),

            'suspended' => Tab::make('Dinonaktifkan')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', false))
                ->badge(User::where('is_active', false)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pengguna')
                ->icon('heroicon-o-plus'),

            // 1. Ekspor CSV Standar (Default Tanpa NIK, WhatsApp Opsional)
            Action::make('export_csv')
                ->label('Ekspor Pengguna (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->form([
                    Toggle::make('include_whatsapp')
                        ->label('Sertakan Nomor WhatsApp')
                        ->default(false)
                        ->helperText('Nomor WhatsApp peserta akan disertakan dalam file CSV jika diaktifkan.'),
                ])
                ->action(function (array $data): StreamedResponse {
                    $includeWa = ! empty($data['include_whatsapp']);
                    $records = User::with(['profile'])->latest('created_at')->get();
                    $filename = 'daftar-pengguna-juleha-'.date('Y-m-d-His').'.csv';

                    $headers = [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ];

                    $callback = function () use ($records, $includeWa) {
                        $handle = fopen('php://output', 'w');
                        fwrite($handle, "\xEF\xBB\xBF");

                        $columns = [
                            'ID Pengguna',
                            'Nama Lengkap',
                            'Email',
                            'Peran Akun',
                            'Status Akun',
                            'Metode Masuk',
                            'Kecamatan',
                            'Desa/Kelurahan',
                            'Tanggal Bergabung',
                            'Login Terakhir',
                        ];

                        if ($includeWa) {
                            array_splice($columns, 3, 0, ['Nomor WhatsApp']);
                        }

                        fputcsv($handle, CsvSanitizer::sanitizeRow($columns));

                        foreach ($records as $item) {
                            $profile = $item->profile;
                            $row = [
                                $item->id,
                                $item->name,
                                $item->email,
                                match ($item->role) {
                                    User::ROLE_SUPER_ADMIN => 'Super Admin',
                                    User::ROLE_ADMIN_KEGIATAN, 'event_admin' => 'Admin Kegiatan',
                                    User::ROLE_EDITOR_KONTEN, 'content_editor' => 'Editor Konten',
                                    default => 'Peserta',
                                },
                                $item->is_active ? 'Aktif' : 'Dinonaktifkan',
                                filled($item->google_id) ? 'Google' : 'Email/Password',
                                $profile?->district ?? '-',
                                $profile?->village ?? '-',
                                $item->created_at?->format('Y-m-d H:i:s'),
                                $item->last_login_at?->format('Y-m-d H:i:s') ?? 'Belum pernah',
                            ];

                            if ($includeWa) {
                                array_splice($row, 3, 0, [$profile?->phone ? "'".$profile->phone : '-']);
                            }

                            fputcsv($handle, CsvSanitizer::sanitizeRow($row));
                        }

                        fclose($handle);
                    };

                    return response()->streamDownload($callback, $filename, $headers);
                }),

            // 2. Ekspor Khusus Super Admin dengan NIK Lengkap (Tercatat di Audit Log)
            Action::make('export_csv_nik')
                ->label('Ekspor dengan NIK (Super Admin)')
                ->icon('heroicon-o-shield-check')
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() === true)
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Ekspor Data Sensitif (NIK)')
                ->modalDescription('Anda akan mengunduh daftar pengguna beserta NIK lengkap untuk keperluan administrasi dan pelaporan resmi. Aksi ini akan dicatat secara permanen di Log Audit sesuai UU Perlindungan Data Pribadi. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Ekspor dengan NIK')
                ->action(function (): StreamedResponse {
                    $records = User::with(['profile'])->latest('created_at')->get();

                    AuditLog::log('export_nik', 'User', null, [
                        'total_records' => $records->count(),
                        'source' => 'users_resource_export',
                    ]);

                    $filename = 'daftar-pengguna-juleha-nik-'.date('Y-m-d-His').'.csv';
                    $headers = [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ];

                    $callback = function () use ($records) {
                        $handle = fopen('php://output', 'w');
                        fwrite($handle, "\xEF\xBB\xBF");

                        fputcsv($handle, CsvSanitizer::sanitizeRow([
                            'ID Pengguna',
                            'Nama Lengkap',
                            'Nama Sesuai KTP',
                            'NIK Lengkap',
                            'Email',
                            'Nomor WhatsApp',
                            'Peran Akun',
                            'Status Akun',
                            'Kecamatan',
                            'Desa/Kelurahan',
                            'Tanggal Bergabung',
                        ]));

                        foreach ($records as $item) {
                            $profile = $item->profile;
                            fputcsv($handle, CsvSanitizer::sanitizeRow([
                                $item->id,
                                $item->name,
                                $profile?->full_name_ktp ?? $item->name,
                                $profile?->nik ? "'".$profile->nik : '-',
                                $item->email,
                                $profile?->phone ? "'".$profile->phone : '-',
                                match ($item->role) {
                                    User::ROLE_SUPER_ADMIN => 'Super Admin',
                                    User::ROLE_ADMIN_KEGIATAN, 'event_admin' => 'Admin Kegiatan',
                                    User::ROLE_EDITOR_KONTEN, 'content_editor' => 'Editor Konten',
                                    default => 'Peserta',
                                },
                                $item->is_active ? 'Aktif' : 'Dinonaktifkan',
                                $profile?->district ?? '-',
                                $profile?->village ?? '-',
                                $item->created_at?->format('Y-m-d H:i:s'),
                            ]));
                        }

                        fclose($handle);
                    };

                    return response()->streamDownload($callback, $filename, $headers);
                }),
        ];
    }
}
