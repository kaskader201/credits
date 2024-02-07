<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use App\Exception\UserNotFoundException;
use App\Repository\CreditRepository;
use App\Repository\UserRepository;
use App\Uuid\UuidInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ExpirationCreditService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreditRepository $creditRepository,
        private CreditService $creditService,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function expireCredits(?UuidInterface $userExternalId, ?DateTimeImmutable $now = null): void
    {
        $this->entityManager->clear();
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($userExternalId, $now): void {
            if ($userExternalId === null) {
                $expiredCredits = $this->creditRepository->findAllUnUsedButExpiredForAll($now);
            } else {
                $user = $this->userRepository->getByExternalId($userExternalId);
                $expiredCredits = $this->creditRepository->findAllUnUsedButExpiredForUser($user, $now);
            }

            foreach ($expiredCredits as $credit){
                $usableAmount = $this->creditService->getUsableAmountOfCredit($credit);
                $entityManager->persist(Transaction::createExpiredAndMarkCreditAsExpired($credit, $usableAmount));
            }
        });
        $this->entityManager->clear();
    }
}
