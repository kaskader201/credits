<?php

namespace App\Input;

use Brick\Math\BigDecimal;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class UseCreditInput implements \JsonSerializable
{
    public string $requestId;
    public BigDecimal $amount;
    public UuidInterface $userExternalId;

    public function __construct(
        string $requestId,
        float $amount,
        string $userExternalId,
    ) {
        $this->requestId = $requestId;
        $this->amount = BigDecimal::of($amount);
        $this->userExternalId = Uuid::fromString($userExternalId);
    }

    #[\Override] public function jsonSerialize(): array
    {
        return [
            'requestId' => $this->requestId,
            'amount' => $this->amount,
            'userExternalId' => $this->userExternalId->toString(),
        ];
    }
}
