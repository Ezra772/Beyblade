<?php

namespace Tests\Unit\Domain\Recommendation;

use App\Domain\Recommendation\Criterion;
use App\Domain\Recommendation\Normalization\DefaultNormalizationRules;
use App\Domain\Recommendation\Normalization\DefaultNormalizationService;
use App\Domain\Recommendation\Performance\DefaultContributionRules;
use App\Domain\Recommendation\Performance\DefaultPerformanceEngine;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DefaultPerformanceEngineTest extends TestCase
{
    private DefaultPerformanceEngine $engine;

    /** @var array<string, float> */
    private array $wizardRodBlade;

    /** @var array<string, float> */
    private array $ratchet570;

    /** @var array<string, float> */
    private array $ballBit;

    protected function setUp(): void
    {
        $this->engine = new DefaultPerformanceEngine(new DefaultNormalizationService);

        // Reference data from AI_README.md Section 6.
        $this->wizardRodBlade = [
            'weight' => 35.2,
            'attack' => 68.0,
            'defense' => 82.0,
            'stamina' => 98.0,
            'smash' => 55.0,
            'upper' => 40.0,
            'recoil' => 25.0,
            'burst_resistance' => 86.0,
        ];

        $this->ratchet570 = [
            'height' => 70.0,       // raw mm — will be passed as ratchet_height to ContributionRule
            'weight' => 7.1,
            'stability' => 90.0,
            'burst_resistance' => 82.0,
        ];

        $this->ballBit = [
            'speed' => 35.0,
            'stamina' => 100.0,
            'grip' => 40.0,
            'control' => 90.0,
            'movement' => 30.0,
            'dash' => 15.0,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DoD 7.2 — All 8 criterion keys present
    // ─────────────────────────────────────────────────────────────────────────

    public function test_all_8_criterion_keys_present_in_profile(): void
    {
        $profile = $this->computeDefaultProfile();

        foreach (Criterion::cases() as $criterion) {
            $this->assertArrayHasKey(
                $criterion->value,
                $profile,
                "Missing criterion key: {$criterion->value}",
            );
        }

        $this->assertCount(8, $profile);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DoD 7.2 — WEIGHT is additive: blade.weight + ratchet.weight = 35.2 + 7.1 = 42.3
    // ─────────────────────────────────────────────────────────────────────────

    public function test_weight_criterion_is_additive_sum_of_blade_and_ratchet(): void
    {
        $profile = $this->computeDefaultProfile();

        // 35.2 (blade) + 7.1 (ratchet) = 42.3
        $this->assertEqualsWithDelta(42.3, $profile[Criterion::WEIGHT->value], 0.001);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DoD 7.2 — All performance criteria non-negative and within plausible range
    // ─────────────────────────────────────────────────────────────────────────

    public function test_all_performance_criteria_are_non_negative(): void
    {
        $profile = $this->computeDefaultProfile();

        foreach ($profile as $key => $value) {
            $this->assertGreaterThanOrEqual(0.0, $value, "Criterion {$key} is negative: {$value}");
        }
    }

    public function test_pure_performance_criteria_do_not_exceed_110(): void
    {
        $profile = $this->computeDefaultProfile();

        // Non-additive criteria are weighted sums of 0-100 values — should not exceed ~110.
        $nonAdditiveKeys = array_filter(
            array_keys($profile),
            fn ($key) => $key !== Criterion::WEIGHT->value,
        );

        foreach ($nonAdditiveKeys as $key) {
            $this->assertLessThanOrEqual(110.0, $profile[$key], "Criterion {$key} unexpectedly high: {$profile[$key]}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Spot-check a specific criterion with known inputs
    // ─────────────────────────────────────────────────────────────────────────

    public function test_control_criterion_uses_ratchet_stability_and_bit_control(): void
    {
        // CONTROL = 0.20 × ratchet.stability + 0.80 × bit.control
        //         = 0.20 × 90  + 0.80 × 90
        //         = 18 + 72 = 90
        $profile = $this->computeDefaultProfile();

        $expectedControl = 0.20 * 90.0 + 0.80 * 90.0;
        $this->assertEqualsWithDelta($expectedControl, $profile[Criterion::CONTROL->value], 0.001);
    }

    public function test_defense_criterion_is_computed_correctly(): void
    {
        // DEFENSE = 0.55 × blade.defense + 0.35 × ratchet.stability + 0.10 × bit.grip
        //         = 0.55 × 82 + 0.35 × 90 + 0.10 × 40
        //         = 45.1 + 31.5 + 4.0 = 80.6
        $profile = $this->computeDefaultProfile();

        $expected = 0.55 * 82.0 + 0.35 * 90.0 + 0.10 * 40.0;
        $this->assertEqualsWithDelta($expected, $profile[Criterion::DEFENSE->value], 0.001);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Error handling
    // ─────────────────────────────────────────────────────────────────────────

    public function test_missing_attribute_throws_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/attack/');

        // Remove 'attack' from blade attributes to trigger the exception.
        $incompleteBladeAttributes = array_diff_key($this->wizardRodBlade, ['attack' => null]);

        $this->engine->computeProfile(
            $incompleteBladeAttributes,
            $this->ratchet570,
            $this->ballBit,
            DefaultContributionRules::get(),
            DefaultNormalizationRules::get(),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Verify no Eloquent imports in Domain namespace (static check via reflection)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_default_performance_engine_has_no_eloquent_dependency(): void
    {
        // Verify via reflection that no Illuminate or App\Models namespace is imported.
        $reflection = new \ReflectionClass(DefaultPerformanceEngine::class);
        $fileName = $reflection->getFileName();

        $contents = file_get_contents($fileName);

        $this->assertStringNotContainsString(
            'use Illuminate\\',
            $contents,
            'DefaultPerformanceEngine must not import any Illuminate namespaces.',
        );
        $this->assertStringNotContainsString(
            'use App\\Models\\',
            $contents,
            'DefaultPerformanceEngine must not import any App\\Models namespaces.',
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array<string, float>
     */
    private function computeDefaultProfile(): array
    {
        // The DefaultContributionRules use two different ratchet keys:
        //   'ratchet_height' / 'ratchet_weight' — for sources requiring normalization
        //   'weight'                             — for the WEIGHT additive rule (raw grams)
        // A real UseCase would expose both; we mirror that here.
        $ratchetMapped = [
            'ratchet_height' => $this->ratchet570['height'],
            'ratchet_weight' => $this->ratchet570['weight'],
            'weight' => $this->ratchet570['weight'],       // raw grams for WEIGHT additive rule
            'stability' => $this->ratchet570['stability'],
            'burst_resistance' => $this->ratchet570['burst_resistance'],
        ];

        $bladeMapped = array_merge($this->wizardRodBlade, [
            'blade_weight' => $this->wizardRodBlade['weight'],
        ]);

        return $this->engine->computeProfile(
            $bladeMapped,
            $ratchetMapped,
            $this->ballBit,
            DefaultContributionRules::get(),
            DefaultNormalizationRules::get(),
        );
    }
}
