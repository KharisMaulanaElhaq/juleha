<?php

namespace App\Filament\Resources\Certificates;

use App\Filament\Resources\Certificates\Pages\EditCertificate;
use App\Filament\Resources\Certificates\Pages\ListCertificates;
use App\Filament\Resources\Certificates\Schemas\CertificateForm;
use App\Filament\Resources\Certificates\Tables\CertificatesTable;
use App\Models\Certificate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationLabel = 'Sertifikat';

    protected static ?string $modelLabel = 'Sertifikat';

    protected static ?string $pluralModelLabel = 'Sertifikat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        // Hanya super_admin dan admin_kegiatan; editor_konten dan peserta 403
        return $user && $user->isAdminKegiatan();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Certificate::expiringSoon(90)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Sertifikat yang akan segera berakhir dalam 90 hari';
    }

    public static function form(Schema $schema): Schema
    {
        return CertificateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificates::route('/'),
            'edit' => EditCertificate::route('/{record}/edit'),
        ];
    }
}
