<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

class UserAlreadyExistException extends RuntimeException
{

    public static function byExternalId(UuidInterface $externalId): self
    {
        return new self("User with external id '{$externalId->toString()}' already exist");
    }

}
