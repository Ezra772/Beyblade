<?php

namespace App\Domain\Recommendation\Normalization;

final class DefaultNormalizationService implements NormalizationService
{
    /**
     * Normalize $rawValue for $attributeKey using the first matching rule in $rules.
     *
     * If no rule is found the value is clamped to [0, 100] and returned as-is
     * (safe fallback — assumes the attribute is already on a comparable scale).
     *
     * @param NormalizationRule[] $rules
     */
    public function normalize(string $attributeKey, float $rawValue, array $rules): float
    {
        $rule = $this->findRule($attributeKey, $rules);

        if ($rule === null) {
            return $this->clamp($rawValue, 0.0, 100.0);
        }

        return match ($rule->strategy) {
            NormalizationStrategy::NONE => $rawValue,
            NormalizationStrategy::MIN_MAX => $this->applyMinMax($rawValue, $rule),
            NormalizationStrategy::ORDINAL_MAP => $rawValue, // Reserved — no ordinal attributes yet.
        };
    }

    /**
     * @param NormalizationRule[] $rules
     */
    private function findRule(string $attributeKey, array $rules): ?NormalizationRule
    {
        foreach ($rules as $rule) {
            if ($rule->attributeKey === $attributeKey) {
                return $rule;
            }
        }

        return null;
    }

    private function applyMinMax(float $rawValue, NormalizationRule $rule): float
    {
        $min = $rule->theoreticalMin ?? 0.0;
        $max = $rule->theoreticalMax ?? 100.0;

        $range = $max - $min;

        // Guard against division by zero when theoretical bounds are identical.
        if ($range == 0.0) {
            return 0.0;
        }

        $clamped = $this->clamp($rawValue, $min, $max);
        $result = (($clamped - $min) / $range) * 100.0;

        return $rule->invert ? (100.0 - $result) : $result;
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
