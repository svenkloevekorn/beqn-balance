<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    use RedirectsToListPage;
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
