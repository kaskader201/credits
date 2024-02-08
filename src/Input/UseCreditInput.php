<?php

namespace App\Input;

use Brick\Math\BigDecimal;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class UseCreditInput
{
    public BigDecimal $amount;
    public UuidInterface $userExternalId;
    public function __construct(
        float $amount,
        string $userExternalId,
    ) {
        $this->amount = BigDecimal::of($amount);
        $this->userExternalId = Uuid::fromString($userExternalId);
    }
}
