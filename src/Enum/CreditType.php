<?php

namespace App\Enum;

enum CreditType: string
{
    case Refund = 'refund';
    case Marketing = 'marketing';
}
