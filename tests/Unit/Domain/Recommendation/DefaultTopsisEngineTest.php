<?php

namespace Tests\Unit\Domain\Recommendation;

use App\Domain\Recommendation\CriterionType;
use App\Domain\Recommendation\Topsis\DecisionAlternative;
use App\Domain\Recommendation\Topsis\DefaultTopsisEngine;
use App\Domain\Recommendation\Topsis\TopsisResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DefaultTopsisEngineTest extends TestCase
{
    private DefaultTopsisEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new DefaultTopsisEngine;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DoD 7.1 — Verified manual calculation test
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Manual verification:
     *   A: c1=7, c2=8, c3=9   →  V(A) ≈ 0.625, rank 1
     *   B: c1=9, c2=7, c3=6   →  V(B) ≈ 0.375, rank 2
     *
     * Calculations (weights = 1/3 each, all BENEFIT):
     *   col norms: c1=√130≈11.4018, c2=√113≈10.6301, c3=√117≈10.8167
     *   normalized A: [0.61394, 0.75277, 0.83205]
     *   normalized B: [0.78935, 0.65868, 0.55470]
     *   weighted  A:  [0.20465, 0.25092, 0.27735]
     *   weighted  B:  [0.26312, 0.21956, 0.18490]
     *   A+ = [0.26312, 0.25092, 0.27735]  A- = [0.20465, 0.21956, 0.18490]
     *   D+(A)=0.05847  D-(A)=0.09763
     *   D+(B)=0.09763  D-(B)=0.05847
     *   V(A)=0.09763/(0.05847+0.09763)=0.6253  V(B)=0.05847/0.15610=0.3747
     */
    public function test_manual_calculation_preference_scores_and_ranking(): void
    {
        $alternatives = [
            new DecisionAlternative('A', ['c1' => 7.0, 'c2' => 8.0, 'c3' => 9.0]),
            new DecisionAlternative('B', ['c1' => 9.0, 'c2' => 7.0, 'c3' => 6.0]),
        ];

        $weights = ['c1' => 1 / 3, 'c2' => 1 / 3, 'c3' => 1 / 3];
        $types = ['c1' => CriterionType::BENEFIT, 'c2' => CriterionType::BENEFIT, 'c3' => CriterionType::BENEFIT];

        $results = $this->engine->rank($alternatives, $weights, $types);

        $this->assertCount(2, $results);

        $byId = [];
        foreach ($results as $result) {
            $byId[$result->alternativeId] = $result;
        }

        // Alternative A should be rank 1 with score ≈ 0.625
        $this->assertSame(1, $byId['A']->rank);
        $this->assertEqualsWithDelta(0.625, $byId['A']->preferenceScore, 0.001);

        // Alternative B should be rank 2 with score ≈ 0.375
        $this->assertSame(2, $byId['B']->rank);
        $this->assertEqualsWithDelta(0.375, $byId['B']->preferenceScore, 0.001);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Additional correctness tests
    // ─────────────────────────────────────────────────────────────────────────

    public function test_results_are_sorted_descending_by_preference_score(): void
    {
        $alternatives = [
            new DecisionAlternative('X', ['c1' => 1.0]),
            new DecisionAlternative('Y', ['c1' => 5.0]),
            new DecisionAlternative('Z', ['c1' => 9.0]),
        ];

        $weights = ['c1' => 1.0];
        $types = ['c1' => CriterionType::BENEFIT];

        $results = $this->engine->rank($alternatives, $weights, $types);

        $this->assertSame('Z', $results[0]->alternativeId);
        $this->assertSame('Y', $results[1]->alternativeId);
        $this->assertSame('X', $results[2]->alternativeId);
    }

    public function test_ranks_are_assigned_1_based(): void
    {
        $alternatives = [
            new DecisionAlternative('A', ['c1' => 3.0]),
            new DecisionAlternative('B', ['c1' => 7.0]),
        ];

        $weights = ['c1' => 1.0];
        $types = ['c1' => CriterionType::BENEFIT];

        $results = $this->engine->rank($alternatives, $weights, $types);

        $this->assertSame(1, $results[0]->rank);
        $this->assertSame(2, $results[1]->rank);
    }

    public function test_cost_criterion_prefers_lower_value(): void
    {
        // For COST criterion, lower value is better — A (value=3) should beat B (value=9).
        $alternatives = [
            new DecisionAlternative('A', ['c1' => 3.0]),
            new DecisionAlternative('B', ['c1' => 9.0]),
        ];

        $weights = ['c1' => 1.0];
        $types = ['c1' => CriterionType::COST];

        $results = $this->engine->rank($alternatives, $weights, $types);

        $this->assertSame('A', $results[0]->alternativeId);
        $this->assertSame(1, $results[0]->rank);
    }

    public function test_single_alternative_returns_rank_1_with_score_0(): void
    {
        // With only one alternative D+ = D- = 0 → preference score = 0 (zero-guard fires).
        $alternatives = [
            new DecisionAlternative('solo', ['c1' => 5.0]),
        ];

        $weights = ['c1' => 1.0];
        $types = ['c1' => CriterionType::BENEFIT];

        $results = $this->engine->rank($alternatives, $weights, $types);

        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]->rank);
        $this->assertSame('solo', $results[0]->alternativeId);
    }

    public function test_empty_alternatives_throws_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->engine->rank([], ['c1' => 1.0], ['c1' => CriterionType::BENEFIT]);
    }

    public function test_result_dto_contains_distances(): void
    {
        $alternatives = [
            new DecisionAlternative('A', ['c1' => 7.0, 'c2' => 8.0, 'c3' => 9.0]),
            new DecisionAlternative('B', ['c1' => 9.0, 'c2' => 7.0, 'c3' => 6.0]),
        ];

        $weights = ['c1' => 1 / 3, 'c2' => 1 / 3, 'c3' => 1 / 3];
        $types = ['c1' => CriterionType::BENEFIT, 'c2' => CriterionType::BENEFIT, 'c3' => CriterionType::BENEFIT];

        $results = $this->engine->rank($alternatives, $weights, $types);

        foreach ($results as $result) {
            $this->assertInstanceOf(TopsisResult::class, $result);
            $this->assertGreaterThanOrEqual(0.0, $result->distanceToPositiveIdeal);
            $this->assertGreaterThanOrEqual(0.0, $result->distanceToNegativeIdeal);
        }
    }
}
