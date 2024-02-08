<?php

declare(strict_types=1);

namespace App\Input;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class CreateUserInput
{

    public UuidInterface $userExternalId;

    public function __construct(
        string $userExternalId,
    ) {
        $this->userExternalId = Uuid::fromString($userExternalId);
    }

}
