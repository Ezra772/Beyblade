<?php

namespace App\Domain\Recommendation\Topsis;

final class TopsisResult
{
    /**
     * @param string $alternativeId           The DecisionAlternative->id this result belongs to.
     * @param float  $preferenceScore         V_i = D- / (D+ + D-), range [0, 1].
     * @param float  $distanceToPositiveIdeal D+ — Euclidean distance to the positive ideal solution.
     * @param float  $distanceToNegativeIdeal D- — Euclidean distance to the negative ideal solution.
     * @param int    $rank                    1-based rank (1 = best).
     */
    public function __construct(
        public readonly string $alternativeId,
        public readonly float $preferenceScore,
        public readonly float $distanceToPositiveIdeal,
        public readonly float $distanceToNegativeIdeal,
        public readonly int $rank,
    ) {}
}
