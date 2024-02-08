<?php

declare(strict_types=1);

namespace App\Entity\Id\Transaction;

use App\Doctrine\Uuid7Type;

class TransactionUuidType extends Uuid7Type
{
    final public const NAME = 'transaction_uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public static function getUuidClass(): string
    {
        return TransactionUuid::class;
    }
}
