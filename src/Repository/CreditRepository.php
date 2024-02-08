<?php

namespace App\Repository;

use App\Entity\Credit;
use App\Entity\Id\User\UserUuidType;
use App\Entity\User;
use App\Provider\DateTimeProvider;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

readonly class CreditRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DateTimeProvider $dateTimeProvider,
    ) {
    }

    /**
     * @return Credit[]
     */
    public function findAllUsable(User $user): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Credit::class, 'c')
            ->andWhere('c.usable = true')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user, UserUuidType::NAME)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Credit[]
     */
    public function findAllUsableSorted(User $user): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Credit::class, 'c')
            ->andWhere('c.usable = true')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user, UserUuidType::NAME)
            ->orderBy('c.priority', 'ASC')
            ->addOrderBy('c.expiredAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Credit[]
     */
    public function findAllUnUsedButExpiredForUser(User $user, ?DateTimeImmutable $now = null): array
    {
        $now ??= $this->dateTimeProvider->now();
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Credit::class, 'c')
            ->andWhere('c.usable = true')
            ->andWhere('c.expiredAt < :now')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user, UserUuidType::NAME)
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Credit[]
     */
    public function findAllUnUsedButExpiredForAll(?DateTimeImmutable $now = null): array
    {
        $now ??= $this->dateTimeProvider->now();
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Credit::class, 'c')
            ->andWhere('c.usable = true')
            ->andWhere('c.expiredAt < :now')
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->getQuery()
            ->getResult();
    }
}
