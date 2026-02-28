<?php

namespace App\Filament\Resources\Dunnings\Pages;

use App\Filament\Resources\Dunnings\DunningResource;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\CreateRecord;

class CreateDunning extends CreateRecord
{
    use RedirectsToListPage;
    protected static string $resource = DunningResource::class;
}
