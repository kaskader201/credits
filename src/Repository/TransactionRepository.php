<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Credit;
use App\Entity\Id\Credit\CreditUuidType;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

readonly class TransactionRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return Transaction[]
     */
    public function findAllUseOfCredit(Credit $credit): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Transaction::class, 't')
            ->andWhere('t.credit = :credit')
            ->setParameter('credit', $credit, CreditUuidType::NAME)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
