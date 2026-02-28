<?php

namespace App\Filament\Concerns;

trait RedirectsToListPage
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
