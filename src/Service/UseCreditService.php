<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Enum\CreditPriority;
use App\Enum\TransactionActionType;
use App\Exception\BalanceToLowException;
use App\Exception\UserNotFoundException;
use App\Repository\CreditRepository;
use App\Repository\UserRepository;
use Ramsey\Uuid\UuidInterface;
use Brick\Math\BigDecimal;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UseCreditService
{
    public function __construct(
        private CreditRepository $creditRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private CreditService $creditService,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function useCredit(
        BigDecimal $amount,
        UuidInterface $userExternalId,
    ): void {
        $this->entityManager->clear();
        $this->entityManager->wrapInTransaction(
            function (EntityManagerInterface $entityManager) use (
                $userExternalId,
                $amount,
            ): void {
                $user = $this->userRepository->getByExternalId($userExternalId);
                $credits = $this->creditRepository->findAllUsableSorted($user);
                $totalOfAmount = $amount;
                foreach ($credits as $credit) {
                    $usableAmount = $this->creditService->getUsableAmountOfCredit($credit);
                    if ($totalOfAmount->isGreaterThanOrEqualTo($usableAmount)) {
                        $credit->markAsFullyUsed();
                    }
                    $newTotalOfAmount = $totalOfAmount->minus($usableAmount);

                    $transaction = new Transaction(
                        $user,
                        $credit,
                        TransactionActionType::CreditUse,
                        $newTotalOfAmount->isLessThanOrEqualTo(BigDecimal::zero()) ? $totalOfAmount : $usableAmount,
                    );
                    $entityManager->persist($transaction);
                    if ($newTotalOfAmount->isLessThanOrEqualTo(BigDecimal::zero())) {
                        break;
                    }
                    $totalOfAmount = $newTotalOfAmount;
                }
            }
        );
        $this->entityManager->clear();
    }
}
