<?php

namespace App\Domain\Recommendation\Weight;

final class WeightProfile
{
    /**
     * @param string               $id      Unique identifier (e.g. 'attack_build').
     * @param string               $label   Human-readable label (e.g. 'Attack Build').
     * @param array<string, float> $weights Criteria weights keyed by Criterion->value.
     *                                      All values should sum to 1.0.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly array $weights,
    ) {}
}
