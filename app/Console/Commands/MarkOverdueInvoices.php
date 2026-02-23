<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Markiert versendete Rechnungen als überfällig, wenn das Fälligkeitsdatum überschritten ist';

    public function handle(): int
    {
        $count = Invoice::where('status', InvoiceStatus::Sent)
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => InvoiceStatus::Overdue]);

        $this->info("{$count} Rechnung(en) als überfällig markiert.");

        return self::SUCCESS;
    }
}
