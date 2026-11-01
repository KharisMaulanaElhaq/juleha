<?php

namespace App\Filament\Resources\BoardMembers\Pages;

use App\Filament\Resources\BoardMembers\BoardMemberResource;
use App\Models\BoardMember;
use App\Services\BoardMemberPhotoService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
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
                ->label('Unggah Banyak Foto / Google Drive')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Impor Banyak Foto Pengurus')
                ->modalDescription('Anda dapat mengimpor banyak foto sekaligus via berkas ZIP (hasil download folder Google Drive) atau menempel daftar tautan file Google Drive.')
                ->modalSubmitActionLabel('Mulai Impor Foto')
                ->form([
                    Radio::make('import_type')
                        ->label('Metode Impor')
                        ->options([
                            'zip' => 'Berkas ZIP (Klik kanan folder foto di Google Drive -> Download, lalu unggah file ZIP tersebut di sini)',
                            'drive_links' => 'Daftar Tautan Google Drive (Format per baris: Nama Pengurus = Link File)',
                        ])
                        ->default('zip')
                        ->live()
                        ->required(),

                    FileUpload::make('zip_file')
                        ->label('Berkas ZIP Foto')
                        ->helperText('Nama file di dalam ZIP sebaiknya mengandung nama pengurus (misal: "Chasan Mudhofar.jpg"). Sistem akan mencocokkan secara otomatis.')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'])
                        ->maxSize(51200) // 50 MB
                        ->visible(fn ($get) => $get('import_type') === 'zip')
                        ->required(fn ($get) => $get('import_type') === 'zip'),

                    Textarea::make('drive_links')
                        ->label('Daftar Tautan File Google Drive')
                        ->placeholder("Chasan Mudhofar = https://drive.google.com/file/d/1abc.../view\nAhmad Fauzi = https://drive.google.com/file/d/2xyz.../view")
                        ->helperText('Tulis satu pengurus per baris dengan format: Nama = Link Google Drive. Pastikan izin berbagi disetel "Siapa saja yang memiliki link".')
                        ->rows(8)
                        ->visible(fn ($get) => $get('import_type') === 'drive_links')
                        ->required(fn ($get) => $get('import_type') === 'drive_links'),
                ])
                ->action(function (array $data) {
                    $service = app(BoardMemberPhotoService::class);

                    try {
                        if ($data['import_type'] === 'zip') {
                            $filePath = storage_path('app/' . $data['zip_file']);
                            if (! file_exists($filePath)) {
                                throw new \RuntimeException('Berkas ZIP tidak ditemukan di server.');
                            }

                            $result = $service->importPhotosFromZip($filePath);
                            @unlink($filePath);
                        } else {
                            $result = $service->importPhotosFromBatchDriveLinks($data['drive_links']);
                        }

                        $matchedCount = count($result['matched']);
                        $unmatchedCount = count($result['unmatched']);
                        $errorCount = count($result['errors']);

                        $body = "Berhasil memasang foto untuk {$matchedCount} pengurus.";
                        if ($unmatchedCount > 0) {
                            $body .= " ({$unmatchedCount} berkas/tautan tidak cocok dengan nama pengurus).";
                        }
                        if ($errorCount > 0) {
                            $body .= " ({$errorCount} gagal diproses).";
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
