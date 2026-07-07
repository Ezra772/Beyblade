<?php

namespace App\Domain\Recommendation\Normalization;

/**
 * Factory for default normalization rules for Beyblade X physical attributes.
 *
 * ⚠️  PLACEHOLDER VALUES — the project owner must review and adjust theoreticalMin /
 * theoreticalMax based on the official Beyblade X / UX part specifications (lightest /
 * heaviest Blade, shortest / tallest Ratchet pin height) before this goes to production.
 * Do NOT replace these with dataset-derived min/max values — that would cause silent score
 * drift whenever a new extreme part is added to the database.
 */
final class DefaultNormalizationRules
{
    /**
     * @return NormalizationRule[]
     */
    public static function get(): array
    {
        return [
            // Ratchet pin height (mm). Theoretical range based on Beyblade X series design.
            new NormalizationRule(
                attributeKey: 'ratchet_height',
                strategy: NormalizationStrategy::MIN_MAX,
                theoreticalMin: 50.0,
                theoreticalMax: 100.0,
                invert: false,
            ),

            // Blade weight (g). Theoretical range based on lightest / heaviest Blade in X/UX series.
            new NormalizationRule(
                attributeKey: 'blade_weight',
                strategy: NormalizationStrategy::MIN_MAX,
                theoreticalMin: 15.0,
                theoreticalMax: 50.0,
                invert: false,
            ),

            // Ratchet weight (g). Theoretical range based on lightest / heaviest Ratchet in X/UX series.
            new NormalizationRule(
                attributeKey: 'ratchet_weight',
                strategy: NormalizationStrategy::MIN_MAX,
                theoreticalMin: 2.0,
                theoreticalMax: 12.0,
                invert: false,
            ),
        ];
    }
}
