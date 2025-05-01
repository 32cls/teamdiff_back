<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumEnhancements;

enum RegionEnum: string
{
    use EnumEnhancements;

    case NA1 = 'NA1';
    case BR1 = 'BR1';
    case LA1 = 'LA1';
    case LA2 = 'LA2';
    case KR = 'KR';
    case JP1 = 'JP1';
    case EUN1 = 'EUN1';
    case EUW1 = 'EUW1';
    case ME1 = 'ME1';
    case TR1 = 'TR1';
    case RU = 'RU';
    case OC1 = 'OC1';
    case SG2 = 'SG2';
    case TW2 = 'TW2';
    case VN2 = 'VN2';
}
