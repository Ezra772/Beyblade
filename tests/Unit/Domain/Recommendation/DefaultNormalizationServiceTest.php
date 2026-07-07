<?php

namespace Tests\Unit\Domain\Recommendation;

use App\Domain\Recommendation\Normalization\DefaultNormalizationRules;
use App\Domain\Recommendation\Normalization\DefaultNormalizationService;
use App\Domain\Recommendation\Normalization\NormalizationRule;
use App\Domain\Recommendation\Normalization\NormalizationStrategy;
use PHPUnit\Framework\TestCase;

class DefaultNormalizationServiceTest extends TestCase
{
    private DefaultNormalizationService $service;

    protected function setUp(): void
    {
        $this->service = new DefaultNormalizationService;
    }

    // ── MIN_MAX strategy ───────────────────────────────────────────────────

    public function test_min_max_midpoint_returns_50(): void
    {
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 0.0, 100.0),
        ];

        $result = $this->service->normalize('height', 50.0, $rules);

        $this->assertEqualsWithDelta(50.0, $result, 0.001);
    }

    public function test_min_max_at_theoretical_min_returns_0(): void
    {
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 50.0, 100.0),
        ];

        $result = $this->service->normalize('height', 50.0, $rules);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_min_max_at_theoretical_max_returns_100(): void
    {
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 50.0, 100.0),
        ];

        $result = $this->service->normalize('height', 100.0, $rules);

        $this->assertEqualsWithDelta(100.0, $result, 0.001);
    }

    public function test_min_max_value_above_max_is_clamped_to_100(): void
    {
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 0.0, 100.0),
        ];

        $result = $this->service->normalize('height', 999.0, $rules);

        $this->assertEqualsWithDelta(100.0, $result, 0.001);
    }

    public function test_min_max_value_below_min_is_clamped_to_0(): void
    {
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 50.0, 100.0),
        ];

        $result = $this->service->normalize('height', -10.0, $rules);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_invert_true_flips_result(): void
    {
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 0.0, 100.0, invert: true),
        ];

        // At 75 of 0-100 range without invert = 75; with invert = 25
        $result = $this->service->normalize('height', 75.0, $rules);

        $this->assertEqualsWithDelta(25.0, $result, 0.001);
    }

    public function test_min_max_zero_range_returns_0(): void
    {
        // Guard against division by zero when theoreticalMin == theoreticalMax.
        $rules = [
            new NormalizationRule('height', NormalizationStrategy::MIN_MAX, 50.0, 50.0),
        ];

        $result = $this->service->normalize('height', 50.0, $rules);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    // ── NONE strategy ─────────────────────────────────────────────────────

    public function test_none_strategy_returns_raw_value(): void
    {
        $rules = [
            new NormalizationRule('attack', NormalizationStrategy::NONE),
        ];

        $result = $this->service->normalize('attack', 68.0, $rules);

        $this->assertEqualsWithDelta(68.0, $result, 0.001);
    }

    // ── No-rule fallback ──────────────────────────────────────────────────

    public function test_no_matching_rule_clamps_to_0_100(): void
    {
        $result = $this->service->normalize('unknown_attribute', 150.0, []);

        $this->assertEqualsWithDelta(100.0, $result, 0.001);
    }

    public function test_no_matching_rule_below_0_clamps_to_0(): void
    {
        $result = $this->service->normalize('unknown_attribute', -50.0, []);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_no_matching_rule_in_range_passes_through(): void
    {
        $result = $this->service->normalize('unknown_attribute', 42.0, []);

        $this->assertEqualsWithDelta(42.0, $result, 0.001);
    }

    // ── DefaultNormalizationRules integration ─────────────────────────────

    public function test_default_rules_ratchet_height_midpoint(): void
    {
        // theoreticalMin=50, theoreticalMax=100 → midpoint=75 → normalizes to 50
        $result = $this->service->normalize('ratchet_height', 75.0, DefaultNormalizationRules::get());

        $this->assertEqualsWithDelta(50.0, $result, 0.001);
    }

    public function test_default_rules_blade_weight_midpoint(): void
    {
        // theoreticalMin=15, theoreticalMax=50 → midpoint=32.5 → normalizes to 50
        $result = $this->service->normalize('blade_weight', 32.5, DefaultNormalizationRules::get());

        $this->assertEqualsWithDelta(50.0, $result, 0.001);
    }
}
