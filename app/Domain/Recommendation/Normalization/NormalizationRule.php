<?php

namespace App\Domain\Recommendation\Normalization;

final class NormalizationRule
{
    /**
     * @param string                  $attributeKey    Key name used to match ContributionSource->attributeKey.
     * @param NormalizationStrategy   $strategy        How to normalize this attribute.
     * @param float|null              $theoreticalMin  Theoretical lower bound (not dataset min).
     * @param float|null              $theoreticalMax  Theoretical upper bound (not dataset max).
     * @param array<string,float>|null $ordinalMap     Lookup table for ORDINAL_MAP strategy.
     * @param bool                    $invert          If true, result = 100 - normalizedValue.
     */
    public function __construct(
        public readonly string $attributeKey,
        public readonly NormalizationStrategy $strategy,
        public readonly ?float $theoreticalMin = null,
        public readonly ?float $theoreticalMax = null,
        public readonly ?array $ordinalMap = null,
        public readonly bool $invert = false,
    ) {}
}
