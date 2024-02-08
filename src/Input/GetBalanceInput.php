<?php

declare(strict_types=1);

namespace App\Input;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class GetBalanceInput
{
    public UuidInterface $userExternalId;

    public function __construct(
        string $userExternalId,
    ) {
        $this->userExternalId = Uuid::fromString($userExternalId);
    }

    public static function fromUseCreditInput(UseCreditInput $input): self
    {
        return new self($input->userExternalId->toString());
    }
}
