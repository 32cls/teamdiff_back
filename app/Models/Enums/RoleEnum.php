<?php

namespace App\Models\Enums;

enum RoleEnum: string
{
    use EnumEnhancementsTrait;

    case Top = 'TOP';
    case Jungle = 'JUNGLE';
    case Mid = 'MID';
    case Bottom = 'BOTTOM';
    case Support = 'SUPPORT';
}
