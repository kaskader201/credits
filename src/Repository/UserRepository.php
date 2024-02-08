<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function getByExternalId(UuidInterface $externalId): User
    {
        $user = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->andWhere('u.externalId = :externalId')
            ->setParameter('externalId', $externalId->toString())
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if ($user === null) {
            throw UserNotFoundException::byExternalId($externalId);
        }
        return $user;
    }
}
