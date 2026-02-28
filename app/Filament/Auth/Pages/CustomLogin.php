<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    protected string $view = 'filament.auth.pages.custom-login';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
