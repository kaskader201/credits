<?php

declare(strict_types=1);

namespace App\Input;

use App\Enum\CreditPriority;
use App\Enum\CreditType;
use App\Provider\DateTimeProvider;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class AddCreditInput implements \JsonSerializable
{
    public BigDecimal $amount;
    public UuidInterface $userExternalId;
    public CreditPriority $creditPriority;
    public CreditType $type;
    public ?DateTimeImmutable $expiredAt;
    public ?string $note;

    public function __construct(
        public string $requestId,
        float $amount,
        string $userExternalId,
        int $creditPriority,
        string $type,
        ?string $expiredAt,
        ?string $note,
    ) {
        $this->amount = BigDecimal::of($amount);
        $this->userExternalId = Uuid::fromString($userExternalId);
        $this->creditPriority = CreditPriority::from($creditPriority);
        $this->type = CreditType::from($type);
        $this->expiredAt = $expiredAt !== null ? new DateTimeImmutable($expiredAt) : null;
        $this->note = $note;
    }

    #[\Override] public function jsonSerialize(): array
    {
        return [
            'requestId' => $this->requestId,
            'amount' => $this->amount,
            'userExternalId' => $this->userExternalId->toString(),
            'creditPriority' => $this->creditPriority->value,
            'type' => $this->type->value,
            'expiredAt' => $this->expiredAt?->format(DateTimeProvider::FORMAT),
            'note' => $this->note,
        ];
    }
}
