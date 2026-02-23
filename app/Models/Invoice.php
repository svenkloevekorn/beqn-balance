<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'status' => InvoiceStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function netTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (InvoiceItem $item) => $item->line_total);
        });
    }

    public function vatTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (InvoiceItem $item) => $item->line_vat);
        });
    }

    public function grossTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (InvoiceItem $item) => $item->line_gross);
        });
    }

    public static function generateInvoiceNumber(): string
    {
        return NumberRange::generateNext('invoice');
    }
}
