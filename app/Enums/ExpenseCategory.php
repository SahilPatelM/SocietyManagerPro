<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Electricity = 'electricity';
    case Water = 'water';
    case SecuritySalary = 'security_salary';
    case CleanerSalary = 'cleaner_salary';
    case Repair = 'repair';
    case Garden = 'garden';
    case Festival = 'festival';
    case Miscellaneous = 'miscellaneous';
}
