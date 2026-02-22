<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'quote_number',
        'customer_id',
        'quote_date',
        'valid_until',
        'status',
        'apply_discount',
        'discount_percent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'valid_until' => 'date',
            'apply_discount' => 'boolean',
            'discount_percent' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function netTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (QuoteItem $item) => $item->line_total);
        });
    }

    public function vatTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (QuoteItem $item) => $item->line_vat);
        });
    }

    public function grossTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (QuoteItem $item) => $item->line_gross);
        });
    }

    public function discountAmount(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->apply_discount || ! $this->discount_percent) {
                return 0;
            }

            return round($this->net_total * $this->discount_percent / 100, 2);
        });
    }

    public function netTotalAfterDiscount(): Attribute
    {
        return Attribute::get(function () {
            return round($this->net_total - $this->discount_amount, 2);
        });
    }

    public static function generateQuoteNumber(): string
    {
        return NumberRange::generateNext('quote');
    }
}
