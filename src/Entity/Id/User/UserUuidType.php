<?php

declare(strict_types=1);

namespace App\Entity\Id\User;

use App\Doctrine\Uuid7Type;
use App\Entity\Id\Transaction\TransactionUuid;

class UserUuidType extends Uuid7Type
{
    final public const NAME = 'user_uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public static function getUuidClass(): string
    {
        return UserUuid::class;
    }
}
