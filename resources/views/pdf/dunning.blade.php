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

        /* Rechnungsdaten-Tabelle */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8mm;
        }

        .invoice-table thead th {
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

        .invoice-table thead th.right {
            text-align: right;
        }

        .invoice-table tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
        }

        .invoice-table tbody td.right {
            text-align: right;
        }

        .invoice-table tfoot td {
            padding: 6px 8px;
            font-size: 10px;
            font-weight: bold;
            border-top: 2px solid #333;
        }

        .invoice-table tfoot td.right {
            text-align: right;
        }

        /* Mahntext */
        .dunning-text {
            margin-top: 8mm;
            margin-bottom: 8mm;
            font-size: 10px;
            line-height: 1.6;
            white-space: pre-line;
        }

        /* Zahlungshinweis */
        .payment-notice {
            margin-top: 10mm;
            padding: 8px 10px;
            background: #f9f9f9;
            border-left: 3px solid #dc2626;
            font-size: 10px;
            line-height: 1.6;
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
                @if($dunning->invoice->customer)
                    <strong>{{ $dunning->invoice->customer->name }}</strong><br>
                    @if($dunning->invoice->customer->street){{ $dunning->invoice->customer->street }}<br>@endif
                    @if($dunning->invoice->customer->zip || $dunning->invoice->customer->city)
                        {{ $dunning->invoice->customer->zip }} {{ $dunning->invoice->customer->city }}<br>
                    @endif
                    @if($dunning->invoice->customer->country && $dunning->invoice->customer->country !== 'DE')
                        {{ $dunning->invoice->customer->country }}<br>
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
    <div class="document-title">{{ $dunning->subject }}</div>

    {{-- Dokumentdaten --}}
    <table class="document-meta">
        <tr>
            <td class="label">Mahnstufe:</td>
            <td class="value">{{ $dunning->level->getLabel() }}</td>
        </tr>
        <tr>
            <td class="label">Mahndatum:</td>
            <td class="value">{{ $dunning->dunning_date->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="label">Rechnungsnummer:</td>
            <td class="value">{{ $dunning->invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td class="label">Rechnungsdatum:</td>
            <td class="value">{{ $dunning->invoice->invoice_date->format('d.m.Y') }}</td>
        </tr>
        @if($dunning->invoice->customer && $dunning->invoice->customer->vat_id)
            <tr>
                <td class="label">USt-IdNr. Kunde:</td>
                <td class="value">{{ $dunning->invoice->customer->vat_id }}</td>
            </tr>
        @endif
    </table>

    {{-- Mahntext --}}
    <div class="dunning-text">{{ $dunning->text }}</div>

    {{-- Rechnungsuebersicht --}}
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Beschreibung</th>
                <th class="right">Betrag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Offener Rechnungsbetrag (Rechnung {{ $dunning->invoice->invoice_number }})</td>
                <td class="right">{{ number_format($dunning->invoice->remaining_amount, 2, ',', '.') }} &euro;</td>
            </tr>
            @if($dunning->fee > 0)
                <tr>
                    <td>Mahngebuehr</td>
                    <td class="right">{{ number_format($dunning->fee, 2, ',', '.') }} &euro;</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Gesamtbetrag</td>
                <td class="right">{{ number_format($dunning->invoice->remaining_amount + $dunning->fee, 2, ',', '.') }} &euro;</td>
            </tr>
        </tfoot>
    </table>

    {{-- Zahlungshinweis --}}
    <div class="payment-notice">
        Bitte ueberweisen Sie den Gesamtbetrag von <strong>{{ number_format($dunning->invoice->remaining_amount + $dunning->fee, 2, ',', '.') }} &euro;</strong>
        bis zum <strong>{{ $dunning->due_date->format('d.m.Y') }}</strong>
        auf das unten genannte Konto.
        @if($company->iban)
            <br>IBAN: <strong>{{ $company->iban }}</strong>
            @if($company->bic) | BIC: <strong>{{ $company->bic }}</strong>@endif
        @endif
    </div>
</div>

</body>
</html>
