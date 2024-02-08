<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\BigDecimalType;
use App\Entity\Id\Request\RequestUuid;
use App\Entity\Id\Request\RequestUuidType;
use App\Enum\RequestOperation;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity]
#[ORM\UniqueConstraint(name:"REQUEST_USER",columns: ['request_id', 'user_id'])]
class Request implements Entity
{
    #[Id]
    #[Column(type: RequestUuidType::NAME, nullable: false)]
    public RequestUuid $id;

    #[Column(type: Types::STRING, nullable: false)]
    public string $requestId;

    #[Column(type: BigDecimalType::NAME, precision: 36, scale: 2, nullable: false)]
    public BigDecimal $amount;

    #[Column(type: Types::STRING, nullable: false, enumType: RequestOperation::class)]
    public RequestOperation $operation;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public User $user;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::JSON, nullable: false)]
    public array $data;

    public function __construct(
        string $requestId,
        BigDecimal $amount,
        RequestOperation $operation,
        User $user,
        array $data
    ) {
        $this->id = RequestUuid::generate();
        $this->requestId = $requestId;
        $this->amount = $amount;
        $this->user = $user;
        $this->operation = $operation;
        $this->createdAt = new DateTimeImmutable('now');
        $this->data = $data;
    }

    public static function createNeutral(string $requestId, User $user): self
    {
        return new self(
            $requestId,
            BigDecimal::zero(),
            RequestOperation::Check,
            $user,
            [],
        );
    }
    public function getId(): RequestUuid
    {
        return $this->id;
    }

    public function getDataAsJson(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
