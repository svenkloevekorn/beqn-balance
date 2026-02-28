<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .page {
            padding: 20mm 20mm 25mm 20mm;
            position: relative;
        }

        /* Wasserzeichen */
        .watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.3);
            z-index: -1;
            white-space: nowrap;
        }

        /* Absenderzeile */
        .sender-line {
            font-size: 7px;
            color: #888;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin-bottom: 8mm;
        }

        /* Header mit Empfaenger und Firmendaten */
        .header {
            width: 100%;
            margin-bottom: 12mm;
        }

        .header td {
            vertical-align: top;
        }

        .recipient {
            width: 55%;
            font-size: 10px;
            line-height: 1.5;
        }

        .company-info {
            width: 45%;
            text-align: right;
            font-size: 9px;
            color: #555;
            line-height: 1.6;
        }

        .company-logo {
            max-width: 150px;
            max-height: 50px;
            margin-bottom: 5px;
        }

        .company-name {
            font-weight: bold;
            font-size: 10px;
            color: #333;
        }

        /* Dokumenttitel */
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5mm;
            color: #222;
        }

        /* Dokumentdaten */
        .document-meta {
            margin-bottom: 10mm;
        }

        .document-meta td {
            padding: 2px 0;
            font-size: 10px;
        }

        .document-meta .label {
            color: #666;
            padding-right: 15px;
            white-space: nowrap;
        }

        .document-meta .value {
            font-weight: bold;
        }

        /* Positionen-Tabelle */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8mm;
        }

        .items-table thead th {
            background: #f5f5f5;
            border-bottom: 2px solid #333;
            padding: 6px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            text-align: left;
        }

        .items-table thead th.right {
            text-align: right;
        }

        .items-table tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
        }

        .items-table tbody td.right {
            text-align: right;
        }

        .items-table tbody td.center {
            text-align: center;
        }

        /* Summen */
        .totals-wrapper {
            width: 100%;
        }

        .totals-table {
            margin-left: auto;
            border-collapse: collapse;
            min-width: 250px;
        }

        .totals-table td {
            padding: 3px 10px;
            font-size: 10px;
        }

        .totals-table td.label {
            color: #666;
        }

        .totals-table td.value {
            text-align: right;
        }

        .totals-table .discount td {
            color: #dc2626;
        }

        .totals-table .grand-total td {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 12px;
            padding-top: 6px;
            color: #222;
        }

        /* Bemerkungen */
        .notes {
            margin-top: 10mm;
            padding: 8px 10px;
            background: #f9f9f9;
            border-left: 3px solid #ddd;
            font-size: 9px;
            color: #555;
        }

        /* Fusszeile */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            border-top: 1px solid #ccc;
            padding-top: 3mm;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            font-size: 7.5px;
            color: #888;
            line-height: 1.5;
            vertical-align: top;
        }

        .footer-label {
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>

@if($isDraft)
    <div class="watermark">ENTWURF</div>
@endif

<div class="footer">
    <table class="footer-table">
        <tr>
            <td style="width: 30%">
                @if($company->company_name)
                    <span class="footer-label">{{ $company->company_name }}</span>
                    @if($company->legal_form) {{ $company->legal_form }}@endif
                    <br>
                @endif
                @if($company->street){{ $company->street }}<br>@endif
                @if($company->zip || $company->city){{ $company->zip }} {{ $company->city }}<br>@endif
                @if($company->managing_director)GF: {{ $company->managing_director }}@endif
            </td>
            <td style="width: 25%">
                @if($company->phone)<span class="footer-label">Tel:</span> {{ $company->phone }}<br>@endif
                @if($company->fax)<span class="footer-label">Fax:</span> {{ $company->fax }}<br>@endif
                @if($company->email){{ $company->email }}<br>@endif
                @if($company->website){{ $company->website }}@endif
            </td>
            <td style="width: 25%">
                @if($company->bank_name)<span class="footer-label">{{ $company->bank_name }}</span><br>@endif
                @if($company->iban)<span class="footer-label">IBAN:</span> {{ $company->iban }}<br>@endif
                @if($company->bic)<span class="footer-label">BIC:</span> {{ $company->bic }}@endif
            </td>
            <td style="width: 20%">
                @if($company->vat_id)<span class="footer-label">USt-IdNr.:</span> {{ $company->vat_id }}<br>@endif
                @if($company->tax_number)<span class="footer-label">Steuer-Nr.:</span> {{ $company->tax_number }}<br>@endif
                @if($company->trade_register){{ $company->trade_register }}@endif
            </td>
        </tr>
    </table>
</div>

<div class="page">
    {{-- Absenderzeile --}}
    <div class="sender-line">
        {{ $company->company_name ?? '' }}
        @if($company->street) &middot; {{ $company->street }}@endif
        @if($company->zip || $company->city) &middot; {{ $company->zip }} {{ $company->city }}@endif
    </div>

    {{-- Header --}}
    <table class="header">
        <tr>
            <td class="recipient">
                @if($document->customer)
                    <strong>{{ $document->customer->name }}</strong><br>
                    @if($document->customer->street){{ $document->customer->street }}<br>@endif
                    @if($document->customer->zip || $document->customer->city)
                        {{ $document->customer->zip }} {{ $document->customer->city }}<br>
                    @endif
                    @if($document->customer->country && $document->customer->country !== 'DE')
                        {{ $document->customer->country }}<br>
                    @endif
                @endif
            </td>
            <td class="company-info">
                @if($company->logo_path)
                    <img src="{{ storage_path('app/public/' . $company->logo_path) }}" class="company-logo" alt="Logo"><br>
                @endif
                <span class="company-name">{{ $company->company_name ?? '' }}</span>
                @if($company->legal_form) {{ $company->legal_form }}@endif
                <br>
                @if($company->street){{ $company->street }}<br>@endif
                @if($company->zip || $company->city){{ $company->zip }} {{ $company->city }}@endif
            </td>
        </tr>
    </table>

    {{-- Dokumenttitel --}}
    <div class="document-title">{{ $title }}</div>

    {{-- Dokumentdaten --}}
    <table class="document-meta">
        <tr>
            <td class="label">{{ $numberLabel }}:</td>
            <td class="value">{{ $numberValue }}</td>
        </tr>
        <tr>
            <td class="label">{{ $dateLabel }}:</td>
            <td class="value">
                @if($dateValue instanceof \Carbon\Carbon)
                    {{ $dateValue->format('d.m.Y') }}
                @else
                    {{ $dateValue }}
                @endif
            </td>
        </tr>
        @if(!empty($extraFields))
            @foreach($extraFields as $label => $value)
                @if($value)
                    <tr>
                        <td class="label">{{ $label }}:</td>
                        <td class="value">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        @endif
        @if($document->customer && $document->customer->vat_id)
            <tr>
                <td class="label">USt-IdNr. Kunde:</td>
                <td class="value">{{ $document->customer->vat_id }}</td>
            </tr>
        @endif
    </table>

    {{-- Positionen --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">Pos.</th>
                @if(!empty($discount))
                    <th style="width: 28%">Beschreibung</th>
                    <th class="right" style="width: 8%">Menge</th>
                    <th style="width: 8%">Einheit</th>
                    <th class="right" style="width: 12%">Listenpreis</th>
                    <th class="right" style="width: 8%">Rabatt</th>
                    <th class="right" style="width: 12%">Einzelpreis</th>
                    <th class="right" style="width: 7%">MwSt</th>
                    <th class="right" style="width: 12%">Gesamt</th>
                @else
                    <th style="width: 40%">Beschreibung</th>
                    <th class="right" style="width: 10%">Menge</th>
                    <th style="width: 10%">Einheit</th>
                    <th class="right" style="width: 15%">Einzelpreis</th>
                    <th class="right" style="width: 8%">MwSt</th>
                    <th class="right" style="width: 12%">Gesamt</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($document->items->sortBy('sort_order') as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->article?->name ?? $item->description }}</strong>
                        @if($item->description)
                            <br><span style="font-weight: normal; font-size: 9px; color: #555;">{{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td>{{ $item->unit }}</td>
                    @if(!empty($discount))
                        <td class="right" style="text-decoration: line-through; color: #999;">
                            {{ number_format($item->article?->net_price ?? $item->net_price, 2, ',', '.') }} &euro;
                        </td>
                        <td class="right" style="color: #dc2626;">
                            -{{ number_format($discount['percent'], 2, ',', '.') }} %
                        </td>
                    @endif
                    <td class="right">{{ number_format($item->net_price, 2, ',', '.') }} &euro;</td>
                    <td class="right">{{ number_format($item->vat_rate, 0) }} %</td>
                    <td class="right">{{ number_format($item->line_total, 2, ',', '.') }} &euro;</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summen --}}
    <div class="totals-wrapper">
        <table class="totals-table">
            <tr>
                <td class="label">Netto-Summe:</td>
                <td class="value">{{ number_format($document->net_total, 2, ',', '.') }} &euro;</td>
            </tr>

            @if(!empty($discount))
                <tr>
                    <td colspan="2" style="font-size: 8px; color: #888; font-style: italic; padding: 1px 10px;">
                        inkl. {{ number_format($discount['percent'], 2, ',', '.') }} % Kundenrabatt
                    </td>
                </tr>
            @endif

            @php
                $items = $document->items;
                $vatGroups = [];

                foreach ($items as $item) {
                    $rate = number_format($item->vat_rate, 2);
                    if (!isset($vatGroups[$rate])) {
                        $vatGroups[$rate] = 0;
                    }
                    $vatGroups[$rate] += $item->line_total * $item->vat_rate / 100;
                }
                ksort($vatGroups);
            @endphp

            @foreach($vatGroups as $rate => $vatAmount)
                <tr>
                    <td class="label">MwSt {{ number_format((float)$rate, 0) }} %:</td>
                    <td class="value">{{ number_format($vatAmount, 2, ',', '.') }} &euro;</td>
                </tr>
            @endforeach

            <tr class="grand-total">
                <td class="label">Brutto-Summe:</td>
                <td class="value">{{ number_format($document->gross_total, 2, ',', '.') }} &euro;</td>
            </tr>
        </table>
    </div>

    {{-- Bemerkungen --}}
    @if($document->notes)
        <div class="notes">
            {{ $document->notes }}
        </div>
    @endif

    {{-- Zahlungshinweis fuer Rechnungen --}}
    @if($type === 'invoice')
        <div style="margin-top: 10mm; font-size: 10px;">
            Bitte ueberweisen Sie den Betrag
            @if($document->due_date)
                bis zum <strong>{{ $document->due_date->format('d.m.Y') }}</strong>
            @endif
            auf das unten genannte Konto.
        </div>
    @endif

    {{-- Gueltigkeit fuer Angebote --}}
    @if($type === 'quote' && $document->valid_until)
        <div style="margin-top: 10mm; font-size: 10px;">
            Dieses Angebot ist gueltig bis zum <strong>{{ $document->valid_until->format('d.m.Y') }}</strong>.
        </div>
    @endif
</div>

</body>
</html>
