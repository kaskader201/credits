<?php

declare(strict_types=1);

namespace App\Native;

use App\Entity\Id\Request\RequestUuid;
use App\Entity\Id\User\UserUuid;
use App\Enum\TransactionActionType;
use App\Exception\BalanceToLowException;
use App\Input\UseCreditInput;
use Brick\Math\BigDecimal;
use Doctrine\ORM\EntityManagerInterface;

class NativeUseCredit
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NativeRepository $repository,
    ) {
    }

    public function useCredits(UseCreditInput $input): void
    {
        $userId = $this->repository->getUserIdByExternalId($input->userExternalId);

        $balance = $this->repository->getBalance($userId);
        if ($balance->isLessThan($input->amount)) {
            throw BalanceToLowException::create($balance);
        }

        $this->entityManager->beginTransaction();
        try {
            $requestId = $this->repository->createRequest($input, $userId);

            $this->processExpiredCredits($userId, $requestId);

            $credits = $this->repository->getAllUsableCreditsWitUsableAmountSorted($userId);

            $totalOfAmount = $input->amount->abs();
            foreach ($credits as $row) {
                $usableAmount = $row['amount'];
                $creditId = $row['creditId'];
                if ($totalOfAmount->isGreaterThanOrEqualTo($usableAmount)) {
                    $this->repository->markCreditAsFullyUsed($creditId);
                }
                $newTotalOfAmount = $totalOfAmount->minus($usableAmount);
                $this->repository->createTransaction(
                    TransactionActionType::CreditUse->value,
                    $newTotalOfAmount->isLessThanOrEqualTo(BigDecimal::zero()) ? $totalOfAmount : $usableAmount,
                    $creditId,
                    $userId,
                    $requestId,
                );

                if ($newTotalOfAmount->isLessThanOrEqualTo(BigDecimal::zero())) {
                    break;
                }
                $totalOfAmount = $newTotalOfAmount;
            }

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function processExpiredCredits(UserUuid $userId, RequestUuid $requestId): void
    {
        $expiredCredits = $this->repository->findExpiredCreditsWithRemainingAmount($userId);

        foreach ($expiredCredits as $expiredCredit) {
           $this->repository->createTransaction(
               TransactionActionType::Expiration->value,
               $expiredCredit['amount'],
               $expiredCredit['creditId'],
               $userId,
               $requestId,
           );
        }
    }
}
