<?php

namespace App\Domain\Recommendation\Weight;

use App\Domain\Recommendation\Criterion;

/**
 * Factory for preset WeightProfiles used in testing and initial seeding.
 *
 * These will be migrated to a `weight_profiles` database table in a later stage.
 * The WeightProfile DTO structure will remain identical — only the data source changes.
 */
final class DefaultWeightProfiles
{
    /**
     * @return WeightProfile[]
     */
    public static function get(): array
    {
        return [
            new WeightProfile(
                id: 'attack_build',
                label: 'Attack Build',
                weights: [
                    Criterion::ATTACK->value => 0.40,
                    Criterion::DEFENSE->value => 0.10,
                    Criterion::STAMINA->value => 0.10,
                    Criterion::SPEED->value => 0.20,
                    Criterion::WEIGHT->value => 0.20,
                    Criterion::STABILITY->value => 0.00,
                    Criterion::BURST_RESISTANCE->value => 0.00,
                    Criterion::CONTROL->value => 0.00,
                ],
            ),

            new WeightProfile(
                id: 'defense_build',
                label: 'Defense Build',
                weights: [
                    Criterion::ATTACK->value => 0.05,
                    Criterion::DEFENSE->value => 0.35,
                    Criterion::STAMINA->value => 0.10,
                    Criterion::SPEED->value => 0.00,
                    Criterion::WEIGHT->value => 0.20,
                    Criterion::STABILITY->value => 0.20,
                    Criterion::BURST_RESISTANCE->value => 0.10,
                    Criterion::CONTROL->value => 0.00,
                ],
            ),

            new WeightProfile(
                id: 'stamina_build',
                label: 'Stamina Build',
                weights: [
                    Criterion::ATTACK->value => 0.00,
                    Criterion::DEFENSE->value => 0.10,
                    Criterion::STAMINA->value => 0.45,
                    Criterion::SPEED->value => 0.05,
                    Criterion::WEIGHT->value => 0.05,
                    Criterion::STABILITY->value => 0.15,
                    Criterion::BURST_RESISTANCE->value => 0.10,
                    Criterion::CONTROL->value => 0.10,
                ],
            ),

            new WeightProfile(
                id: 'balance_build',
                label: 'Balance Build',
                weights: [
                    Criterion::ATTACK->value => 0.125,
                    Criterion::DEFENSE->value => 0.125,
                    Criterion::STAMINA->value => 0.125,
                    Criterion::SPEED->value => 0.125,
                    Criterion::WEIGHT->value => 0.125,
                    Criterion::STABILITY->value => 0.125,
                    Criterion::BURST_RESISTANCE->value => 0.125,
                    Criterion::CONTROL->value => 0.125,
                ],
            ),
        ];
    }
}
