<?php

namespace App\Domain\Recommendation\Normalization;

interface NormalizationService
{
    /**
     * Normalize a single raw value according to the matching rule in $rules.
     *
     * @param NormalizationRule[] $rules
     */
    public function normalize(string $attributeKey, float $rawValue, array $rules): float;
}
