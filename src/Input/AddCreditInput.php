<?php

declare(strict_types=1);

namespace App\Input;

use App\Enum\CreditPriority;
use App\Enum\CreditType;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class AddCreditInput
{

    public BigDecimal $amount;
    public UuidInterface $userExternalId;
    public CreditPriority $creditPriority;
    public CreditType $type;
    public ?DateTimeImmutable $expiredAt;
    public ?string $note;

    public function __construct(
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

}
