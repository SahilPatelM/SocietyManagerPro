<?php

namespace App\Enums;

enum IncomeCategory: string
{
    case Maintenance = 'maintenance';
    case Donation = 'donation';
    case Penalty = 'penalty';
    case Other = 'other';
}
