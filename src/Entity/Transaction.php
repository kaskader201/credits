<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\BigDecimalType;
use App\Entity\Id\Transaction\TransactionUuid;
use App\Entity\Id\Transaction\TransactionUuidType;
use App\Enum\TransactionActionType;
use App\Exception\LogicException;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity]
#[ORM\Index(name: 'USER_CREDIT', columns: ['user_id','credit_id'])]
#[ORM\Index(name: 'USER_CREATED_AT', columns: ['user_id', 'created_at'])]
class Transaction
{

    #[Id]
    #[Column(type: TransactionUuidType::NAME, nullable: false)]
    public readonly TransactionUuid $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public readonly User $user;

    #[ManyToOne(targetEntity: Credit::class)]
    #[JoinColumn(nullable: false)]
    public readonly Credit $credit;

    #[Column(type: Types::STRING, nullable: false, enumType: TransactionActionType::class)]
    public readonly TransactionActionType $action;

    #[Column(type: BigDecimalType::NAME, precision: 36, scale: 2, nullable: false)]
    public readonly BigDecimal $amount;

    #[Column(type: Types::DATE_IMMUTABLE, nullable: false)]
    public readonly DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        Credit $credit,
        TransactionActionType $action,
        BigDecimal $amount,
    ) {
        $this->id = TransactionUuid::generate();
        if ($user !== $credit->user) {
            throw new LogicException(
                "User #{$user->id->toString()} and User #{$credit->user->id->toString()} on Credit#{$credit->id->toString()} do not match.",
            );
        }
        $this->user = $user;
        $this->credit = $credit;

        if ($action->isNegative() && !$amount->isNegative() || !$action->isNegative() && $amount->isNegative()) {
            $amount = $amount->negated();
        }
        $this->action = $action;
        $this->amount = $amount;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function createExpiredAndMarkCreditAsExpired(Credit $credit, BigDecimal $expiredAmount): self
    {
        $credit->markAsExpired($expiredAmount);

        return new self(
            $credit->user,
            $credit,
            TransactionActionType::Expiration,
            $expiredAmount,
        );
    }

}
