<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    protected $fillable = [
        'delivery_note_number',
        'customer_id',
        'delivery_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class)->orderBy('sort_order');
    }

    public function netTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (DeliveryNoteItem $item) => $item->line_total);
        });
    }

    public function vatTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (DeliveryNoteItem $item) => $item->line_vat);
        });
    }

    public function grossTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->sum(fn (DeliveryNoteItem $item) => $item->line_gross);
        });
    }

    public static function generateDeliveryNoteNumber(): string
    {
        return NumberRange::generateNext('delivery_note');
    }
}
