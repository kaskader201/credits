<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Credit;
use App\Entity\Id\Request\RequestUuid;
use App\Entity\Request;
use App\Entity\Transaction;
use App\Enum\RequestOperation;
use App\Exception\LogicException;
use App\Input\AddCreditInput;
use App\Input\GetBalanceInput;
use App\Input\UseCreditInput;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Brick\Math\BigDecimal;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RequestService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createRequest(GetBalanceInput|AddCreditInput|UseCreditInput $input): RequestUuid
    {
        $this->entityManager->clear();
        $uuid = $this->entityManager->wrapInTransaction(function () use ($input): RequestUuid {
            $user = $this->userRepository->getByExternalId($input->userExternalId);
            if ($input instanceof GetBalanceInput) {
                $request = Request::createNeutral($input->requestId, $user);
            } elseif ($input instanceof AddCreditInput) {
                $request = new Request(
                    $input->requestId,
                    $input->amount,
                    RequestOperation::Income,
                    $user,
                    $input->jsonSerialize(),
                );
            } elseif ($input instanceof UseCreditInput) {
                $request = new Request(
                    $input->requestId,
                    $input->amount->abs()->negated(),
                    RequestOperation::Outcome,
                    $user,
                    $input->jsonSerialize(),
                );
            } else {
                throw new LogicException('Invalid request input type');
            }
            $this->entityManager->persist($request);
            return $request->id;
        });
        $this->entityManager->clear();
        return $uuid;
    }
}
