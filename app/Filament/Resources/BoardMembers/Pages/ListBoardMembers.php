<?php

namespace App\Filament\Resources\BoardMembers\Pages;

use App\Filament\Resources\BoardMembers\BoardMemberResource;
use App\Models\BoardMember;
use App\Services\BoardMemberPhotoService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBoardMembers extends ListRecords
{
    protected static string $resource = BoardMemberResource::class;

    public function getSubheading(): ?string
    {
        $total = BoardMember::where('kind', BoardMember::KIND_PERSON)->where('is_active', true)->count();
        $withPhoto = BoardMember::where('kind', BoardMember::KIND_PERSON)->where('is_active', true)->whereNotNull('photo')->count();

        return "{$withPhoto} dari {$total} pengurus sudah berfoto. Foto otomatis dipotong rasio 4:5 format WebP.";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_import_photos')
                ->label('Impor Foto via ZIP')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Impor Banyak Foto Pengurus (Berkas ZIP)')
                ->modalDescription('Unggah berkas ZIP yang berisi foto-foto pengurus (misalnya hasil unduh folder Google Drive ke komputer Anda). Berkas diekstrak lokal di server tanpa permintaan ke internet.')
                ->modalSubmitActionLabel('Mulai Impor Foto')
                ->form([
                    FileUpload::make('zip_file')
                        ->label('Berkas ZIP Foto')
                        ->helperText('Nama file di dalam ZIP sebaiknya mengandung nama pengurus (misal: "Chasan Mudhofar.jpg"). Sistem akan mencocokkan secara otomatis.')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'])
                        ->maxSize(51200) // 50 MB
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(BoardMemberPhotoService::class);

                    try {
                        $filePath = storage_path('app/' . $data['zip_file']);
                        if (! file_exists($filePath)) {
                            throw new \RuntimeException('Berkas ZIP tidak ditemukan di server.');
                        }

                        $result = $service->importPhotosFromZip($filePath);
                        @unlink($filePath);

                        $matchedCount = count($result['matched']);
                        $unmatchedCount = count($result['unmatched']);
                        $errorCount = count($result['errors']);

                        $body = "Berhasil memasang foto untuk {$matchedCount} pengurus.";
                        if ($unmatchedCount > 0) {
                            $body .= " ({$unmatchedCount} berkas foto tidak cocok dengan nama pengurus aktif).";
                        }
                        if ($errorCount > 0) {
                            $body .= " ({$errorCount} gagal diproses: " . implode(', ', array_slice($result['errors'], 0, 3)) . ').';
                        }

                        Notification::make()
                            ->title('Proses Impor Foto Selesai')
                            ->body($body)
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Melakukan Impor Foto')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('view_public')
                ->label('Lihat Halaman Publik')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('pengurus'))
                ->openUrlInNewTab(),

            CreateAction::make()
                ->label('Tambah Pengurus Baru'),
        ];
    }
}
