<?php

namespace App\Service;

use App\Entity\Id\Request\RequestUuid;
use App\Entity\Request;
use App\Entity\Transaction;
use App\Enum\TransactionActionType;
use App\Exception\UserNotFoundException;
use App\Repository\CreditRepository;
use App\Repository\RequestRepository;
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
        private RequestRepository $requestRepository,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function useCredit(
        BigDecimal $amount,
        UuidInterface $userExternalId,
        RequestUuid $requestUuid,
    ): void {
        $this->entityManager->clear();
        $this->entityManager->wrapInTransaction(
            function (EntityManagerInterface $entityManager) use (
                $userExternalId,
                $amount,
                $requestUuid,
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
                        $this->requestRepository->getById($requestUuid),
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
