<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use RedirectsToListPage;
    protected static string $resource = ArticleResource::class;
}
