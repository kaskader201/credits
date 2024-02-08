<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Id\Request\RequestUuid;
use App\Entity\Request;
use App\Entity\Transaction;
use App\Exception\LogicException;
use App\Exception\UserNotFoundException;
use App\Repository\CreditRepository;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;

final readonly class ExpirationCreditService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreditRepository $creditRepository,
        private CreditService $creditService,
        private UserRepository $userRepository,
        private RequestRepository $requestRepository,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function expireCredits(?UuidInterface $userExternalId, RequestUuid $requestUuid, ?DateTimeImmutable $now = null): void
    {
        $this->entityManager->clear();
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($userExternalId, $requestUuid, $now): void {
            if ($userExternalId === null) {
                $expiredCredits = $this->creditRepository->findAllUnUsedButExpiredForAll($now);
            } else {
                $user = $this->userRepository->getByExternalId($userExternalId);
                $expiredCredits = $this->creditRepository->findAllUnUsedButExpiredForUser($user, $now);
            }
            foreach ($expiredCredits as $credit) {
                $usableAmount = $this->creditService->getUsableAmountOfCredit($credit);
                if ($usableAmount->isLessThan(0)) {
                    throw new LogicException(
                        "Error on Expiration Credit {$credit->getId()->toString()}, Negative expired amount.",
                    );
                }
                $entityManager->persist(
                    Transaction::createExpiredAndMarkCreditAsExpired(
                        $credit,
                        $usableAmount,
                        $this->requestRepository->getById($requestUuid),
                    ),
                );
            }
        });
        $this->entityManager->clear();
    }
}
