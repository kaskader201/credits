<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Id\User\UserUuid;
use App\Entity\Id\User\UserUuidType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

#[ORM\Entity]
#[ORM\Index(name: 'EXTERNAL_ID', columns: ['external_id'])]
final readonly class User
{
    #[Id]
    #[Column(type: UserUuidType::NAME, nullable: false)]
    public UserUuid $id;
    #[Column(type: Types::STRING, nullable: false)]
    public string $externalId;

    #[Column(type: Types::DATE_IMMUTABLE, nullable: false)]
    public DateTimeImmutable $createdAt;

    public function __construct(
        string $externalId,
    ) {
        $this->id = UserUuid::generate();
        $this->externalId = $externalId;
        $this->createdAt = new DateTimeImmutable();
    }

}
