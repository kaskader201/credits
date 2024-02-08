<?php

declare(strict_types=1);

namespace App\Entity\Id\Credit;

use App\Doctrine\Uuid7Type;

/**
 * @template-extends Uuid7Type<CreditUuid>
 */
class CreditUuidType extends Uuid7Type
{

    final public const NAME = 'credit_uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public static function getUuidClass(): string
    {
        return CreditUuid::class;
    }
}
