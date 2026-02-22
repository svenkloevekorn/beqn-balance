<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\ContactPerson;
use App\Models\Customer;
use App\Models\IncomingInvoice;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NumberRange;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuhaSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    // --- Model Tests ---

    public function test_customer_can_be_created(): void
    {
        $customer = Customer::create([
            'name' => 'Test Kunde',
            'street' => 'Teststr. 1',
            'zip' => '12345',
            'city' => 'Teststadt',
            'country' => 'DE',
            'email' => 'test@kunde.de',
            'payment_term_days' => 14,
        ]);

        $this->assertDatabaseHas('customers', ['name' => 'Test Kunde']);
        $this->assertEquals('Teststr. 1, 12345 Teststadt, DE', $customer->full_address);
    }

    public function test_supplier_can_be_created(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Lieferant',
            'city' => 'Hamburg',
            'country' => 'DE',
        ]);

        $this->assertDatabaseHas('suppliers', ['name' => 'Test Lieferant']);
        $this->assertNotEmpty($supplier->full_address);
    }

    public function test_article_can_be_created(): void
    {
        $article = Article::create([
            'name' => 'Testartikel',
            'unit' => 'Stück',
            'net_price' => 50.00,
            'vat_rate' => 19.00,
        ]);

        $this->assertDatabaseHas('articles', ['name' => 'Testartikel']);
        $this->assertEquals('50.00', $article->net_price);
    }

    public function test_invoice_with_items_calculates_totals(): void
    {
        $customer = Customer::first();

        $invoice = Invoice::create([
            'invoice_number' => 'RE-2026-9999',
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'draft',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Webentwicklung',
            'quantity' => 10,
            'unit' => 'Stunde',
            'net_price' => 100.00,
            'vat_rate' => 19.00,
            'sort_order' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Hosting',
            'quantity' => 1,
            'unit' => 'Stück',
            'net_price' => 50.00,
            'vat_rate' => 19.00,
            'sort_order' => 1,
        ]);

        $invoice->load('items');

        // 10 * 100 + 1 * 50 = 1050 netto
        $this->assertEquals(1050.00, $invoice->net_total);
        // 1050 * 0.19 = 199.50
        $this->assertEquals(199.50, $invoice->vat_total);
        // 1050 + 199.50 = 1249.50
        $this->assertEquals(1249.50, $invoice->gross_total);
    }

    public function test_invoice_item_calculates_line_totals(): void
    {
        $customer = Customer::first();
        $invoice = Invoice::create([
            'invoice_number' => 'RE-2026-8888',
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'draft',
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Beratung',
            'quantity' => 5,
            'net_price' => 120.00,
            'vat_rate' => 19.00,
            'sort_order' => 0,
        ]);

        $this->assertEquals(600.00, $item->line_total);   // 5 * 120
        $this->assertEquals(114.00, $item->line_vat);      // 600 * 0.19
        $this->assertEquals(714.00, $item->line_gross);    // 600 + 114
    }

    public function test_incoming_invoice_can_be_created(): void
    {
        $supplier = Supplier::first();

        $invoice = IncomingInvoice::create([
            'supplier_id' => $supplier->id,
            'external_invoice_number' => 'EXT-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'net_amount' => 100.00,
            'vat_amount' => 19.00,
            'gross_amount' => 119.00,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('incoming_invoices', ['external_invoice_number' => 'EXT-001']);
        $this->assertEquals('100.00', $invoice->net_amount);
    }

    public function test_customer_has_invoices_relationship(): void
    {
        $customer = Customer::first();

        Invoice::create([
            'invoice_number' => 'RE-2026-7777',
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'draft',
        ]);

        $this->assertCount(1, $customer->invoices);
    }

    public function test_supplier_has_incoming_invoices_relationship(): void
    {
        $supplier = Supplier::first();

        IncomingInvoice::create([
            'supplier_id' => $supplier->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'net_amount' => 50,
            'vat_amount' => 9.50,
            'gross_amount' => 59.50,
            'status' => 'open',
        ]);

        $this->assertCount(1, $supplier->incomingInvoices);
    }

    // --- CompanySetting Tests ---

    public function test_company_setting_singleton(): void
    {
        $s1 = CompanySetting::instance();
        $s2 = CompanySetting::instance();

        $this->assertEquals($s1->id, $s2->id);
        $this->assertEquals(1, CompanySetting::count());
    }

    public function test_company_setting_can_be_updated(): void
    {
        $settings = CompanySetting::instance();
        $settings->update(['company_name' => 'Meine Firma GmbH', 'iban' => 'DE89370400440532013000']);

        $settings->refresh();
        $this->assertEquals('Meine Firma GmbH', $settings->company_name);
        $this->assertEquals('DE89370400440532013000', $settings->iban);
    }

    // --- NumberRange Tests ---

    public function test_number_ranges_are_seeded(): void
    {
        $this->assertEquals(5, NumberRange::count());
        $this->assertDatabaseHas('number_ranges', ['type' => 'invoice', 'format' => 'RE-{jjjj}-{jz,4}']);
        $this->assertDatabaseHas('number_ranges', ['type' => 'quote', 'format' => 'AN-{jjjj}-{jz,4}']);
        $this->assertDatabaseHas('number_ranges', ['type' => 'delivery_note', 'format' => 'LS-{jjjj}-{jz,4}']);
        $this->assertDatabaseHas('number_ranges', ['type' => 'customer', 'format' => 'KD-{z,4}']);
        $this->assertDatabaseHas('number_ranges', ['type' => 'article', 'format' => 'ART-{z,4}']);
    }

    public function test_number_range_generates_with_year(): void
    {
        $year = now()->year;
        $number = NumberRange::generateNext('invoice');

        $this->assertStringStartsWith("RE-{$year}-", $number);
        $this->assertEquals("RE-{$year}-0001", $number);
    }

    public function test_number_range_increments(): void
    {
        $year = now()->year;

        $first = NumberRange::generateNext('invoice');
        $second = NumberRange::generateNext('invoice');

        $this->assertEquals("RE-{$year}-0001", $first);
        $this->assertEquals("RE-{$year}-0002", $second);
    }

    public function test_number_range_without_year(): void
    {
        $number = NumberRange::generateNext('customer');
        $this->assertEquals('KD-0001', $number);

        $number2 = NumberRange::generateNext('customer');
        $this->assertEquals('KD-0002', $number2);
    }

    public function test_number_range_preview_does_not_increment(): void
    {
        $range = NumberRange::where('type', 'quote')->first();
        $preview1 = $range->previewNext();
        $preview2 = $range->previewNext();

        $this->assertEquals($preview1, $preview2);

        // Zaehler darf sich nicht veraendert haben
        $range->refresh();
        $this->assertEquals(0, $range->counter_yearly);
    }

    public function test_number_range_yearly_reset(): void
    {
        $range = NumberRange::where('type', 'invoice')->first();

        // Simuliere: letztes Jahr wurde gezaehlt
        $range->update([
            'counter_yearly' => 50,
            'last_reset_year' => now()->year - 1,
        ]);

        $number = NumberRange::generateNext('invoice');
        $this->assertStringEndsWith('-0001', $number);
    }

    public function test_invoice_generate_number_uses_number_range(): void
    {
        $year = now()->year;
        $number = Invoice::generateInvoiceNumber();

        $this->assertStringStartsWith("RE-{$year}-", $number);
    }

    // --- Filament Page Tests ---

    public function test_login_page_is_accessible(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_customers_page_requires_auth(): void
    {
        $this->get('/admin/customers')->assertRedirect('/admin/login');
    }

    public function test_dashboard_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_customers_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/customers')->assertOk();
    }

    public function test_suppliers_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/suppliers')->assertOk();
    }

    public function test_articles_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/articles')->assertOk();
    }

    public function test_invoices_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/invoices')->assertOk();
    }

    public function test_incoming_invoices_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/incoming-invoices')->assertOk();
    }

    public function test_company_settings_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/company-settings')->assertOk();
    }

    // --- Seeder Tests ---

    public function test_seeder_creates_customers(): void
    {
        $this->assertGreaterThanOrEqual(3, Customer::count());
    }

    public function test_seeder_creates_suppliers(): void
    {
        $this->assertGreaterThanOrEqual(2, Supplier::count());
    }

    public function test_seeder_creates_articles(): void
    {
        $this->assertGreaterThanOrEqual(4, Article::count());
    }

    // --- Kategorie Tests ---

    public function test_category_can_be_created(): void
    {
        $category = Category::create([
            'name' => 'Testkategorie',
            'description' => 'Eine Testkategorie',
            'color' => '#ff0000',
        ]);

        $this->assertDatabaseHas('categories', ['name' => 'Testkategorie']);
        $this->assertEquals('#ff0000', $category->color);
    }

    public function test_article_can_have_categories(): void
    {
        $category1 = Category::create(['name' => 'Kat A']);
        $category2 = Category::create(['name' => 'Kat B']);

        $article = Article::create([
            'name' => 'Multi-Kat Artikel',
            'unit' => 'Stück',
            'net_price' => 10.00,
            'vat_rate' => 19.00,
        ]);

        $article->categories()->attach([$category1->id, $category2->id]);

        $this->assertCount(2, $article->categories);
        $this->assertTrue($article->categories->contains($category1));
        $this->assertTrue($article->categories->contains($category2));
    }

    public function test_category_has_articles(): void
    {
        $category = Category::create(['name' => 'Testkat']);

        $article1 = Article::create([
            'name' => 'Artikel A',
            'unit' => 'Stück',
            'net_price' => 5.00,
            'vat_rate' => 19.00,
        ]);

        $article2 = Article::create([
            'name' => 'Artikel B',
            'unit' => 'Stück',
            'net_price' => 15.00,
            'vat_rate' => 19.00,
        ]);

        $category->articles()->attach([$article1->id, $article2->id]);

        $this->assertCount(2, $category->articles);
    }

    public function test_seeder_creates_categories(): void
    {
        $this->assertGreaterThanOrEqual(6, Category::count());
        $this->assertDatabaseHas('categories', ['name' => 'Kaffee']);
        $this->assertDatabaseHas('categories', ['name' => 'Equipment']);
        $this->assertDatabaseHas('categories', ['name' => 'Dienstleistung']);
    }

    public function test_seeded_articles_have_categories(): void
    {
        $articles = Article::has('categories')->get();
        $this->assertGreaterThanOrEqual(4, $articles->count());
    }

    public function test_categories_page_accessible_when_logged_in(): void
    {
        $user = User::first();
        $this->actingAs($user)->get('/admin/categories')->assertOk();
    }

    public function test_deleting_category_detaches_articles(): void
    {
        $category = Category::create(['name' => 'Temp-Kat']);
        $article = Article::create([
            'name' => 'Temp-Artikel',
            'unit' => 'Stück',
            'net_price' => 1.00,
            'vat_rate' => 19.00,
        ]);
        $article->categories()->attach($category->id);

        $category->delete();

        $this->assertDatabaseMissing('article_category', ['category_id' => $category->id]);
        $this->assertDatabaseHas('articles', ['name' => 'Temp-Artikel']);
    }

    // --- Ansprechpartner Tests ---

    public function test_customer_can_have_contact_persons(): void
    {
        $customer = Customer::first();

        $contact = ContactPerson::create([
            'customer_id' => $customer->id,
            'name' => 'Test Kontakt',
            'position' => 'Einkauf',
            'email' => 'kontakt@test.de',
            'phone' => '0123 456789',
        ]);

        $this->assertDatabaseHas('contact_persons', ['name' => 'Test Kontakt']);
        $this->assertTrue($customer->contactPersons->contains($contact));
        $this->assertEquals($customer->id, $contact->customer->id);
    }

    public function test_deleting_customer_deletes_contact_persons(): void
    {
        $customer = Customer::create([
            'name' => 'Temp Kunde',
            'city' => 'Berlin',
            'country' => 'DE',
            'payment_term_days' => 14,
        ]);

        ContactPerson::create([
            'customer_id' => $customer->id,
            'name' => 'Temp Kontakt',
        ]);

        $customerId = $customer->id;
        $customer->delete();

        $this->assertDatabaseMissing('contact_persons', ['customer_id' => $customerId]);
    }

    public function test_customer_has_discount_percent(): void
    {
        $customer = Customer::create([
            'name' => 'Rabatt Kunde',
            'city' => 'Berlin',
            'country' => 'DE',
            'payment_term_days' => 14,
            'discount_percent' => 7.50,
        ]);

        $this->assertEquals('7.50', $customer->discount_percent);
    }

    public function test_customer_has_notes(): void
    {
        $customer = Customer::create([
            'name' => 'Notiz Kunde',
            'city' => 'Berlin',
            'country' => 'DE',
            'payment_term_days' => 14,
            'notes' => 'Wichtiger Hinweis',
        ]);

        $this->assertEquals('Wichtiger Hinweis', $customer->notes);
    }

    public function test_seeder_creates_contact_persons(): void
    {
        $this->assertGreaterThanOrEqual(3, ContactPerson::count());
        $this->assertDatabaseHas('contact_persons', ['name' => 'Max Mustermann']);
    }

    public function test_seeder_creates_customer_with_discount(): void
    {
        $this->assertDatabaseHas('customers', [
            'name' => 'Mustermann GmbH',
            'discount_percent' => 5.00,
        ]);
    }
}
