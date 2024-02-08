<?php

namespace App\Service;

use App\Entity\Id\Request\RequestUuid;
use App\Exception\UserNotFoundException;
use App\Input\GetBalanceInput;
use App\Repository\CreditRepository;
use App\Repository\UserRepository;
use Brick\Math\Exception\MathException;
use Ramsey\Uuid\UuidInterface;
use Brick\Math\BigDecimal;

final readonly class ActualBalanceService
{
    public function __construct(
        private CreditRepository $creditRepository,
        private UserRepository $userRepository,
        private CreditService $creditService,
        private ExpirationCreditService $expirationCreditService,
        private RequestService $requestService,
    ) {
    }

    /**
     * @throws UserNotFoundException|MathException
     */
    public function getBalance(GetBalanceInput $input, ?RequestUuid $requestUuid): BigDecimal
    {
        if ($requestUuid === null) {
            $requestUuid = $this->requestService->createRequest($input);
        }
        $this->expirationCreditService->expireCredits($input->userExternalId, $requestUuid);
        return $this->calculateBalance($input->userExternalId);
    }

    /**
     * @throws UserNotFoundException|MathException
     */
    public function calculateBalance(UuidInterface $userExternalId): BigDecimal
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
