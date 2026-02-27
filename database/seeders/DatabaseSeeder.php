<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\ContactPerson;
use App\Models\Customer;
use App\Models\CustomerArticlePrice;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\NumberRange;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Firmenstammdaten
        CompanySetting::create([
            'company_name' => 'BEQN Kaffeemanufaktur GmbH',
            'legal_form' => 'GmbH',
            'managing_director' => 'Thomas Brenner',
            'street' => 'Roeststraße 12',
            'zip' => '50678',
            'city' => 'Koeln',
            'country' => 'DE',
            'phone' => '0221 33445566',
            'fax' => '0221 33445567',
            'email' => 'info@beqn-kaffee.de',
            'website' => 'https://www.beqn-kaffee.de',
            'vat_id' => 'DE316789012',
            'tax_number' => '214/5678/0123',
            'trade_register' => 'HRB 98765, Amtsgericht Koeln',
            'bank_name' => 'Sparkasse KoelnBonn',
            'iban' => 'DE89 3705 0198 0012 3456 78',
            'bic' => 'COLSDE33XXX',
        ]);

        // Rollen
        $allPermissions = [];
        foreach (Role::$resources as $resource => $label) {
            foreach (Role::$abilities as $ability => $abilityLabel) {
                $allPermissions[$resource][$ability] = true;
            }
        }

        $adminRole = Role::create([
            'name' => 'Administrator',
            'permissions' => $allPermissions,
            'is_super_admin' => true,
        ]);

        $buchhaltungPermissions = [];
        foreach (['customers', 'suppliers', 'articles', 'categories', 'invoices', 'incoming_invoices', 'quotes', 'delivery_notes'] as $resource) {
            foreach (Role::$abilities as $ability => $label) {
                $buchhaltungPermissions[$resource][$ability] = true;
            }
        }
        $buchhaltungPermissions['settings']['view'] = true;

        Role::create([
            'name' => 'Buchhalter',
            'permissions' => $buchhaltungPermissions,
        ]);

        $mitarbeiterPermissions = [];
        foreach (['customers', 'articles', 'quotes', 'delivery_notes'] as $resource) {
            $mitarbeiterPermissions[$resource]['view'] = true;
        }
        $mitarbeiterPermissions['invoices']['view'] = true;

        Role::create([
            'name' => 'Mitarbeiter',
            'permissions' => $mitarbeiterPermissions,
        ]);

        // Benutzer
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => $adminRole->id,
            'is_super_admin' => true,
        ]);

        // Kunden
        $mustermann = Customer::create([
            'name' => 'Mustermann GmbH',
            'street' => 'Musterstraße 1',
            'zip' => '10115',
            'city' => 'Berlin',
            'country' => 'DE',
            'email' => 'info@mustermann.de',
            'phone' => '030 12345678',
            'vat_id' => 'DE123456789',
            'payment_term_days' => 14,
            'discount_percent' => 5.00,
            'pricing_mode' => 'percentage',
            'notes' => 'Stammkunde seit 2024, bevorzugt Hausmischung',
        ]);

        ContactPerson::create([
            'customer_id' => $mustermann->id,
            'name' => 'Max Mustermann',
            'position' => 'Geschaeftsfuehrer',
            'email' => 'max@mustermann.de',
            'phone' => '030 12345678',
        ]);

        ContactPerson::create([
            'customer_id' => $mustermann->id,
            'name' => 'Erika Mustermann',
            'position' => 'Einkauf',
            'email' => 'erika@mustermann.de',
            'phone' => '030 12345679',
        ]);

        $beispiel = Customer::create([
            'name' => 'Beispiel AG',
            'street' => 'Beispielweg 42',
            'zip' => '80331',
            'city' => 'München',
            'country' => 'DE',
            'email' => 'buchhaltung@beispiel-ag.de',
            'phone' => '089 98765432',
            'vat_id' => 'DE987654321',
            'payment_term_days' => 30,
            'discount_percent' => 10.00,
            'pricing_mode' => 'percentage',
        ]);

        ContactPerson::create([
            'customer_id' => $beispiel->id,
            'name' => 'Anna Schmidt',
            'position' => 'Bueroleitung',
            'email' => 'a.schmidt@beispiel-ag.de',
        ]);

        Customer::create([
            'name' => 'Schmidt & Partner',
            'street' => 'Hauptstraße 15',
            'zip' => '20095',
            'city' => 'Hamburg',
            'country' => 'DE',
            'email' => 'kontakt@schmidt-partner.de',
            'phone' => '040 55667788',
            'payment_term_days' => 14,
            'pricing_mode' => 'none',
        ]);

        // Kunde mit individuellen Preisen
        $cafeWunderbar = Customer::create([
            'name' => 'Cafe Wunderbar',
            'street' => 'Marktplatz 7',
            'zip' => '60311',
            'city' => 'Frankfurt',
            'country' => 'DE',
            'email' => 'bestellung@cafe-wunderbar.de',
            'phone' => '069 22334455',
            'payment_term_days' => 7,
            'pricing_mode' => 'custom_prices',
            'notes' => 'Grossabnehmer Kaffee, Sonderkonditionen vereinbart',
        ]);

        ContactPerson::create([
            'customer_id' => $cafeWunderbar->id,
            'name' => 'Lisa Weber',
            'position' => 'Inhaberin',
            'email' => 'lisa@cafe-wunderbar.de',
            'phone' => '069 22334455',
        ]);

        // Weiterer Kunde mit individuellen Preisen
        $hotelKrone = Customer::create([
            'name' => 'Hotel Krone GmbH & Co. KG',
            'street' => 'Am Schlosspark 1',
            'zip' => '69117',
            'city' => 'Heidelberg',
            'country' => 'DE',
            'email' => 'einkauf@hotel-krone.de',
            'phone' => '06221 998877',
            'vat_id' => 'DE556677889',
            'payment_term_days' => 21,
            'pricing_mode' => 'custom_prices',
        ]);

        ContactPerson::create([
            'customer_id' => $hotelKrone->id,
            'name' => 'Klaus Berger',
            'position' => 'F&B Manager',
            'email' => 'k.berger@hotel-krone.de',
            'phone' => '06221 998800',
        ]);

        // Kunde mit prozentualem Rabatt
        $bueroPeters = Customer::create([
            'name' => 'Buero Peters OHG',
            'street' => 'Friedrichstraße 88',
            'zip' => '40217',
            'city' => 'Duesseldorf',
            'country' => 'DE',
            'email' => 'office@buero-peters.de',
            'phone' => '0211 7766554',
            'payment_term_days' => 14,
            'discount_percent' => 3.00,
            'pricing_mode' => 'percentage',
        ]);

        // Kunde ohne Rabatt
        $privatkunde = Customer::create([
            'name' => 'Dr. Martin Schulze',
            'street' => 'Lindenallee 23',
            'zip' => '53113',
            'city' => 'Bonn',
            'country' => 'DE',
            'email' => 'm.schulze@posteo.de',
            'phone' => '0228 1122334',
            'payment_term_days' => 14,
            'pricing_mode' => 'none',
        ]);

        // Kunde mit Rabatt
        $baeckerei = Customer::create([
            'name' => 'Baeckerei Sonnenschein',
            'street' => 'Backstubenweg 3',
            'zip' => '51065',
            'city' => 'Koeln',
            'country' => 'DE',
            'email' => 'info@baeckerei-sonnenschein.de',
            'phone' => '0221 8899001',
            'payment_term_days' => 7,
            'discount_percent' => 8.00,
            'pricing_mode' => 'percentage',
            'notes' => 'Liefert montags und donnerstags',
        ]);

        ContactPerson::create([
            'customer_id' => $baeckerei->id,
            'name' => 'Petra Sonnenschein',
            'position' => 'Inhaberin',
            'email' => 'petra@baeckerei-sonnenschein.de',
        ]);

        // Lieferanten
        Supplier::create([
            'name' => 'Bürobedarf24 GmbH',
            'street' => 'Industriestraße 8',
            'zip' => '50667',
            'city' => 'Köln',
            'country' => 'DE',
            'email' => 'bestellung@buerobedarf24.de',
            'phone' => '0221 11223344',
            'vat_id' => 'DE111222333',
            'payment_term_days' => 14,
        ]);

        Supplier::create([
            'name' => 'TechSupply Europe',
            'street' => 'Am Hafen 3',
            'zip' => '28195',
            'city' => 'Bremen',
            'country' => 'DE',
            'email' => 'invoice@techsupply.eu',
            'phone' => '0421 44556677',
            'vat_id' => 'DE444555666',
            'payment_term_days' => 30,
        ]);

        // Kategorien (Kaffee-Roesterei)
        $kaffee = Category::create([
            'name' => 'Kaffee',
            'description' => 'Roestkaffee, Bohnen und gemahlener Kaffee',
            'color' => '#8B4513',
        ]);

        $equipment = Category::create([
            'name' => 'Equipment',
            'description' => 'Muehlen, Maschinen und Zubehoer',
            'color' => '#6B7280',
        ]);

        $verpackung = Category::create([
            'name' => 'Verpackung',
            'description' => 'Tueten, Etiketten und Verpackungsmaterial',
            'color' => '#16A34A',
        ]);

        $dienstleistung = Category::create([
            'name' => 'Dienstleistung',
            'description' => 'Beratung, Schulungen und Barista-Kurse',
            'color' => '#2563EB',
        ]);

        $merchandise = Category::create([
            'name' => 'Merchandise',
            'description' => 'Tassen, Bekleidung und Geschenkartikel',
            'color' => '#EA580C',
        ]);

        $rohkaffee = Category::create([
            'name' => 'Rohkaffee',
            'description' => 'Gruener Rohkaffee, ungeröstet',
            'color' => '#65A30D',
        ]);

        // Artikel
        $a1 = Article::create([
            'name' => 'Hausmischung 250g',
            'description' => 'Unsere beliebte Hausmischung, ganze Bohne',
            'unit' => 'Stück',
            'net_price' => 8.90,
            'vat_rate' => 7.00,
        ]);
        $a1->categories()->attach([$kaffee->id]);

        $a2 = Article::create([
            'name' => 'Espresso Intenso 1kg',
            'description' => 'Kraeftiger Espresso, dunkle Roestung',
            'unit' => 'Stück',
            'net_price' => 28.00,
            'vat_rate' => 7.00,
        ]);
        $a2->categories()->attach([$kaffee->id]);

        $a3 = Article::create([
            'name' => 'Barista-Kurs Einsteiger',
            'description' => 'Halbtaegiger Workshop fuer Einsteiger',
            'unit' => 'Pauschal',
            'net_price' => 89.00,
            'vat_rate' => 19.00,
        ]);
        $a3->categories()->attach([$dienstleistung->id]);

        $a4 = Article::create([
            'name' => 'Kaffeebeutel 250g mit Ventil',
            'description' => 'Standbodenbeutel mit Aromaventil, 100 Stueck',
            'unit' => 'Stück',
            'net_price' => 45.00,
            'vat_rate' => 19.00,
        ]);
        $a4->categories()->attach([$verpackung->id]);

        $a5 = Article::create([
            'name' => 'Keramiktasse mit Logo',
            'description' => 'Handgefertigte Tasse mit Roesterei-Logo',
            'unit' => 'Stück',
            'net_price' => 12.50,
            'vat_rate' => 19.00,
        ]);
        $a5->categories()->attach([$merchandise->id]);

        $a6 = Article::create([
            'name' => 'Handmuehle Comandante',
            'description' => 'Hochwertige Handkaffeemuehle',
            'unit' => 'Stück',
            'net_price' => 210.00,
            'vat_rate' => 19.00,
        ]);
        $a6->categories()->attach([$equipment->id]);

        $a7 = Article::create([
            'name' => 'Filterkaffee Colombia 500g',
            'description' => 'Helle Roestung, fruchtig-florale Noten',
            'unit' => 'Stück',
            'net_price' => 14.50,
            'vat_rate' => 7.00,
        ]);
        $a7->categories()->attach([$kaffee->id]);

        $a8 = Article::create([
            'name' => 'Crema Classico 1kg',
            'description' => 'Ausgewogener Alltagskaffee, ganze Bohne',
            'unit' => 'Stück',
            'net_price' => 22.00,
            'vat_rate' => 7.00,
        ]);
        $a8->categories()->attach([$kaffee->id]);

        $a9 = Article::create([
            'name' => 'Entkoffeiniert Swiss Water 250g',
            'description' => 'Schonend entkoffeiniert, voller Geschmack',
            'unit' => 'Stück',
            'net_price' => 9.80,
            'vat_rate' => 7.00,
        ]);
        $a9->categories()->attach([$kaffee->id]);

        $a10 = Article::create([
            'name' => 'Barista-Kurs Fortgeschrittene',
            'description' => 'Ganztagsworkshop Latte Art und Extraktion',
            'unit' => 'Pauschal',
            'net_price' => 149.00,
            'vat_rate' => 19.00,
        ]);
        $a10->categories()->attach([$dienstleistung->id]);

        $a11 = Article::create([
            'name' => 'Rohkaffee Ethiopia Yirgacheffe 5kg',
            'description' => 'Gruener Rohkaffee, Spezialitaetenqualitaet',
            'unit' => 'Stück',
            'net_price' => 62.00,
            'vat_rate' => 7.00,
        ]);
        $a11->categories()->attach([$rohkaffee->id]);

        $a12 = Article::create([
            'name' => 'Tamper 58mm Edelstahl',
            'description' => 'Professioneller Tamper mit Holzgriff',
            'unit' => 'Stück',
            'net_price' => 35.00,
            'vat_rate' => 19.00,
        ]);
        $a12->categories()->attach([$equipment->id]);

        $a13 = Article::create([
            'name' => 'Milchkanne 350ml',
            'description' => 'Edelstahl Milchkanne fuer Latte Art',
            'unit' => 'Stück',
            'net_price' => 18.50,
            'vat_rate' => 19.00,
        ]);
        $a13->categories()->attach([$equipment->id]);

        $a14 = Article::create([
            'name' => 'Kaffeebeutel 1kg mit Ventil',
            'description' => 'Standbodenbeutel mit Aromaventil, 50 Stueck',
            'unit' => 'Stück',
            'net_price' => 38.00,
            'vat_rate' => 19.00,
        ]);
        $a14->categories()->attach([$verpackung->id]);

        $a15 = Article::create([
            'name' => 'Geschenkbox Kaffee-Entdecker',
            'description' => '3x 100g verschiedene Single Origins mit Tasse',
            'unit' => 'Stück',
            'net_price' => 29.90,
            'vat_rate' => 19.00,
        ]);
        $a15->categories()->attach([$merchandise->id, $kaffee->id]);

        // Inaktiver Artikel
        Article::create([
            'name' => 'Espresso Roma 250g (AUSLAUF)',
            'description' => 'Wird nicht mehr produziert',
            'unit' => 'Stück',
            'net_price' => 7.50,
            'vat_rate' => 7.00,
            'is_active' => false,
        ]);

        // Individuelle Preise fuer Cafe Wunderbar
        CustomerArticlePrice::create([
            'customer_id' => $cafeWunderbar->id,
            'article_id' => $a1->id,
            'custom_net_price' => 7.20,
            'is_active' => true,
        ]);
        CustomerArticlePrice::create([
            'customer_id' => $cafeWunderbar->id,
            'article_id' => $a2->id,
            'custom_net_price' => 23.50,
            'is_active' => true,
        ]);
        CustomerArticlePrice::create([
            'customer_id' => $cafeWunderbar->id,
            'article_id' => $a7->id,
            'custom_net_price' => 11.80,
            'is_active' => true,
        ]);
        CustomerArticlePrice::create([
            'customer_id' => $cafeWunderbar->id,
            'article_id' => $a8->id,
            'custom_net_price' => 18.00,
            'is_active' => true,
        ]);

        // Individuelle Preise fuer Hotel Krone
        CustomerArticlePrice::create([
            'customer_id' => $hotelKrone->id,
            'article_id' => $a2->id,
            'custom_net_price' => 24.00,
            'is_active' => true,
        ]);
        CustomerArticlePrice::create([
            'customer_id' => $hotelKrone->id,
            'article_id' => $a8->id,
            'custom_net_price' => 19.00,
            'is_active' => true,
        ]);
        CustomerArticlePrice::create([
            'customer_id' => $hotelKrone->id,
            'article_id' => $a9->id,
            'custom_net_price' => 8.00,
            'is_active' => true,
        ]);
        CustomerArticlePrice::create([
            'customer_id' => $hotelKrone->id,
            'article_id' => $a5->id,
            'custom_net_price' => 10.00,
            'is_active' => true,
        ]);

        // --- Angebote ---

        // AN-1: Mustermann, mit Rabatt, versendet
        $quote1 = Quote::create([
            'quote_number' => 'AN-2026-0001',
            'customer_id' => $mustermann->id,
            'quote_date' => now()->subDays(14),
            'valid_until' => now()->addDays(16),
            'status' => 'sent',
            'apply_discount' => true,
            'discount_percent' => $mustermann->discount_percent,
        ]);
        QuoteItem::create([
            'quote_id' => $quote1->id,
            'article_id' => $a1->id,
            'description' => 'Hausmischung 250g',
            'quantity' => 50,
            'unit' => 'Stück',
            'net_price' => 8.90,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        QuoteItem::create([
            'quote_id' => $quote1->id,
            'article_id' => $a5->id,
            'description' => 'Keramiktasse mit Logo',
            'quantity' => 20,
            'unit' => 'Stück',
            'net_price' => 12.50,
            'vat_rate' => 19.00,
            'sort_order' => 1,
        ]);

        // AN-2: Cafe Wunderbar, individuelle Preise, Entwurf
        $quote2 = Quote::create([
            'quote_number' => 'AN-2026-0002',
            'customer_id' => $cafeWunderbar->id,
            'quote_date' => now()->subDays(3),
            'valid_until' => now()->addDays(27),
            'status' => 'draft',
            'apply_discount' => false,
        ]);
        QuoteItem::create([
            'quote_id' => $quote2->id,
            'article_id' => $a1->id,
            'description' => 'Hausmischung 250g',
            'quantity' => 100,
            'unit' => 'Stück',
            'net_price' => 7.20,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        QuoteItem::create([
            'quote_id' => $quote2->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 20,
            'unit' => 'Stück',
            'net_price' => 23.50,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);
        QuoteItem::create([
            'quote_id' => $quote2->id,
            'article_id' => $a8->id,
            'description' => 'Crema Classico 1kg',
            'quantity' => 15,
            'unit' => 'Stück',
            'net_price' => 18.00,
            'vat_rate' => 7.00,
            'sort_order' => 2,
        ]);

        // AN-3: Hotel Krone, individuelle Preise, angenommen
        $quote3 = Quote::create([
            'quote_number' => 'AN-2026-0003',
            'customer_id' => $hotelKrone->id,
            'quote_date' => now()->subDays(21),
            'valid_until' => now()->addDays(9),
            'status' => 'accepted',
            'apply_discount' => false,
        ]);
        QuoteItem::create([
            'quote_id' => $quote3->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 30,
            'unit' => 'Stück',
            'net_price' => 24.00,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        QuoteItem::create([
            'quote_id' => $quote3->id,
            'article_id' => $a9->id,
            'description' => 'Entkoffeiniert Swiss Water 250g',
            'quantity' => 50,
            'unit' => 'Stück',
            'net_price' => 8.00,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);
        QuoteItem::create([
            'quote_id' => $quote3->id,
            'article_id' => $a5->id,
            'description' => 'Keramiktasse mit Logo',
            'quantity' => 40,
            'unit' => 'Stück',
            'net_price' => 10.00,
            'vat_rate' => 19.00,
            'sort_order' => 2,
        ]);

        // AN-4: Baeckerei Sonnenschein, mit Rabatt, versendet
        $quote4 = Quote::create([
            'quote_number' => 'AN-2026-0004',
            'customer_id' => $baeckerei->id,
            'quote_date' => now()->subDays(5),
            'valid_until' => now()->addDays(25),
            'status' => 'sent',
            'apply_discount' => true,
            'discount_percent' => $baeckerei->discount_percent,
        ]);
        QuoteItem::create([
            'quote_id' => $quote4->id,
            'article_id' => $a8->id,
            'description' => 'Crema Classico 1kg',
            'quantity' => 10,
            'unit' => 'Stück',
            'net_price' => 22.00,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        QuoteItem::create([
            'quote_id' => $quote4->id,
            'article_id' => $a7->id,
            'description' => 'Filterkaffee Colombia 500g',
            'quantity' => 20,
            'unit' => 'Stück',
            'net_price' => 14.50,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);

        // AN-5: Dr. Schulze, kein Rabatt, abgelehnt
        $quote5 = Quote::create([
            'quote_number' => 'AN-2026-0005',
            'customer_id' => $privatkunde->id,
            'quote_date' => now()->subDays(30),
            'valid_until' => now()->subDays(1),
            'status' => 'rejected',
            'apply_discount' => false,
        ]);
        QuoteItem::create([
            'quote_id' => $quote5->id,
            'article_id' => $a6->id,
            'description' => 'Handmuehle Comandante',
            'quantity' => 1,
            'unit' => 'Stück',
            'net_price' => 210.00,
            'vat_rate' => 19.00,
            'sort_order' => 0,
        ]);
        QuoteItem::create([
            'quote_id' => $quote5->id,
            'article_id' => $a15->id,
            'description' => 'Geschenkbox Kaffee-Entdecker',
            'quantity' => 2,
            'unit' => 'Stück',
            'net_price' => 29.90,
            'vat_rate' => 19.00,
            'sort_order' => 1,
        ]);

        // --- Rechnungen ---

        // RE-1: Mustermann, mit Rabatt, bezahlt
        $inv1 = Invoice::create([
            'invoice_number' => 'RE-2026-0001',
            'customer_id' => $mustermann->id,
            'invoice_date' => now()->subDays(28),
            'due_date' => now()->subDays(14),
            'status' => 'paid',
            'apply_discount' => true,
            'discount_percent' => 5.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'article_id' => $a1->id,
            'description' => 'Hausmischung 250g',
            'quantity' => 30,
            'unit' => 'Stück',
            'net_price' => 8.90,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'article_id' => $a3->id,
            'description' => 'Barista-Kurs Einsteiger',
            'quantity' => 2,
            'unit' => 'Pauschal',
            'net_price' => 89.00,
            'vat_rate' => 19.00,
            'sort_order' => 1,
        ]);
        Payment::create([
            'invoice_id' => $inv1->id,
            'payment_date' => now()->subDays(10),
            'amount' => 461.85,
            'payment_method' => 'bank_transfer',
        ]);

        // RE-2: Beispiel AG, mit Rabatt, versendet (offen)
        $inv2 = Invoice::create([
            'invoice_number' => 'RE-2026-0002',
            'customer_id' => $beispiel->id,
            'invoice_date' => now()->subDays(10),
            'due_date' => now()->addDays(20),
            'status' => 'sent',
            'apply_discount' => true,
            'discount_percent' => 10.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 20,
            'unit' => 'Stück',
            'net_price' => 28.00,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'article_id' => $a4->id,
            'description' => 'Kaffeebeutel 250g mit Ventil',
            'quantity' => 2,
            'unit' => 'Stück',
            'net_price' => 45.00,
            'vat_rate' => 19.00,
            'sort_order' => 1,
        ]);

        // RE-3: Cafe Wunderbar, individuelle Preise, teilbezahlt
        $inv3 = Invoice::create([
            'invoice_number' => 'RE-2026-0003',
            'customer_id' => $cafeWunderbar->id,
            'invoice_date' => now()->subDays(14),
            'due_date' => now()->subDays(7),
            'status' => 'partially_paid',
            'apply_discount' => false,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv3->id,
            'article_id' => $a1->id,
            'description' => 'Hausmischung 250g',
            'quantity' => 80,
            'unit' => 'Stück',
            'net_price' => 7.20,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv3->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 15,
            'unit' => 'Stück',
            'net_price' => 23.50,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);
        Payment::create([
            'invoice_id' => $inv3->id,
            'payment_date' => now()->subDays(5),
            'amount' => 500.00,
            'payment_method' => 'bank_transfer',
        ]);

        // RE-4: Hotel Krone, individuelle Preise, versendet
        $inv4 = Invoice::create([
            'invoice_number' => 'RE-2026-0004',
            'customer_id' => $hotelKrone->id,
            'invoice_date' => now()->subDays(7),
            'due_date' => now()->addDays(14),
            'status' => 'sent',
            'apply_discount' => false,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv4->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 25,
            'unit' => 'Stück',
            'net_price' => 24.00,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv4->id,
            'article_id' => $a8->id,
            'description' => 'Crema Classico 1kg',
            'quantity' => 10,
            'unit' => 'Stück',
            'net_price' => 19.00,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv4->id,
            'article_id' => $a5->id,
            'description' => 'Keramiktasse mit Logo',
            'quantity' => 30,
            'unit' => 'Stück',
            'net_price' => 10.00,
            'vat_rate' => 19.00,
            'sort_order' => 2,
        ]);

        // RE-5: Baeckerei Sonnenschein, mit Rabatt, Entwurf
        $inv5 = Invoice::create([
            'invoice_number' => 'RE-2026-0005',
            'customer_id' => $baeckerei->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'draft',
            'apply_discount' => true,
            'discount_percent' => 8.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv5->id,
            'article_id' => $a8->id,
            'description' => 'Crema Classico 1kg',
            'quantity' => 5,
            'unit' => 'Stück',
            'net_price' => 22.00,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv5->id,
            'article_id' => $a1->id,
            'description' => 'Hausmischung 250g',
            'quantity' => 20,
            'unit' => 'Stück',
            'net_price' => 8.90,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);

        // RE-6: Buero Peters, mit Rabatt, ueberfaellig
        $inv6 = Invoice::create([
            'invoice_number' => 'RE-2026-0006',
            'customer_id' => $bueroPeters->id,
            'invoice_date' => now()->subDays(30),
            'due_date' => now()->subDays(16),
            'status' => 'overdue',
            'apply_discount' => true,
            'discount_percent' => 3.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv6->id,
            'article_id' => $a10->id,
            'description' => 'Barista-Kurs Fortgeschrittene',
            'quantity' => 3,
            'unit' => 'Pauschal',
            'net_price' => 149.00,
            'vat_rate' => 19.00,
            'sort_order' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv6->id,
            'article_id' => $a12->id,
            'description' => 'Tamper 58mm Edelstahl',
            'quantity' => 3,
            'unit' => 'Stück',
            'net_price' => 35.00,
            'vat_rate' => 19.00,
            'sort_order' => 1,
        ]);

        // --- Lieferscheine ---

        $dn1 = DeliveryNote::create([
            'delivery_note_number' => 'LS-2026-0001',
            'customer_id' => $beispiel->id,
            'delivery_date' => now()->subDays(10),
            'status' => 'delivered',
        ]);
        DeliveryNoteItem::create([
            'delivery_note_id' => $dn1->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 20,
            'unit' => 'Stück',
            'net_price' => 28.00,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);

        $dn2 = DeliveryNote::create([
            'delivery_note_number' => 'LS-2026-0002',
            'customer_id' => $cafeWunderbar->id,
            'delivery_date' => now()->subDays(14),
            'status' => 'delivered',
        ]);
        DeliveryNoteItem::create([
            'delivery_note_id' => $dn2->id,
            'article_id' => $a1->id,
            'description' => 'Hausmischung 250g',
            'quantity' => 80,
            'unit' => 'Stück',
            'net_price' => 7.20,
            'vat_rate' => 7.00,
            'sort_order' => 0,
        ]);
        DeliveryNoteItem::create([
            'delivery_note_id' => $dn2->id,
            'article_id' => $a2->id,
            'description' => 'Espresso Intenso 1kg',
            'quantity' => 15,
            'unit' => 'Stück',
            'net_price' => 23.50,
            'vat_rate' => 7.00,
            'sort_order' => 1,
        ]);

        // Nummernkreis-Zaehler hochsetzen, damit neue Nummern korrekt vergeben werden
        NumberRange::where('type', 'invoice')->update(['counter_yearly' => 6, 'last_reset_year' => now()->year]);
        NumberRange::where('type', 'quote')->update(['counter_yearly' => 5, 'last_reset_year' => now()->year]);
        NumberRange::where('type', 'delivery_note')->update(['counter_yearly' => 2, 'last_reset_year' => now()->year]);
    }
}
