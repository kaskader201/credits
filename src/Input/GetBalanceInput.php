<?php

declare(strict_types=1);

namespace App\Input;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class GetBalanceInput implements \JsonSerializable
{
    public UuidInterface $userExternalId;

    public function __construct(
        public string $requestId,
        string $userExternalId,
    ) {
        $this->userExternalId = Uuid::fromString($userExternalId);
    }

    public static function fromUseCreditInput(UseCreditInput $input): self
    {
        return new self('Neutral', $input->userExternalId->toString());
    }

    #[\Override] public function jsonSerialize(): array
    {
        return [
            'requestId' => $this->requestId,
            'userExternalId' => $this->userExternalId->toString(),
        ];
    }
}
