<?php

namespace App\Service;

use App\Entity\Credit;
use App\Entity\Transaction;
use App\Enum\CreditPriority;
use App\Enum\CreditType;
use App\Enum\TransactionActionType;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Ramsey\Uuid\UuidInterface;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AddCreditService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function addCredit(
        BigDecimal $amount,
        UuidInterface $userExternalId,
        CreditPriority $creditPriority,
        CreditType $type,
        ?DateTimeImmutable $expiredAt,
        ?string $note,
    ): void {
        $this->entityManager->clear();
        $this->entityManager->wrapInTransaction(
            function (EntityManagerInterface $entityManager) use (
                $userExternalId,
                $amount,
                $creditPriority,
                $type,
                $expiredAt,
                $note
            ): void {

                $user = $this->userRepository->getByExternalId($userExternalId);
                $credit = new Credit(
                    $user,
                    $amount,
                    $creditPriority,
                    $type,
                    $expiredAt,
                    $note,
                );
                $transaction = new Transaction(
                    $user,
                    $credit,
                    TransactionActionType::Addition,
                    $amount,
                );
                $entityManager->persist($credit);
                $entityManager->persist($transaction);

            }
        );
        $this->entityManager->clear();
    }
}
