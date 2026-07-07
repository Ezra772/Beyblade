<?php

namespace App\Domain\Recommendation\Normalization;

enum NormalizationStrategy
{
    case MIN_MAX;
    case ORDINAL_MAP; // Reserved for future non-numeric physical attributes; not used by default rules.
    case NONE;
}
