<?php

namespace App\Domain\Recommendation\Topsis;

use App\Domain\Recommendation\CriterionType;

interface TopsisEngine
{
    /**
     * Rank a set of decision alternatives using the TOPSIS method (8 explicit stages).
     *
     * @param DecisionAlternative[]        $alternatives   Candidates to rank.
     * @param array<string, float>         $criteriaWeights Weights per criterion (Criterion->value keys).
     * @param array<string, CriterionType> $criteriaTypes   BENEFIT or COST per criterion.
     * @return TopsisResult[] Sorted descending by preferenceScore; rank starts at 1.
     *
     * @throws \InvalidArgumentException if $alternatives is empty.
     */
    public function rank(array $alternatives, array $criteriaWeights, array $criteriaTypes): array;
}
