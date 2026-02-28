<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\IncomingInvoice;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -7;

    protected function getStats(): array
    {
        $fmt = fn ($v) => number_format($v, 2, ',', '.') . ' €';

        // Umsatz aktueller Monat (bezahlt)
        $monthlyRevenue = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->get()
            ->sum(fn (Invoice $inv) => $inv->gross_total);

        // Umsatz aktuelles Jahr (bezahlt)
        $yearlyRevenue = Invoice::where('status', InvoiceStatus::Paid)
            ->whereYear('invoice_date', now()->year)
            ->get()
            ->sum(fn (Invoice $inv) => $inv->gross_total);

        // Offene Rechnungen (sent + partially_paid)
        $openInvoices = Invoice::whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::PartiallyPaid])->get();
        $openInvoiceCount = $openInvoices->count();
        $openInvoiceSum = $openInvoices->sum(fn (Invoice $inv) => $inv->gross_total);

        // Ueberfaellige Rechnungen
        $overdueInvoices = Invoice::where('status', InvoiceStatus::Overdue)->get();
        $overdueCount = $overdueInvoices->count();
        $overdueSum = $overdueInvoices->sum(fn (Invoice $inv) => $inv->gross_total);

        // Offene Eingangsrechnungen
        $openIncomingCount = IncomingInvoice::where('status', 'open')->count();
        $openIncomingSum = IncomingInvoice::where('status', 'open')->sum('gross_amount');

        // Gewinn aktuelles Jahr
        $paidInvoiceSum = $yearlyRevenue;
        $paidIncomingSum = IncomingInvoice::where('status', 'paid')
            ->whereYear('created_at', now()->year)
            ->sum('gross_amount');
        $profit = $paidInvoiceSum - $paidIncomingSum;

        return [
            Stat::make('Umsatz ' . now()->translatedFormat('F'), $fmt($monthlyRevenue))
                ->description('Bezahlte Rechnungen')
                ->color('success'),
            Stat::make('Umsatz ' . now()->year, $fmt($yearlyRevenue))
                ->description('Bezahlte Rechnungen')
                ->color('success'),
            Stat::make('Gewinn ' . now()->year, $fmt($profit))
                ->description('Einnahmen − Ausgaben')
                ->color($profit >= 0 ? 'success' : 'danger'),
            Stat::make('Offene Rechnungen', $openInvoiceCount)
                ->description($fmt($openInvoiceSum))
                ->color('warning'),
            Stat::make('Überfällige Rechnungen', $overdueCount)
                ->description($fmt($overdueSum))
                ->color($overdueCount > 0 ? 'danger' : 'success'),
            Stat::make('Offene Eingangsrechnungen', $openIncomingCount)
                ->description($fmt($openIncomingSum))
                ->color($openIncomingCount > 0 ? 'warning' : 'success'),
        ];
    }
}
