<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function getHeading(): string | Htmlable
    {
        return '';
    }
}
