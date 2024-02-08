<?php

declare(strict_types=1);

namespace App\Entity\Id\Request;

use App\Doctrine\Uuid7Type;
use App\Entity\Id\Transaction\TransactionUuid;

class RequestUuidType extends Uuid7Type
{
    final public const NAME = 'request_uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public static function getUuidClass(): string
    {
        return RequestUuid::class;
    }
}
