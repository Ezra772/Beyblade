<?php

namespace App\Domain\Recommendation\Performance;

use App\Domain\Recommendation\Criterion;

final class ContributionRule
{
    /**
     * @param Criterion            $criterion  The output criterion this rule produces.
     * @param ContributionSource[] $sources    Ordered list of part-attribute sources.
     * @param bool                 $isAdditive When true, source values are summed directly
     *                                         (source->weight is ignored). When false, each
     *                                         source value is multiplied by source->weight.
     */
    public function __construct(
        public readonly Criterion $criterion,
        public readonly array $sources,
        public readonly bool $isAdditive = false,
    ) {}
}
