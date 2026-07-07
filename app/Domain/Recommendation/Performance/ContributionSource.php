<?php

namespace App\Domain\Recommendation\Performance;

use App\Domain\Recommendation\PartType;

final class ContributionSource
{
    /**
     * @param PartType $part               Which part's attribute array to read from.
     * @param string   $attributeKey       Key in the part's attribute array (snake_case column name).
     * @param float    $weight             Contribution weight applied to this source's value.
     * @param bool     $requiresNormalization  True for physical attributes (height, weight)
     *                                     that must be normalized to 0–100 before use.
     */
    public function __construct(
        public readonly PartType $part,
        public readonly string $attributeKey,
        public readonly float $weight,
        public readonly bool $requiresNormalization = false,
    ) {}
}
