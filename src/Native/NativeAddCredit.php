<?php

namespace App\Native;

use App\Entity\Id\Request\RequestUuid;
use App\Entity\Id\User\UserUuid;
use App\Enum\TransactionActionType;
use App\Input\AddCreditInput;
use Doctrine\ORM\EntityManagerInterface;

class NativeAddCredit
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NativeRepository $repository,
    ) {
    }

    public function addCredit(AddCreditInput $inputData): void
    {
        $userId = $this->repository->getUserIdByExternalId($inputData->userExternalId);
        $this->entityManager->beginTransaction();
        try {
            $requestId = $this->repository->createRequest($inputData, $userId);

            $this->processExpiredCredits($userId, $requestId);

            $creditId = $this->repository->createCredit(
                $inputData,
                $userId,
            );

            $this->repository->createTransaction(
                TransactionActionType::Addition->value,
                $inputData->amount,
                $creditId,
                $userId,
                $requestId,
            );
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
