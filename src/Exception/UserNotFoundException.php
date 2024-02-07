<?php

namespace App\Exception;

use App\Uuid\UuidInterface;

class UserNotFoundException extends RuntimeException
{

    public static function byExternalId(UuidInterface $externalId): self
    {
        return new self("User with external id '{$externalId}' not found");
    }

}
