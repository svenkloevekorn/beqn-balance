<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Kunden
        Customer::create([
            'name' => 'Mustermann GmbH',
            'street' => 'Musterstraße 1',
            'zip' => '10115',
            'city' => 'Berlin',
            'country' => 'DE',
            'email' => 'info@mustermann.de',
            'phone' => '030 12345678',
            'vat_id' => 'DE123456789',
            'payment_term_days' => 14,
        ]);

        Customer::create([
            'name' => 'Beispiel AG',
            'street' => 'Beispielweg 42',
            'zip' => '80331',
            'city' => 'München',
            'country' => 'DE',
            'email' => 'buchhaltung@beispiel-ag.de',
            'phone' => '089 98765432',
            'vat_id' => 'DE987654321',
            'payment_term_days' => 30,
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
    }
}
