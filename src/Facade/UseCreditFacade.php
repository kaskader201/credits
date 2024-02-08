<?php

namespace App\Facade;

use App\Exception\BalanceToLowException;
use App\Service\ActualBalanceService;
use App\Service\UseCreditService;
use Ramsey\Uuid\UuidInterface;
use Brick\Math\BigDecimal;

class UseCreditFacade
{

    public function __construct(
        private ActualBalanceService $actualBalanceService,
        private UseCreditService $useCreditService,
    ) {
    }

    public function useCredits(
        BigDecimal $amount,
        UuidInterface $userExternalId,
    ): void {
        $balance = $this->actualBalanceService->getBalance($userExternalId);
        if ($balance->isLessThan($amount)) {
            throw BalanceToLowException::create($balance);
        }

        $this->useCreditService->useCredit($amount, $userExternalId);
    }
}
