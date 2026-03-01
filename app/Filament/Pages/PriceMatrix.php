<?php

namespace App\Filament\Pages;

use App\Models\Article;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerArticlePrice;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PriceMatrix extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|\UnitEnum|null $navigationGroup = 'Artikel';

    protected static ?string $navigationLabel = 'Preismatrix';

    protected static ?string $title = 'Preismatrix';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.price-matrix';

    public array $groupedArticles = [];

    public array $customers = [];

    public array $prices = [];

    public int $startIndex = 0;

    public int $visibleCount = 4;

    public int $scrollStep = 4;

    public const VISIBLE_OPTIONS = [4, 6, 8, 12, 16];

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $articles = Article::where('is_active', true)
            ->with('categories')
            ->orderBy('name')
            ->get(['id', 'name', 'net_price']);

        $this->customers = Customer::where('pricing_mode', 'custom_prices')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        // Artikel nach Kategorien gruppieren
        $categories = Category::orderBy('name')->get();
        $assignedArticleIds = [];
        $groups = [];

        foreach ($categories as $category) {
            $categoryArticles = $articles->filter(
                fn ($a) => $a->categories->contains($category->id)
            );

            if ($categoryArticles->isNotEmpty()) {
                $groups[] = [
                    'name' => $category->name,
                    'color' => $category->color ?? '#6b7280',
                    'articles' => $categoryArticles->map(fn ($a) => [
                        'id' => $a->id,
                        'name' => $a->name,
                        'net_price' => $a->net_price,
                    ])->values()->toArray(),
                ];

                foreach ($categoryArticles as $a) {
                    $assignedArticleIds[$a->id] = true;
                }
            }
        }

        // Artikel ohne Kategorie als "Sonstige"
        $uncategorized = $articles->filter(
            fn ($a) => ! isset($assignedArticleIds[$a->id])
        );

        if ($uncategorized->isNotEmpty()) {
            $groups[] = [
                'name' => 'Sonstige',
                'color' => '#6b7280',
                'articles' => $uncategorized->map(fn ($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'net_price' => $a->net_price,
                ])->values()->toArray(),
            ];
        }

        $this->groupedArticles = $groups;

        // Preise laden
        $customerIds = array_column($this->customers, 'id');
        $articleIds = $articles->pluck('id')->toArray();

        $existingPrices = CustomerArticlePrice::whereIn('customer_id', $customerIds)
            ->whereIn('article_id', $articleIds)
            ->get();

        $this->prices = [];
        foreach ($existingPrices as $cap) {
            if ($cap->is_active && $cap->custom_net_price !== null) {
                $this->prices[$cap->article_id . '_' . $cap->customer_id] = number_format((float) $cap->custom_net_price, 2, '.', '');
            }
        }
    }

    public function setVisibleCount(int $count): void
    {
        if (! in_array($count, self::VISIBLE_OPTIONS)) {
            return;
        }

        $this->visibleCount = $count;
        $maxStart = max(0, count($this->customers) - $this->visibleCount);
        $this->startIndex = min($this->startIndex, $maxStart);
    }

    public function previousCustomers(): void
    {
        if ($this->startIndex > 0) {
            $this->startIndex = max(0, $this->startIndex - $this->scrollStep);
        }
    }

    public function nextCustomers(): void
    {
        $maxStart = max(0, count($this->customers) - $this->visibleCount);
        if ($this->startIndex < $maxStart) {
            $this->startIndex = min($maxStart, $this->startIndex + $this->scrollStep);
        }
    }

    public function updatePrice(int $articleId, int $customerId, ?string $value): void
    {
        $value = $value !== null ? trim($value) : '';
        $value = str_replace(',', '.', $value);

        $key = $articleId . '_' . $customerId;

        if ($value === '') {
            CustomerArticlePrice::updateOrCreate(
                ['customer_id' => $customerId, 'article_id' => $articleId],
                ['is_active' => false, 'custom_net_price' => null]
            );
            unset($this->prices[$key]);
        } else {
            $numericValue = round((float) $value, 2);
            CustomerArticlePrice::updateOrCreate(
                ['customer_id' => $customerId, 'article_id' => $articleId],
                ['is_active' => true, 'custom_net_price' => $numericValue]
            );
            $this->prices[$key] = number_format($numericValue, 2, '.', '');
        }

        Notification::make()
            ->success()
            ->title('Preis gespeichert')
            ->duration(1500)
            ->send();
    }
}
