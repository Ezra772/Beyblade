<?php

namespace App\Domain\Recommendation\Performance;

use App\Domain\Recommendation\Criterion;
use App\Domain\Recommendation\PartType;

/**
 * Default contribution rules derived from mechanical analysis of Beyblade X parts.
 *
 * These weights represent agreed-upon mechanical reasoning for how each part type
 * contributes to each aggregated performance criterion. Do NOT change these values
 * without a mechanical justification discussed with the project owner.
 *
 * Attributes that require normalization (physical attributes not already on a 0–100 scale):
 *   - 'ratchet_height'  → Ratchet pin height (mm), normalized via DefaultNormalizationRules
 *   - 'blade_weight'    → Blade weight (g), normalized via DefaultNormalizationRules
 *   - 'ratchet_weight'  → Ratchet weight (g), normalized via DefaultNormalizationRules
 *
 * The WEIGHT criterion is additive (raw grams summed directly; no per-source weighting).
 * Bit has no weight attribute and therefore does not contribute to WEIGHT.
 */
final class DefaultContributionRules
{
    /**
     * @return ContributionRule[]
     */
    public static function get(): array
    {
        return [
            // ── ATTACK ───────────────────────────────────────────────────────────────
            // Blade is the primary driver. Ratchet height affects launch angle/smash
            // potential. Bit speed and dash add mobility-based attack contribution.
            new ContributionRule(
                criterion: Criterion::ATTACK,
                sources: [
                    new ContributionSource(PartType::BLADE, 'attack', 0.65),
                    new ContributionSource(PartType::RATCHET, 'ratchet_height', 0.10, requiresNormalization: true),
                    new ContributionSource(PartType::BIT, 'speed', 0.15),
                    new ContributionSource(PartType::BIT, 'dash', 0.10),
                ],
            ),

            // ── DEFENSE ──────────────────────────────────────────────────────────────
            // Blade defense is the strongest factor. Ratchet stability damps recoil.
            // Bit grip keeps the top in place under contact.
            new ContributionRule(
                criterion: Criterion::DEFENSE,
                sources: [
                    new ContributionSource(PartType::BLADE, 'defense', 0.55),
                    new ContributionSource(PartType::RATCHET, 'stability', 0.35),
                    new ContributionSource(PartType::BIT, 'grip', 0.10),
                ],
            ),

            // ── STAMINA ──────────────────────────────────────────────────────────────
            // Bit is the primary stamina driver (tip type dominates spin endurance).
            // Blade stamina contributes but less so. Ratchet weight affects rotational
            // inertia marginally.
            new ContributionRule(
                criterion: Criterion::STAMINA,
                sources: [
                    new ContributionSource(PartType::BLADE, 'stamina', 0.35),
                    new ContributionSource(PartType::RATCHET, 'ratchet_weight', 0.10, requiresNormalization: true),
                    new ContributionSource(PartType::BIT, 'stamina', 0.55),
                ],
            ),

            // ── SPEED ────────────────────────────────────────────────────────────────
            // Bit tip type dominates movement speed. Blade weight affects launch
            // momentum slightly. Ratchet height affects gyroscopic behavior / movement arc.
            new ContributionRule(
                criterion: Criterion::SPEED,
                sources: [
                    new ContributionSource(PartType::BLADE, 'blade_weight', 0.20, requiresNormalization: true),
                    new ContributionSource(PartType::RATCHET, 'ratchet_height', 0.10, requiresNormalization: true),
                    new ContributionSource(PartType::BIT, 'speed', 0.70),
                ],
            ),

            // ── WEIGHT ───────────────────────────────────────────────────────────────
            // Additive: raw gram values summed directly (Blade + Ratchet).
            // Bit has no weight attribute and does not contribute.
            // Raw grams are intentionally NOT normalized here — total weight is an
            // absolute physical quantity, not a 0–100 performance score.
            new ContributionRule(
                criterion: Criterion::WEIGHT,
                sources: [
                    new ContributionSource(PartType::BLADE, 'weight', 1.0),
                    new ContributionSource(PartType::RATCHET, 'weight', 1.0),
                ],
                isAdditive: true,
            ),

            // ── STABILITY ────────────────────────────────────────────────────────────
            // Ratchet stability is the dominant factor. Bit grip helps resist wobble.
            // Blade weight (heavier = more gyroscopic inertia) contributes slightly.
            new ContributionRule(
                criterion: Criterion::STABILITY,
                sources: [
                    new ContributionSource(PartType::BLADE, 'blade_weight', 0.10, requiresNormalization: true),
                    new ContributionSource(PartType::RATCHET, 'stability', 0.55),
                    new ContributionSource(PartType::BIT, 'grip', 0.35),
                ],
            ),

            // ── BURST_RESISTANCE ─────────────────────────────────────────────────────
            // Blade and Ratchet lock mechanisms share equal importance.
            // Bit grip offers a small contribution by resisting destabilizing contact.
            new ContributionRule(
                criterion: Criterion::BURST_RESISTANCE,
                sources: [
                    new ContributionSource(PartType::BLADE, 'burst_resistance', 0.45),
                    new ContributionSource(PartType::RATCHET, 'burst_resistance', 0.45),
                    new ContributionSource(PartType::BIT, 'grip', 0.10),
                ],
            ),

            // ── CONTROL ──────────────────────────────────────────────────────────────
            // Bit control is the dominant factor. Ratchet stability helps the top
            // track a consistent path.
            new ContributionRule(
                criterion: Criterion::CONTROL,
                sources: [
                    new ContributionSource(PartType::RATCHET, 'stability', 0.20),
                    new ContributionSource(PartType::BIT, 'control', 0.80),
                ],
            ),
        ];
    }
}
