<?php

namespace App\Enums;

enum ComplaintCategory: string
{
    case Water = 'water';
    case Electricity = 'electricity';
    case Security = 'security';
    case Parking = 'parking';
    case Cleaning = 'cleaning';
    case Other = 'other';

    public function label(): string
    {
        return __('app.complaint_category_'.$this->value);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
