<?php

namespace App\Filament\Widgets;

use App\Models\IncomingInvoice;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BuhaStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $openInvoices = Invoice::where('status', 'sent')->get();
        $openInvoiceCount = $openInvoices->count();
        $openInvoiceSum = $openInvoices->sum(fn (Invoice $inv) => $inv->gross_total);

        $openIncomingCount = IncomingInvoice::where('status', 'open')->count();
        $openIncomingSum = IncomingInvoice::where('status', 'open')->sum('gross_amount');

        $paidInvoiceSum = Invoice::where('status', 'paid')
            ->get()
            ->sum(fn (Invoice $inv) => $inv->gross_total);

        $paidIncomingSum = IncomingInvoice::where('status', 'paid')->sum('gross_amount');

        $profit = $paidInvoiceSum - $paidIncomingSum;

        return [
            Stat::make('Offene Rechnungen', $openInvoiceCount)
                ->description(number_format($openInvoiceSum, 2, ',', '.') . ' €')
                ->color('warning'),
            Stat::make('Offene Eingangsrechnungen', $openIncomingCount)
                ->description(number_format($openIncomingSum, 2, ',', '.') . ' €')
                ->color('warning'),
            Stat::make('Einnahmen (bezahlt)', number_format($paidInvoiceSum, 2, ',', '.') . ' €')
                ->color('success'),
            Stat::make('Ausgaben (bezahlt)', number_format($paidIncomingSum, 2, ',', '.') . ' €')
                ->color('danger'),
            Stat::make('Gewinn', number_format($profit, 2, ',', '.') . ' €')
                ->color($profit >= 0 ? 'success' : 'danger'),
        ];
    }
}
