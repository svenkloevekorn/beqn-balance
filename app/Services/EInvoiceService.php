<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use horstoeko\zugferd\ZugferdProfiles;

class EInvoiceService
{
    /**
     * Mapping von deutschen Einheiten auf UN/ECE-Codes (Recommendation 20).
     * Diese Codes sind fuer die E-Rechnung vorgeschrieben.
     */
    private const UNIT_CODES = [
        'Stück'    => 'H87',  // Piece
        'Stunde'   => 'HUR',  // Hour
        'Pauschal' => 'LS',   // Lump sum
        'kg'       => 'KGM',  // Kilogram
        'm'        => 'MTR',  // Metre
        'm²'       => 'MTK',  // Square metre
        'Liter'    => 'LTR',  // Litre
        'Tag'      => 'DAY',  // Day
        'Monat'    => 'MON',  // Month
    ];

    /**
     * Erstellt ein ZUGFeRD-XML-Dokument aus einer Rechnung.
     */
    public function buildZugferdDocument(Invoice $invoice): ZugferdDocumentBuilder
    {
        $invoice->loadMissing(['customer', 'items']);
        $company = CompanySetting::instance();
        $customer = $invoice->customer;

        $document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_EN16931);

        // --- Rechnungskopf ---
        $document->setDocumentInformation(
            $invoice->invoice_number,
            '380', // 380 = Rechnung
            \DateTime::createFromFormat('Y-m-d', $invoice->invoice_date->format('Y-m-d')),
            'EUR'
        );

        // --- Verkaeufer (Seller) ---
        $sellerName = $company->company_name;
        if ($company->legal_form) {
            $sellerName .= ' ' . $company->legal_form;
        }

        $document->setDocumentSeller($sellerName);
        $document->setDocumentSellerAddress(
            $company->street ?? '',
            '',
            '',
            $company->zip ?? '',
            $company->city ?? '',
            $company->country ?? 'DE'
        );

        if ($company->tax_number) {
            $document->addDocumentSellerTaxRegistration('FC', $company->tax_number);
        }
        if ($company->vat_id) {
            $document->addDocumentSellerTaxRegistration('VA', $company->vat_id);
        }

        if ($company->email || $company->phone) {
            $document->setDocumentSellerContact(
                $company->managing_director ?? '',
                '',
                $company->phone ?? '',
                '',
                $company->email ?? ''
            );
        }

        // --- Kaeufer (Buyer) ---
        $document->setDocumentBuyer($customer->name);
        $document->setDocumentBuyerAddress(
            $customer->street ?? '',
            '',
            '',
            $customer->zip ?? '',
            $customer->city ?? '',
            $customer->country ?? 'DE'
        );

        if ($customer->vat_id) {
            $document->addDocumentBuyerTaxRegistration('VA', $customer->vat_id);
        }

        // Kaeufer-Referenz (Pflichtfeld bei XRechnung, optional bei ZUGFeRD)
        $buyerReference = $invoice->buyer_reference ?? $customer->buyer_reference ?? '';
        if ($buyerReference) {
            $document->setDocumentBuyerReference($buyerReference);
        }

        // --- Lieferdatum (BT-72, Pflicht laut BR-FX-EN-04) ---
        // Rechnungsdatum als Leistungsdatum verwenden
        $document->setDocumentSupplyChainEvent(
            \DateTime::createFromFormat('Y-m-d', $invoice->invoice_date->format('Y-m-d'))
        );

        // --- Zahlungsinformationen ---
        if ($company->iban) {
            // SEPA-Ueberweisung (Code 58) mit IBAN direkt im Aufruf
            $document->addDocumentPaymentMean(
                58,        // TypeCode: SEPA Credit Transfer
                null,      // Information
                null,      // Card Type
                null,      // Card ID
                null,      // Card Holder Name
                null,      // Buyer IBAN
                $company->iban,              // Payee IBAN
                null,                        // Payee Account Name
                null,                        // Payee Proprietary ID
                $company->bic ?? null        // Payee BIC
            );
        } else {
            $document->addDocumentPaymentMean(58);
        }

        // Zahlungsbedingungen
        if ($invoice->due_date) {
            $document->addDocumentPaymentTerm(
                'Zahlbar bis ' . $invoice->due_date->format('d.m.Y'),
                \DateTime::createFromFormat('Y-m-d', $invoice->due_date->format('Y-m-d'))
            );
        }

