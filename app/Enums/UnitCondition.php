<?php

namespace App\Enums;

enum UnitCondition: string
{
    case New = 'new';
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case Worn = 'worn';
}
