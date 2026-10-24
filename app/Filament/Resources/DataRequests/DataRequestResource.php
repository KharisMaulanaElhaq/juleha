<?php

namespace App\Filament\Resources\DataRequests;

use App\Filament\Resources\DataRequests\Pages\EditDataRequest;
use App\Filament\Resources\DataRequests\Pages\ListDataRequests;
use App\Filament\Resources\DataRequests\Schemas\DataRequestForm;
use App\Filament\Resources\DataRequests\Tables\DataRequestsTable;
use App\Models\DataRequest;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataRequestResource extends Resource
{
    protected static ?string $model = DataRequest::class;

    protected static ?string $navigationLabel = 'Permintaan Data';

    protected static ?string $modelLabel = 'Permintaan Data';

    protected static ?string $pluralModelLabel = 'Permintaan Hak Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user && $user->isActive() && ($user->isSuperAdmin() || $user->role === User::ROLE_ADMIN_KEGIATAN);
    }

    public static function canCreate(): bool
    {
        return false; // Dibuat lewat form publik atau portal peserta
    }

    public static function form(Schema $schema): Schema
    {
        return DataRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataRequests::route('/'),
            'edit' => EditDataRequest::route('/{record}/edit'),
        ];
    }
}
