<?php

namespace App\Domain\Management\Import;

enum ImportStrategy
{
    case SKIP_DUPLICATES;
    case OVERWRITE_DUPLICATES;
    case REJECT_ON_DUPLICATE;
}
