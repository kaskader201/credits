<?php

namespace App\Facade;

use App\Exception\BalanceToLowException;
use App\Exception\UserNotFoundException;
use App\Input\GetBalanceInput;
use App\Input\UseCreditInput;
use App\Service\ActualBalanceService;
use App\Service\UseCreditService;
use Brick\Math\Exception\MathException;

class UseCreditFacade
{
    public function __construct(
        private ActualBalanceService $actualBalanceService,
        private UseCreditService $useCreditService,
    ) {
    }

    /**
     * @throws BalanceToLowException
     * @throws UserNotFoundException
     * @throws MathException
     */
    public function useCredits(
        UseCreditInput $input
    ): void {
        $balance = $this->actualBalanceService->getBalance(GetBalanceInput::fromUseCreditInput($input));
        if ($balance->isLessThan($input->amount)) {
            throw BalanceToLowException::create($balance);
        }

        $this->useCreditService->useCredit($input->amount, $input->userExternalId);
    }
}
