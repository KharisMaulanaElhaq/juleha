<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class PurpleUiAvatarsProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        $name = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(function (string $segment): string {
                $letters = preg_replace('/^[^\p{L}\p{N}]+/u', '', $segment);

                return filled($letters) ? mb_substr($letters, 0, 1) : '';
            })
            ->filter()
            ->take(2)
            ->join('');

        if (blank($name)) {
            $name = 'AD';
        }

        // Gunakan latar belakang ungu lembut (#7248b8) dan teks putih agar serasi dengan tema portal
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&format=svg&color=FFFFFF&background=7248B8&bold=true';
    }
}
