<?php

namespace App\Enums;

enum MaintenanceBillType: string
{
    case General = 'general';
    case Parking = 'parking';

    public function label(): string
    {
        return match ($this) {
            self::General => __('app.maintenance_type_general'),
            self::Parking => __('app.maintenance_type_parking'),
        };
    }
}
