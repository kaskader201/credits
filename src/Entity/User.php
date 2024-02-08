<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Id\Transaction\TransactionUuid;
use App\Entity\Id\User\UserUuid;
use App\Entity\Id\User\UserUuidType;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

#[ORM\Entity]
#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Index(name: 'EXTERNAL_ID', columns: ['external_id'])]
#[ORM\UniqueConstraint(name: 'EXTERNAL_ID', columns: ['external_id'])]
readonly class User implements Entity
{
    final public const TABLE_NAME = '"user"';

    #[Id]
    #[Column(type: UserUuidType::NAME, nullable: false)]
    public UserUuid $id;

    #[Column(type: Types::STRING, nullable: false)]
    public string $externalId;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public DateTimeImmutable $createdAt;

    public function __construct(
        string $externalId,
    ) {
        $this->id = UserUuid::generate();
        $this->externalId = $externalId;
        $this->createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function getId(): UserUuid
    {
        return $this->id;
    }
}
