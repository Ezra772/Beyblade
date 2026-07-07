<?php

namespace App\Domain\Recommendation;

enum Criterion: string
{
    case ATTACK = 'attack';
    case DEFENSE = 'defense';
    case STAMINA = 'stamina';
    case SPEED = 'speed';
    case WEIGHT = 'weight';
    case STABILITY = 'stability';
    case BURST_RESISTANCE = 'burst_resistance';
    case CONTROL = 'control';
}
