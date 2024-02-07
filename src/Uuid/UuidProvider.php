<?php

declare(strict_types=1);

namespace App\Uuid;

use Ramsey\Uuid\Uuid;

class UuidProvider extends Uuid implements UuidInterface
{

    public static function fromString(string $uuid): UuidInterface
    {
        $result = parent::fromString($uuid);
        if (!($result instanceof UuidInterface)) {
            throw new \RuntimeException('Invalid type');
        }

        return $result;
    }
}
