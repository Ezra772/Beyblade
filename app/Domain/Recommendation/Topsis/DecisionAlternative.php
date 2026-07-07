<?php

namespace App\Domain\Recommendation\Topsis;

final class DecisionAlternative
{
    /**
     * @param string               $id     Unique identifier for this combination (e.g. 'blade-1_ratchet-2_bit-3').
     * @param array<string, float> $values Performance profile keyed by Criterion->value.
     */
    public function __construct(
        public readonly string $id,
        public readonly array $values,
    ) {}
}