        // --- Positionen ---
        $positionNumber = 0;
        foreach ($invoice->items as $item) {
            $positionNumber++;
            $unitCode = $this->mapUnitCode($item->unit);

            $document->addNewPosition((string) $positionNumber);
            $document->setDocumentPositionProductDetails($item->description);
            $document->setDocumentPositionGrossPrice((float) $item->net_price);
            $document->setDocumentPositionNetPrice((float) $item->net_price);
            $document->setDocumentPositionQuantity((float) $item->quantity, $unitCode);
            $document->addDocumentPositionTax(
                $this->mapVatCategory((float) $item->vat_rate),
                'VAT',
                (float) $item->vat_rate
            );
            $document->setDocumentPositionLineSummation((float) $item->line_total);
        }

        // --- Rabatt auf Dokumentenebene ---
        $netTotal = (float) $invoice->net_total;
        $discountAmount = (float) $invoice->discount_amount;
        $netAfterDiscount = (float) $invoice->net_total_after_discount;

        if ($invoice->apply_discount && $discountAmount > 0) {
            $document->addDocumentAllowanceCharge(
                $discountAmount,
                false, // false = Abzug (Allowance)
                'S',
                'VAT',
                0, // Steuersatz wird ueber die Positionen abgebildet
                null,
                null,
                95, // Discount (UNTDID 5189 Code)
                'Kundenrabatt ' . number_format((float) $invoice->discount_percent, 2) . ' %'
            );
        }

        // --- Steueraufstellung ---
        $vatGroups = $this->calculateVatGroups($invoice);

        foreach ($vatGroups as $rate => $group) {
            $document->addDocumentTax(
                $this->mapVatCategory((float) $rate),
                'VAT',
                $group['base'],
                $group['tax'],
                (float) $rate
            );
        }

        // --- Summen ---
        $grossTotal = $netAfterDiscount + collect($vatGroups)->sum('tax');

        $document->setDocumentSummation(
            round($grossTotal, 2),                      // Brutto inkl. MwSt
            round($grossTotal, 2),                      // Zahlbetrag
            round($netTotal, 2),                        // Summe Netto-Positionen
            0.00,                                       // Zuschlaege
            round($discountAmount, 2),                  // Abzuege
            round($netAfterDiscount, 2),                // Steuerbasis
            round(collect($vatGroups)->sum('tax'), 2),  // Steuer gesamt
            null,
            0.00                                        // Bereits gezahlt
        );

        return $document;
    }

    /**
     * Erzeugt das ZUGFeRD-XML als String.
     */
    public function generateXml(Invoice $invoice): string
    {
        $document = $this->buildZugferdDocument($invoice);

        return $document->getContent();
    }

    /**
     * Bettet das ZUGFeRD-XML in ein bestehendes PDF ein und konvertiert zu PDF/A-3.
     * Nutzt ZugferdDocumentPdfBuilder fuer korrekte PDF/A-3 Konformitaet
     * inkl. XMP-Metadaten (DocumentType, Version, ConformanceLevel).
     */
    public function embedXmlInPdf(Invoice $invoice, string $pdfContent): string
    {
        $document = $this->buildZugferdDocument($invoice);

        $pdfBuilder = ZugferdDocumentPdfBuilder::fromPdfString($document, $pdfContent);
        $pdfBuilder->generateDocument();

        return $pdfBuilder->downloadString();
    }

    /**
     * Berechnet MwSt-Gruppen mit Beruecksichtigung von Rabatten.
     */
    private function calculateVatGroups(Invoice $invoice): array
    {
        $groups = [];

        foreach ($invoice->items as $item) {
            $rate = number_format((float) $item->vat_rate, 2, '.', '');
            if (! isset($groups[$rate])) {
                $groups[$rate] = ['base' => 0, 'tax' => 0];
            }
            $groups[$rate]['base'] += (float) $item->line_total;
        }

        // Rabatt anteilig verteilen
        if ($invoice->apply_discount && $invoice->discount_amount > 0) {
            $netTotal = (float) $invoice->net_total;
            $factor = $netTotal > 0 ? ($netTotal - (float) $invoice->discount_amount) / $netTotal : 1;

            foreach ($groups as $rate => &$group) {
                $group['base'] = round($group['base'] * $factor, 2);
                $group['tax'] = round($group['base'] * (float) $rate / 100, 2);
            }
        } else {
            foreach ($groups as $rate => &$group) {
                $group['tax'] = round($group['base'] * (float) $rate / 100, 2);
            }
        }

        return $groups;
    }

    /**
     * Mappt deutsche Einheiten auf UN/ECE-Codes.
     */
    private function mapUnitCode(string $unit): string
    {
        return self::UNIT_CODES[$unit] ?? 'C62'; // C62 = "one" (Fallback)
    }

    /**
     * Mappt MwSt-Satz auf die EN 16931 Steuerkategorie.
     * S = Standard, Z = Zero-rated, E = Exempt
     */
    private function mapVatCategory(float $vatRate): string
    {
        if ($vatRate > 0) {
            return 'S'; // Standard rate
        }

        return 'E'; // Exempt (steuerbefreit)
    }
}
