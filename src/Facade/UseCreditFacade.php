<?php

namespace App\Facade;

use App\Exception\BalanceToLowException;
use App\Exception\UserNotFoundException;
use App\Input\GetBalanceInput;
use App\Input\UseCreditInput;
use App\Service\ActualBalanceService;
use App\Service\RequestService;
use App\Service\UseCreditService;
use Brick\Math\Exception\MathException;

class UseCreditFacade
{
    public function __construct(
        private ActualBalanceService $actualBalanceService,
        private UseCreditService $useCreditService,
        private RequestService $requestService,
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
        $requestUuid = $this->requestService->createRequest($input);
        $balance = $this->actualBalanceService->getBalance(GetBalanceInput::fromUseCreditInput($input), $requestUuid);
        if ($balance->isLessThan($input->amount)) {
            throw BalanceToLowException::create($balance);
        }

        $this->useCreditService->useCredit($input->amount, $input->userExternalId, $requestUuid);
    }
}
