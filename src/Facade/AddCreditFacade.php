<?php

declare(strict_types=1);

namespace App\Facade;

use App\Exception\UserNotFoundException;
use App\Input\AddCreditInput;
use App\Service\AddCreditService;
use App\Service\ExpirationCreditService;

final readonly class AddCreditFacade
{
    public function __construct(
        private AddCreditService $addCreditService,
        private ExpirationCreditService $expirationCreditService,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function addCredits(AddCreditInput $inputData): void
    {
        $this->expirationCreditService->expireCredits($inputData->userExternalId);

        $this->addCreditService->addCredit(
            $inputData->amount,
            $inputData->userExternalId,
            $inputData->creditPriority,
            $inputData->type,
            $inputData->expiredAt,
            $inputData->note,
        );
    }
}
