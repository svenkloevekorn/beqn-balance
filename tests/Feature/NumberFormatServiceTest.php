<?php

namespace Tests\Feature;

use App\Services\NumberFormatService;
use Carbon\Carbon;
use Tests\TestCase;

class NumberFormatServiceTest extends TestCase
{
    protected NumberFormatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NumberFormatService::class);
    }

    // --- Validierung ---

    public function test_valid_format_returns_no_errors(): void
    {
        $this->assertEmpty($this->service->validate('RE-{jjjj}-{jz,4}'));
        $this->assertEmpty($this->service->validate('AN-{jj}{mm}{tt}{tz,3}'));
        $this->assertEmpty($this->service->validate('KD-{z,4}'));
        $this->assertEmpty($this->service->validate('{z}'));
        $this->assertEmpty($this->service->validate('A-{datum}-{mz,2}'));
    }

    public function test_empty_format_returns_error(): void
    {
        $errors = $this->service->validate('');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('leer', $errors[0]);
    }

    public function test_format_without_counter_returns_error(): void
    {
        $errors = $this->service->validate('RE-{jjjj}');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Zaehler', $errors[0]);
    }

    public function test_invalid_placeholder_returns_error(): void
    {
        $errors = $this->service->validate('RE-{xyz}-{z}');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('xyz', $errors[0]);
    }

    public function test_special_characters_outside_placeholders_returns_error(): void
    {
        $errors = $this->service->validate('RÜ-{z}');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('A-Z', $errors[0]);
    }

    public function test_umlauts_are_rejected(): void
    {
        $this->assertNotEmpty($this->service->validate('Rä-{z}'));
        $this->assertNotEmpty($this->service->validate('Rö-{z}'));
        $this->assertNotEmpty($this->service->validate('Rü-{z}'));
        $this->assertNotEmpty($this->service->validate('Rß-{z}'));
    }

    public function test_allowed_characters_pass(): void
    {
        $this->assertEmpty($this->service->validate('ABC-abc-123-{z}'));
        $this->assertEmpty($this->service->validate('RE-{z}'));
        $this->assertEmpty($this->service->validate('a-{z}'));
    }

    public function test_multiple_errors_returned(): void
    {
        $errors = $this->service->validate('RÜ-{xyz}');
        $this->assertGreaterThanOrEqual(2, count($errors));
    }

    // --- Datum-Platzhalter ---

    public function test_jjjj_placeholder(): void
    {
        $date = Carbon::parse('2026-02-22');
        $result = $this->service->generate('{jjjj}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('2026-1', $result);
    }

    public function test_jj_placeholder(): void
    {
        $date = Carbon::parse('2026-02-22');
        $result = $this->service->generate('{jj}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('26-1', $result);
    }

    public function test_mm_placeholder(): void
    {
        $date = Carbon::parse('2026-02-22');
        $result = $this->service->generate('{mm}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('02-1', $result);
    }

    public function test_m_placeholder(): void
    {
        $date = Carbon::parse('2026-02-05');
        $result = $this->service->generate('{m}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('2-1', $result);
    }

    public function test_tt_placeholder(): void
    {
        $date = Carbon::parse('2026-02-05');
        $result = $this->service->generate('{tt}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('05-1', $result);
    }

    public function test_t_placeholder(): void
    {
        $date = Carbon::parse('2026-02-05');
        $result = $this->service->generate('{t}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('5-1', $result);
    }

    public function test_datum_placeholder(): void
    {
        $date = Carbon::parse('2026-02-22');
        $result = $this->service->generate('{datum}-{z}', ['counter_global' => 1], $date);
        $this->assertEquals('20260222-1', $result);
    }

    // --- Zaehler-Platzhalter ---

    public function test_z_counter(): void
    {
        $result = $this->service->generate('NR-{z}', ['counter_global' => 42]);
        $this->assertEquals('NR-42', $result);
    }

    public function test_jz_counter(): void
    {
        $result = $this->service->generate('NR-{jz}', ['counter_yearly' => 7]);
        $this->assertEquals('NR-7', $result);
    }

    public function test_mz_counter(): void
    {
        $result = $this->service->generate('NR-{mz}', ['counter_monthly' => 3]);
        $this->assertEquals('NR-3', $result);
    }

    public function test_tz_counter(): void
    {
        $result = $this->service->generate('NR-{tz}', ['counter_daily' => 15]);
        $this->assertEquals('NR-15', $result);
    }

    // --- Mindestlaenge / fuehrende Nullen ---

    public function test_counter_with_min_digits(): void
    {
        $result = $this->service->generate('RE-{z,4}', ['counter_global' => 1]);
        $this->assertEquals('RE-0001', $result);
    }

    public function test_counter_with_min_digits_large_number(): void
    {
        $result = $this->service->generate('RE-{z,3}', ['counter_global' => 2100]);
        $this->assertEquals('RE-2100', $result);
    }

    public function test_counter_with_5_digits(): void
    {
        $result = $this->service->generate('AN-{z,5}', ['counter_global' => 103]);
        $this->assertEquals('AN-00103', $result);
    }

    public function test_yearly_counter_with_digits(): void
    {
        $result = $this->service->generate('RE-{jjjj}-{jz,4}', ['counter_yearly' => 1], Carbon::parse('2026-01-01'));
        $this->assertEquals('RE-2026-0001', $result);
    }

    // --- Kombinierte Formate (Beispiele aus Anforderung) ---

    public function test_example_an_with_date_and_counter(): void
    {
        $date = Carbon::parse('2026-02-22');
        $result = $this->service->generate('AN-{jj}{mm}{tt}{z,3}', ['counter_global' => 1], $date);
        $this->assertEquals('AN-260222001', $result);
    }

    public function test_example_k_with_counter(): void
    {
        $result = $this->service->generate('K-{z,3}', ['counter_global' => 2100]);
        $this->assertEquals('K-2100', $result);
    }

    public function test_example_an_with_5_digit_counter(): void
    {
        $result = $this->service->generate('AN-{z,5}', ['counter_global' => 103]);
        $this->assertEquals('AN-00103', $result);
    }

    // --- Required Counters ---

    public function test_get_required_counters(): void
    {
        $this->assertEquals(['jz'], $this->service->getRequiredCounters('RE-{jjjj}-{jz,4}'));
        $this->assertEquals(['z'], $this->service->getRequiredCounters('KD-{z,4}'));
        $this->assertEquals(['tz'], $this->service->getRequiredCounters('AN-{jj}{mm}{tt}{tz,3}'));
    }

    public function test_format_with_multiple_counters(): void
    {
        $counters = $this->service->getRequiredCounters('{z}-{jz}');
        $this->assertContains('z', $counters);
        $this->assertContains('jz', $counters);
    }
}
