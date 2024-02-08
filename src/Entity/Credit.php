<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\BigDecimalType;
use App\Entity\Id\Credit\CreditUuid;
use App\Entity\Id\Credit\CreditUuidType;
use App\Enum\CreditPriority;
use App\Enum\CreditType;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity]
#[ORM\Index(name: 'USABLE_PRIORITY_EXPIRATION', columns: ['usable','priority','expired_at'])]
#[ORM\Index(name: 'USABLE_USER_PRIORITY_EXPIRATION', columns: ['usable','user_id','priority','expired_at'])]
class Credit implements Entity
{
    #[Id]
    #[Column(type: CreditUuidType::NAME, nullable: false)]
    public readonly CreditUuid $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public readonly User $user;

    #[Column(type: BigDecimalType::NAME, precision: 36, scale: 2, nullable: false)]
    public readonly BigDecimal $amount;

    #[Column(type: Types::INTEGER, nullable: false, enumType: CreditPriority::class)]
    public readonly CreditPriority $priority;

    #[Column(type: Types::STRING, nullable: false, enumType: CreditType::class)]
    public readonly CreditType $type;

    #[Column(type: Types::STRING, nullable: true)]
    public readonly ?string $note;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    public readonly ?DateTimeImmutable $expiredAt;

    #[Column(type: Types::BOOLEAN, nullable: true)]
    private ?true $usable;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $fullyUsedAt;

    #[Column(type: BigDecimalType::NAME, precision: 36, scale: 2, nullable: false)]
    private BigDecimal $expiredAmount;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public readonly DateTimeImmutable $createdAt;


    public function __construct(
        User $user,
        BigDecimal $amount,
        CreditPriority $priority,
        CreditType $type,
        ?DateTimeImmutable $expiredAt,
        ?string $note,
    ) {
        $this->id = CreditUuid::generate();
        ;
        $this->user = $user;
        $this->amount = $amount;
        $this->priority = $priority;
        $this->type = $type;
        $this->note = $note;
        $this->expiredAt = $expiredAt;

        $this->usable = true;
        $this->fullyUsedAt = null;
        $this->expiredAmount = BigDecimal::zero();
        $this->createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function isUsable(): bool
    {
        return $this->usable === true;
    }

    public function markAsExpired(BigDecimal $expiredAmount): void
    {
        if ($this->amount->isLessThan($expiredAmount)) {
            throw new \LogicException("Cannot expired more than the total amount on {$this->id->toString()}.");
        }
        $this->expiredAmount = $expiredAmount;
        $this->markAsFullyUsed();
    }

    public function markAsFullyUsed(): void
    {
        $this->usable = null;
        $this->fullyUsedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function getFullyUsedAt(): ?DateTimeImmutable
    {
        return $this->fullyUsedAt;
    }

    public function getExpiredAmount(): BigDecimal
    {
        return $this->expiredAmount;
    }

    public function getId(): CreditUuid
    {
        return $this->id;
    }
}
