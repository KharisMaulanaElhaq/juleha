<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminGreetingWidget extends Widget
{
    protected static ?int $sort = 1;

    protected string $view = 'filament.widgets.admin-greeting-widget';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
    ];
}
