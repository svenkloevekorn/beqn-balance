<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'article_id',
        'description',
        'quantity',
        'unit',
        'net_price',
        'vat_rate',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'net_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function lineTotal(): Attribute
    {
        return Attribute::get(fn () => round($this->quantity * $this->net_price, 2));
    }

    public function lineVat(): Attribute
    {
        return Attribute::get(fn () => round($this->line_total * $this->vat_rate / 100, 2));
    }

    public function lineGross(): Attribute
    {
        return Attribute::get(fn () => round($this->line_total + $this->line_vat, 2));
    }
}
