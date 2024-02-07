<?php

namespace App\Service;

use App\Exception\UserNotFoundException;
use App\Repository\CreditRepository;
use App\Repository\UserRepository;
use App\Uuid\UuidInterface;
use Brick\Math\BigDecimal;

final readonly class ActualBalanceService
{
    public function __construct(
        private CreditRepository $creditRepository,
        private UserRepository $userRepository,
        private CreditService $creditService,
        private ExpirationCreditService $expirationCreditService,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function getBalance(UuidInterface $userExternalId): BigDecimal
    {
        $this->expirationCreditService->expireCredits($userExternalId);
        return $this->calculateBalance($userExternalId);
    }

    /**
     * @throws UserNotFoundException|\Brick\Math\Exception\MathException
     */
    private function calculateBalance(UuidInterface $userExternalId): BigDecimal
    {
        $user = $this->userRepository->getByExternalId($userExternalId);
        $usableCredits = $this->creditRepository->findAllUsable($user);

        $balance = BigDecimal::zero();
        foreach ($usableCredits as $credit) {
            $usableAmount = $this->creditService->getUsableAmountOfCredit($credit);
            $balance = $balance->plus($usableAmount);
        }

        return $balance;
    }
}
