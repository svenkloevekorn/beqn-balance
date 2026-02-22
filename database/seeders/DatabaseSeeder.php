<?php

namespace Database\Seeders;

use App\Models\Article;
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

        // Artikel
        Article::create([
            'name' => 'Webentwicklung',
            'description' => 'Frontend- und Backend-Entwicklung pro Stunde',
            'unit' => 'Stunde',
            'net_price' => 95.00,
            'vat_rate' => 19.00,
        ]);

        Article::create([
            'name' => 'Beratung',
            'description' => 'IT-Beratung und Projektmanagement',
            'unit' => 'Stunde',
            'net_price' => 120.00,
            'vat_rate' => 19.00,
        ]);

        Article::create([
            'name' => 'Hosting Paket Standard',
            'description' => 'Webhosting inkl. Domain und SSL',
            'unit' => 'Stück',
            'net_price' => 29.90,
            'vat_rate' => 19.00,
        ]);

        Article::create([
            'name' => 'Logo-Design',
            'description' => 'Erstellung eines individuellen Logos',
            'unit' => 'Pauschal',
            'net_price' => 450.00,
            'vat_rate' => 19.00,
        ]);
    }
}
