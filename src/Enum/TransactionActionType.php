<?php

namespace App\Enum;

enum TransactionActionType: string
{
    case Addition = 'addition';
    case CreditUse = 'credit_use';
    case Expiration = 'expiration';

    public function isNegative(): bool
    {
        return match ($this) {
            self::Addition => false,
            self::CreditUse,
            self::Expiration => true,
        };
    }
}
