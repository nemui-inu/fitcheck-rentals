<?php

namespace App\Enums;

enum UnitStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
}
