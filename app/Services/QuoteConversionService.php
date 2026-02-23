<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\DeliveryNoteStatus;
use App\Enums\QuoteStatus;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Quote;

class QuoteConversionService
{
    public function toInvoice(Quote $quote): Invoice
    {
        $quote->loadMissing('items', 'customer');

        $customer = $quote->customer;
        $dueDays = $customer?->payment_term_days ?? 14;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $quote->customer_id,
            'invoice_date' => now(),
            'due_date' => now()->addDays($dueDays),
            'status' => InvoiceStatus::Draft,
            'apply_discount' => $quote->apply_discount,
            'discount_percent' => $quote->discount_percent,
            'notes' => $quote->notes,
        ]);

        foreach ($quote->items as $item) {
            $invoice->items()->create([
                'article_id' => $item->article_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'net_price' => $item->net_price,
                'vat_rate' => $item->vat_rate,
                'sort_order' => $item->sort_order,
            ]);
        }

        $quote->update(['status' => QuoteStatus::Accepted]);

        return $invoice;
    }

    public function toDeliveryNote(Quote $quote): DeliveryNote
    {
        $quote->loadMissing('items');

        $deliveryNote = DeliveryNote::create([
            'delivery_note_number' => DeliveryNote::generateDeliveryNoteNumber(),
            'customer_id' => $quote->customer_id,
            'delivery_date' => now(),
            'status' => DeliveryNoteStatus::Draft,
            'notes' => $quote->notes,
        ]);

        foreach ($quote->items as $item) {
            $deliveryNote->items()->create([
                'article_id' => $item->article_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'net_price' => $item->net_price,
                'vat_rate' => $item->vat_rate,
                'sort_order' => $item->sort_order,
            ]);
        }

        $quote->update(['status' => QuoteStatus::Accepted]);

        return $deliveryNote;
    }
}
