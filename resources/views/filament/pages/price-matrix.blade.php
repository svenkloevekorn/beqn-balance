<x-filament-panels::page>
    @if(count($customers) === 0)
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <p class="text-lg font-medium">Keine Kunden mit individuellen Preisen vorhanden.</p>
            <p class="mt-2 text-sm">Setze bei einem Kunden den Preismodus auf &bdquo;Individuelle Preise&ldquo;, damit er hier erscheint.</p>
        </div>
    @else
        @php
            $totalCustomers = count($customers);
            $maxStart = max(0, $totalCustomers - $visibleCount);
            $visibleCustomers = array_slice($customers, $startIndex, $visibleCount);
            $endIndex = $startIndex + count($visibleCustomers);
        @endphp

        {{-- Kunden-Navigation --}}
        <div class="customer-nav" x-data x-on:keydown.left.window="if (document.activeElement.tagName !== 'INPUT') $wire.previousCustomers()" x-on:keydown.right.window="if (document.activeElement.tagName !== 'INPUT') $wire.nextCustomers()">
            <button
                wire:click="previousCustomers"
                @if($startIndex === 0) disabled @endif
                class="nav-btn"
                title="Vorherige Kunden"
            >
                <x-heroicon-m-chevron-left class="w-5 h-5" />
            </button>

            <div class="nav-label">
                <span class="nav-position">Kunden {{ $startIndex + 1 }}&ndash;{{ $endIndex }} von {{ $totalCustomers }}</span>
            </div>

            <div class="nav-separator"></div>

            <div class="nav-page-size">
                <span class="page-size-label">Anzeige:</span>
                <select wire:change="setVisibleCount($event.target.value)" class="page-size-select">
                    @foreach(\App\Filament\Pages\PriceMatrix::VISIBLE_OPTIONS as $option)
                        <option value="{{ $option }}" @if($visibleCount === $option) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <button
                wire:click="nextCustomers"
                @if($startIndex >= $maxStart) disabled @endif
                class="nav-btn"
                title="N&auml;chste Kunden"
            >
                <x-heroicon-m-chevron-right class="w-5 h-5" />
            </button>
        </div>

        {{-- Preistabelle --}}
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm">
            <table class="price-matrix-table">
                <thead>
                    <tr>
                        <th class="col-article text-left">Artikel</th>
                        <th class="col-netto">Netto</th>
                        @foreach($visibleCustomers as $customer)
                            <th class="col-customer" title="{{ $customer['name'] }}">
                                {{ $customer['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                        <tr>
                            <td class="article-name">
                                {{ $article['name'] }}
                            </td>
                            <td class="netto-price">
                                {{ number_format((float) $article['net_price'], 2, ',', '.') }}&nbsp;&euro;
                            </td>
                            @foreach($visibleCustomers as $customer)
                                @php
                                    $key = $article['id'] . '_' . $customer['id'];
                                    $currentValue = $prices[$key] ?? '';
                                @endphp
                                <td class="price-cell">
                                    <input
                                        type="text"
                                        inputmode="decimal"
                                        class="price-input {{ $currentValue !== '' ? 'has-value' : '' }}"
                                        value="{{ $currentValue !== '' ? number_format((float) $currentValue, 2, ',', '.') : '' }}"
                                        placeholder="—"
                                        wire:change="updatePrice({{ $article['id'] }}, {{ $customer['id'] }}, $event.target.value)"
                                    >
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-4">
            <span>{{ count($articles) }} Artikel</span>
            <span>{{ $totalCustomers }} Kunden</span>
            <span>Preis eingeben = aktiv &middot; Feld leeren = deaktiviert</span>
            <span class="ml-auto">Pfeiltasten &larr; &rarr; zum Bl&auml;ttern</span>
        </div>
    @endif

    <style>
        /* Kunden-Navigation */
        .customer-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 16px;
            padding: 12px 16px;
            background: white;
            border: 1px solid rgb(229 231 235);
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        :is(.dark) .customer-nav {
            background: rgb(17 24 39);
            border-color: rgba(255 255 255 / 0.1);
        }

        .nav-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid rgb(209 213 219);
            background: white;
            color: rgb(55 65 81);
            cursor: pointer;
            transition: all 0.15s;
        }

        :is(.dark) .nav-btn {
            background: rgb(31 41 55);
            border-color: rgba(255 255 255 / 0.15);
            color: rgb(209 213 219);
        }

        .nav-btn:hover:not(:disabled) {
            background: rgb(243 244 246);
            border-color: rgb(156 163 175);
        }

        :is(.dark) .nav-btn:hover:not(:disabled) {
            background: rgb(55 65 81);
        }

        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .nav-label {
            display: flex;
            align-items: center;
            min-width: 180px;
            justify-content: center;
        }

        .nav-position {
            font-size: 14px;
            font-weight: 600;
            color: rgb(17 24 39);
        }

        :is(.dark) .nav-position {
            color: white;
        }

        .nav-separator {
            width: 1px;
            height: 24px;
            background: rgb(209 213 219);
        }

        :is(.dark) .nav-separator {
            background: rgba(255 255 255 / 0.15);
        }

        .nav-page-size {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-size-label {
            font-size: 12px;
            color: rgb(107 114 128);
            white-space: nowrap;
        }

        :is(.dark) .page-size-label {
            color: rgb(156 163 175);
        }

        .page-size-select {
            padding: 4px 24px 4px 8px;
            border: 1px solid rgb(209 213 219);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            background: white;
            color: rgb(17 24 39);
            cursor: pointer;
            appearance: auto;
        }

        :is(.dark) .page-size-select {
            background: rgb(31 41 55);
            border-color: rgba(255 255 255 / 0.15);
            color: white;
        }

        .page-size-select:focus {
            outline: none;
            border-color: rgb(59 130 246);
            box-shadow: 0 0 0 2px rgba(59 130 246 / 0.25);
        }

        /* Tabelle */
        .price-matrix-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-size: 13px;
            table-layout: fixed;
        }

        .price-matrix-table thead th {
            background: rgb(249 250 251);
            border-bottom: 2px solid rgb(229 231 235);
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgb(107 114 128);
            white-space: nowrap;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        :is(.dark) .price-matrix-table thead th {
            background: rgb(17 24 39);
            border-bottom-color: rgba(255 255 255 / 0.1);
            color: rgb(156 163 175);
        }

        .price-matrix-table tbody td {
            padding: 6px 12px;
            border-bottom: 1px solid rgb(243 244 246);
        }

        :is(.dark) .price-matrix-table tbody td {
            border-bottom-color: rgba(255 255 255 / 0.05);
        }

        .price-matrix-table tbody tr:hover td {
            background: rgb(249 250 251);
        }

        :is(.dark) .price-matrix-table tbody tr:hover td {
            background: rgba(255 255 255 / 0.03);
        }

        /* Spalten */
        .col-article {
            width: 180px;
        }

        .article-name {
            width: 180px;
            font-weight: 500;
            color: rgb(17 24 39);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        :is(.dark) .article-name {
            color: white;
        }

        .col-netto {
            width: 90px;
            text-align: right;
        }

        .netto-price {
            width: 90px;
            text-align: right;
            color: rgb(107 114 128);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        :is(.dark) .netto-price {
            color: rgb(156 163 175);
        }

        /* Kundenspalten teilen sich den restlichen Platz gleichmäßig */
        .col-customer {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Preis-Input */
        .price-cell {
            padding: 4px 6px !important;
            text-align: center;
        }

        .price-input {
            width: 100%;
            padding: 4px 8px;
            border: 1px solid rgb(229 231 235);
            border-radius: 6px;
            text-align: right;
            font-size: 13px;
            font-variant-numeric: tabular-nums;
            background: transparent;
            color: rgb(107 114 128);
            transition: all 0.15s;
        }

        :is(.dark) .price-input {
            border-color: rgba(255 255 255 / 0.1);
            color: rgb(156 163 175);
        }

        .price-input.has-value {
            color: rgb(17 24 39);
            font-weight: 600;
            background: rgb(239 246 255);
            border-color: rgb(147 197 253);
        }

        :is(.dark) .price-input.has-value {
            color: rgb(219 234 254);
            background: rgba(59 130 246 / 0.15);
            border-color: rgba(59 130 246 / 0.4);
        }

        .price-input:focus {
            outline: none;
            border-color: rgb(59 130 246);
            box-shadow: 0 0 0 2px rgba(59 130 246 / 0.25);
            color: rgb(17 24 39);
        }

        :is(.dark) .price-input:focus {
            color: white;
        }

        .price-input::placeholder {
            color: rgb(209 213 219);
            font-weight: 400;
        }

        :is(.dark) .price-input::placeholder {
            color: rgb(75 85 99);
        }
    </style>
</x-filament-panels::page>
