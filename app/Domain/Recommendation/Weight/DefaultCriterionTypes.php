<?php

namespace App\Domain\Recommendation\Weight;

use App\Domain\Recommendation\Criterion;
use App\Domain\Recommendation\CriterionType;

/**
 * Factory for the default TOPSIS criterion type mapping.
 *
 * All 8 criteria are currently BENEFIT (higher is better).
 * There are no COST criteria in this version — per AI_README.md architecture decision.
 */
final class DefaultCriterionTypes
{
    /**
     * @return array<string, CriterionType> keyed by Criterion->value
     */
    public static function get(): array
    {
        $types = [];

        foreach (Criterion::cases() as $criterion) {
            $types[$criterion->value] = CriterionType::BENEFIT;
        }

        return $types;
    }
}
