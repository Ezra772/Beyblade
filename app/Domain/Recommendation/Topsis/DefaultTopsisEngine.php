<?php

namespace App\Domain\Recommendation\Topsis;

use App\Domain\Recommendation\CriterionType;
use InvalidArgumentException;

/**
 * TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution) engine.
 *
 * Implements all 8 stages as explicit private methods for auditability and unit-testability.
 */
final class DefaultTopsisEngine implements TopsisEngine
{
    /**
     * {@inheritdoc}
     */
    public function rank(array $alternatives, array $criteriaWeights, array $criteriaTypes): array
    {
        if (empty($alternatives)) {
            throw new InvalidArgumentException('Cannot rank an empty list of alternatives.');
        }

        $criteria = array_keys($criteriaWeights);

        // Stage 1 — Build the raw decision matrix.
        $matrix = $this->buildDecisionMatrix($alternatives, $criteria);

        // Stage 2 — Normalize using vector normalization (r_ij = x_ij / √Σx_ij²).
        $normalized = $this->normalizeMatrix($matrix, $criteria);

        // Stage 3 — Apply criterion weights to the normalized matrix.
        $weighted = $this->applyWeights($normalized, $criteria, $criteriaWeights);

        // Stage 4 — Determine positive ideal solution (A+).
        $positiveIdeal = $this->determinePositiveIdeal($weighted, $criteria, $criteriaTypes);

        // Stage 5 — Determine negative ideal solution (A-).
        $negativeIdeal = $this->determineNegativeIdeal($weighted, $criteria, $criteriaTypes);

        // Stage 6 — Calculate Euclidean distances to A+ and A- for each alternative.
        $distances = $this->calculateDistances($weighted, $positiveIdeal, $negativeIdeal, $criteria);

        // Stage 7 — Calculate preference scores V_i = D- / (D+ + D-).
        $preferenceScores = $this->calculatePreferenceScores($distances);

        // Stage 8 — Build ranked TopsisResult array sorted by preference score descending.
        return $this->buildRankedResults($alternatives, $preferenceScores, $distances);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 1: Build raw decision matrix
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  DecisionAlternative[] $alternatives
     * @param  string[]              $criteria
     * @return array<string, array<string, float>>  [alternativeId => [criterion => value]]
     */
    private function buildDecisionMatrix(array $alternatives, array $criteria): array
    {
        $matrix = [];

        foreach ($alternatives as $alternative) {
            $row = [];
            foreach ($criteria as $criterion) {
                $row[$criterion] = $alternative->values[$criterion] ?? 0.0;
            }
            $matrix[$alternative->id] = $row;
        }

        return $matrix;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 2: Vector normalization  r_ij = x_ij / √(Σ x_ij²)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, array<string, float>> $matrix
     * @param  string[]                            $criteria
     * @return array<string, array<string, float>>
     */
    private function normalizeMatrix(array $matrix, array $criteria): array
    {
        // Compute the column norm (√Σx_ij²) for each criterion.
        $columnNorms = [];
        foreach ($criteria as $criterion) {
            $sumOfSquares = 0.0;
            foreach ($matrix as $row) {
                $sumOfSquares += ($row[$criterion] ** 2);
            }
            $columnNorms[$criterion] = $sumOfSquares > 0.0 ? sqrt($sumOfSquares) : 1.0;
        }

        $normalized = [];
        foreach ($matrix as $altId => $row) {
            foreach ($criteria as $criterion) {
                $normalized[$altId][$criterion] = $row[$criterion] / $columnNorms[$criterion];
            }
        }

        return $normalized;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 3: Apply weights  v_ij = w_j × r_ij
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, array<string, float>> $normalized
     * @param  string[]                            $criteria
     * @param  array<string, float>                $weights
     * @return array<string, array<string, float>>
     */
    private function applyWeights(array $normalized, array $criteria, array $weights): array
    {
        $weighted = [];

        foreach ($normalized as $altId => $row) {
            foreach ($criteria as $criterion) {
                $weighted[$altId][$criterion] = ($weights[$criterion] ?? 0.0) * $row[$criterion];
            }
        }

        return $weighted;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 4: Positive ideal solution  A+ = {max for BENEFIT, min for COST}
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, array<string, float>> $weighted
     * @param  string[]                            $criteria
     * @param  array<string, CriterionType>        $types
     * @return array<string, float>
     */
    private function determinePositiveIdeal(array $weighted, array $criteria, array $types): array
    {
        return $this->determineIdeal($weighted, $criteria, $types, positive: true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 5: Negative ideal solution  A- = {min for BENEFIT, max for COST}
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, array<string, float>> $weighted
     * @param  string[]                            $criteria
     * @param  array<string, CriterionType>        $types
     * @return array<string, float>
     */
    private function determineNegativeIdeal(array $weighted, array $criteria, array $types): array
    {
        return $this->determineIdeal($weighted, $criteria, $types, positive: false);
    }

    /**
     * Shared logic for positive (positive=true) and negative (positive=false) ideal solutions.
     *
     * @param  array<string, array<string, float>> $weighted
     * @param  string[]                            $criteria
     * @param  array<string, CriterionType>        $types
     * @return array<string, float>
     */
    private function determineIdeal(
        array $weighted,
        array $criteria,
        array $types,
        bool $positive,
    ): array {
        $ideal = [];

        foreach ($criteria as $criterion) {
            $columnValues = array_column($weighted, $criterion);
            $isBenefit = ($types[$criterion] ?? CriterionType::BENEFIT) === CriterionType::BENEFIT;

            $ideal[$criterion] = ($positive === $isBenefit)
                ? max($columnValues)
                : min($columnValues);
        }

        return $ideal;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 6: Euclidean distances D+ and D-
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, array<string, float>> $weighted
     * @param  array<string, float>                $positiveIdeal
     * @param  array<string, float>                $negativeIdeal
     * @param  string[]                            $criteria
     * @return array<string, array{positive: float, negative: float}>
     */
    private function calculateDistances(
        array $weighted,
        array $positiveIdeal,
        array $negativeIdeal,
        array $criteria,
    ): array {
        $distances = [];

        foreach ($weighted as $altId => $row) {
            $dPlus = 0.0;
            $dMinus = 0.0;

            foreach ($criteria as $criterion) {
                $dPlus += (($row[$criterion] - $positiveIdeal[$criterion]) ** 2);
                $dMinus += (($row[$criterion] - $negativeIdeal[$criterion]) ** 2);
            }

            $distances[$altId] = [
                'positive' => sqrt($dPlus),
                'negative' => sqrt($dMinus),
            ];
        }

        return $distances;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 7: Preference score  V_i = D- / (D+ + D-)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, array{positive: float, negative: float}> $distances
     * @return array<string, float>  [alternativeId => preferenceScore]
     */
    private function calculatePreferenceScores(array $distances): array
    {
        $scores = [];

        foreach ($distances as $altId => ['positive' => $dPlus, 'negative' => $dMinus]) {
            $total = $dPlus + $dMinus;

            // Guard: if both distances are zero all alternatives are identical; score 0.0.
            $scores[$altId] = ($total == 0.0) ? 0.0 : ($dMinus / $total);
        }

        return $scores;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stage 8: Build ranked results sorted by preference score descending
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  DecisionAlternative[]                                    $alternatives
     * @param  array<string, float>                                     $preferenceScores
     * @param  array<string, array{positive: float, negative: float}>   $distances
     * @return TopsisResult[]
     */
    private function buildRankedResults(
        array $alternatives,
        array $preferenceScores,
        array $distances,
    ): array {
        // Sort alternative IDs by preference score descending.
        $ids = array_keys($preferenceScores);
        usort($ids, fn ($a, $b) => $preferenceScores[$b] <=> $preferenceScores[$a]);

        $results = [];
        $rank = 1;

        foreach ($ids as $altId) {
            $results[] = new TopsisResult(
                alternativeId: $altId,
                preferenceScore: $preferenceScores[$altId],
                distanceToPositiveIdeal: $distances[$altId]['positive'],
                distanceToNegativeIdeal: $distances[$altId]['negative'],
                rank: $rank++,
            );
        }

        return $results;
    }
}
