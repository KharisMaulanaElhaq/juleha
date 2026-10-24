<?php

namespace App\Filament\Resources\LegalDocuments;

use App\Filament\Resources\LegalDocuments\Pages\CreateLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\EditLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\ListLegalDocuments;
use App\Filament\Resources\LegalDocuments\Schemas\LegalDocumentForm;
use App\Filament\Resources\LegalDocuments\Tables\LegalDocumentsTable;
use App\Models\LegalDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = LegalDocument::class;

    protected static ?string $navigationLabel = 'Dokumen Hukum';

    protected static ?string $modelLabel = 'Dokumen Hukum';

    protected static ?string $pluralModelLabel = 'Dokumen Hukum';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    /**
     * Hanya Super Admin yang berhak melihat menu dan mengelola dokumen hukum.
     * Admin lain (admin kegiatan, editor konten) tidak dapat melihat menu ini.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user && $user->isActive() && $user->isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return $user && $user->isActive() && $user->isSuperAdmin();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        return $user && $user->isActive() && $user->isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return $user && $user->isActive() && $user->isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return LegalDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegalDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalDocuments::route('/'),
            'create' => CreateLegalDocument::route('/create'),
            'edit' => EditLegalDocument::route('/{record}/edit'),
        ];
    }
}
