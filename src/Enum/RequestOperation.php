<?php

namespace App\Enum;

enum RequestOperation: string
{
    case Income = 'income';
    case Outcome = 'outcome';
    case Check = 'check';
}
