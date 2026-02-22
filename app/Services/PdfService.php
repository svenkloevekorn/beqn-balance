<?php

namespace App\Services;

use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfService
{
    public function generateInvoice(Model $invoice, bool $isDraft = false): string
    {
        $invoice->load(['customer', 'items']);
        $company = CompanySetting::instance();

        $pdf = Pdf::loadView('pdf.invoice', [
            'document' => $invoice,
            'company' => $company,
            'isDraft' => $isDraft,
            'type' => 'invoice',
            'title' => 'Rechnung',
            'numberLabel' => 'Rechnungsnummer',
            'numberValue' => $invoice->invoice_number,
            'dateLabel' => 'Rechnungsdatum',
            'dateValue' => $invoice->invoice_date,
            'extraFields' => [
                'Faelligkeitsdatum' => $invoice->due_date?->format('d.m.Y'),
            ],
        ]);

        $pdf->setPaper('a4');

        $content = $pdf->output();

        return $this->applyLetterhead($content, $company);
    }

    public function generateQuote(Model $quote, bool $isDraft = false): string
    {
        $quote->load(['customer', 'items']);
        $company = CompanySetting::instance();

        $pdf = Pdf::loadView('pdf.invoice', [
            'document' => $quote,
            'company' => $company,
            'isDraft' => $isDraft,
            'type' => 'quote',
            'title' => 'Angebot',
            'numberLabel' => 'Angebotsnummer',
            'numberValue' => $quote->quote_number,
            'dateLabel' => 'Angebotsdatum',
            'dateValue' => $quote->quote_date,
            'extraFields' => [
                'Gueltig bis' => $quote->valid_until?->format('d.m.Y'),
            ],
            'discount' => $quote->apply_discount && $quote->discount_percent > 0 ? [
                'percent' => $quote->discount_percent,
                'amount' => $quote->discount_amount,
                'netAfter' => $quote->net_total_after_discount,
            ] : null,
        ]);

        $pdf->setPaper('a4');

        $content = $pdf->output();

        return $this->applyLetterhead($content, $company);
    }

    public function generateDeliveryNote(Model $deliveryNote, bool $isDraft = false): string
    {
        $deliveryNote->load(['customer', 'items']);
        $company = CompanySetting::instance();

        $pdf = Pdf::loadView('pdf.invoice', [
            'document' => $deliveryNote,
            'company' => $company,
            'isDraft' => $isDraft,
            'type' => 'delivery_note',
            'title' => 'Lieferschein',
            'numberLabel' => 'Lieferscheinnummer',
            'numberValue' => $deliveryNote->delivery_note_number,
            'dateLabel' => 'Lieferdatum',
            'dateValue' => $deliveryNote->delivery_date,
            'extraFields' => [],
        ]);

        $pdf->setPaper('a4');

        $content = $pdf->output();

        return $this->applyLetterhead($content, $company);
    }

    protected function applyLetterhead(string $pdfContent, CompanySetting $company): string
    {
        if (! $company->use_letterhead || ! $company->letterhead_path) {
            return $pdfContent;
        }

        $letterheadPath = Storage::path($company->letterhead_path);

        if (! file_exists($letterheadPath)) {
            return $pdfContent;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_content_');
        file_put_contents($tempFile, $pdfContent);

        try {
            $fpdi = new Fpdi('P', 'mm', 'A4');
            $fpdi->setPrintHeader(false);
            $fpdi->setPrintFooter(false);

            $letterheadPageCount = $fpdi->setSourceFile($letterheadPath);
            $contentPageCount = $fpdi->setSourceFile($tempFile);

            for ($page = 1; $page <= $contentPageCount; $page++) {
                $fpdi->AddPage('P', 'A4');

                // Briefpapier als Hintergrund
                if ($page <= $letterheadPageCount) {
                    $fpdi->setSourceFile($letterheadPath);
                    $tplId = $fpdi->importPage($page);
                    $fpdi->useTemplate($tplId, 0, 0, 210, 297);
                }

                // Inhalt darueber legen
                $fpdi->setSourceFile($tempFile);
                $tplId = $fpdi->importPage($page);
                $fpdi->useTemplate($tplId, 0, 0, 210, 297);
            }

            $result = $fpdi->Output('', 'S');
        } finally {
            @unlink($tempFile);
        }

        return $result;
    }
}
