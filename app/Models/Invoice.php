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
        'apply_discount',
        'discount_percent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'status' => InvoiceStatus::class,
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
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date');
    }

    public function dunnings(): HasMany
    {
        return $this->hasMany(Dunning::class)->orderBy('dunning_date');
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

    public function paidTotal(): Attribute
    {
        return Attribute::get(function () {
            return $this->payments->sum('amount');
        });
    }

    public function remainingAmount(): Attribute
    {
        return Attribute::get(function () {
            $total = $this->apply_discount
                ? $this->net_total_after_discount + $this->vat_total
                : $this->gross_total;

            return round($total - $this->paid_total, 2);
        });
    }

    public function updatePaymentStatus(): void
    {
        $remaining = $this->remaining_amount;

        if ($remaining <= 0) {
            $this->update(['status' => InvoiceStatus::Paid]);
        } elseif ($this->paid_total > 0) {
            $this->update(['status' => InvoiceStatus::PartiallyPaid]);
        }
    }

    public static function generateInvoiceNumber(): string
    {
        return NumberRange::generateNext('invoice');
    }
}
