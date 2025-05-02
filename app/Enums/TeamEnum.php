<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumEnhancements;

enum TeamEnum: int
{
    use EnumEnhancements;

    case Blue = 100;
    case Red = 200;
}
