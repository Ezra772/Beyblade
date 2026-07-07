<?php

namespace App\Domain\Recommendation\Performance;

use App\Domain\Recommendation\Normalization\NormalizationRule;

interface PerformanceEngine
{
    /**
     * Compute the aggregated performance profile for one Blade + Ratchet + Bit combination.
     *
     * All parameters are plain associative arrays — no Eloquent Models allowed here.
     * Conversion from Eloquent Models to arrays is the caller's responsibility (UseCase / Livewire).
     *
     * @param array<string, float> $bladeAttributes    e.g. ['weight'=>35.2, 'attack'=>68, ...]
     * @param array<string, float> $ratchetAttributes  e.g. ['height'=>70, 'weight'=>7.1, ...]
     * @param array<string, float> $bitAttributes       e.g. ['speed'=>35, 'stamina'=>100, ...]
     * @param ContributionRule[]   $contributionRules
     * @param NormalizationRule[]  $normalizationRules
     * @return array<string, float> Performance profile keyed by Criterion->value,
     *                              e.g. ['attack'=>72.4, 'defense'=>65.1, ..., 'control'=>58.0].
     *                              All 8 Criterion keys are always present.
     */
    public function computeProfile(
        array $bladeAttributes,
        array $ratchetAttributes,
        array $bitAttributes,
        array $contributionRules,
        array $normalizationRules,
    ): array;
}
