<?php

namespace App\Domain\Recommendation\Performance;

use App\Domain\Recommendation\Criterion;
use App\Domain\Recommendation\Normalization\NormalizationRule;
use App\Domain\Recommendation\Normalization\NormalizationService;
use App\Domain\Recommendation\PartType;
use InvalidArgumentException;

final class DefaultPerformanceEngine implements PerformanceEngine
{
    public function __construct(
        private readonly NormalizationService $normalizationService,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function computeProfile(
        array $bladeAttributes,
        array $ratchetAttributes,
        array $bitAttributes,
        array $contributionRules,
        array $normalizationRules,
    ): array {
        // Seed all 8 criteria with a default of 0.0 to guarantee all keys are always present.
        $profile = [];
        foreach (Criterion::cases() as $criterion) {
            $profile[$criterion->value] = 0.0;
        }

        foreach ($contributionRules as $rule) {
            $profile[$rule->criterion->value] = $this->computeCriterion(
                $rule,
                $bladeAttributes,
                $ratchetAttributes,
                $bitAttributes,
                $normalizationRules,
            );
        }

        return $profile;
    }

    /**
     * Compute the value for a single criterion from its ContributionRule.
     *
     * @param NormalizationRule[] $normalizationRules
     */
    private function computeCriterion(
        ContributionRule $rule,
        array $bladeAttributes,
        array $ratchetAttributes,
        array $bitAttributes,
        array $normalizationRules,
    ): float {
        $total = 0.0;

        foreach ($rule->sources as $source) {
            $value = $this->readValue(
                $source,
                $bladeAttributes,
                $ratchetAttributes,
                $bitAttributes,
                $normalizationRules,
            );

            if ($rule->isAdditive) {
                $total += $value;
            } else {
                $total += $value * $source->weight;
            }
        }

        return $total;
    }

    /**
     * Read and optionally normalize a single source attribute value.
     *
     * @param NormalizationRule[] $normalizationRules
     *
     * @throws InvalidArgumentException when the attribute key is not found in the part's array.
     */
    private function readValue(
        ContributionSource $source,
        array $bladeAttributes,
        array $ratchetAttributes,
        array $bitAttributes,
        array $normalizationRules,
    ): float {
        $partAttributes = match ($source->part) {
            PartType::BLADE => $bladeAttributes,
            PartType::RATCHET => $ratchetAttributes,
            PartType::BIT => $bitAttributes,
        };

        if (! array_key_exists($source->attributeKey, $partAttributes)) {
            throw new InvalidArgumentException(sprintf(
                'Attribute "%s" not found in %s attributes. Available keys: [%s].',
                $source->attributeKey,
                $source->part->name,
                implode(', ', array_keys($partAttributes)),
            ));
        }

        $raw = (float) $partAttributes[$source->attributeKey];

        if ($source->requiresNormalization) {
            return $this->normalizationService->normalize($source->attributeKey, $raw, $normalizationRules);
        }

        return $raw;
    }
}
