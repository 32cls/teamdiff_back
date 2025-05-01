<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumEnhancements;

enum RoleEnum: string
{
    use EnumEnhancements;

    case Top = 'TOP';
    case Jungle = 'JUNGLE';
    case Mid = 'MID';
    case Bottom = 'BOTTOM';
    case Support = 'SUPPORT';
}
