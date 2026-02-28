<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    use RedirectsToListPage;
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $customerId = $data['id'];

        Article::where('is_active', true)->each(function (Article $article) use ($customerId) {
            \App\Models\CustomerArticlePrice::firstOrCreate(
                [
                    'customer_id' => $customerId,
                    'article_id' => $article->id,
                ],
                [
                    'is_active' => false,
                    'custom_net_price' => null,
                ]
            );
        });

        return $data;
    }
}
