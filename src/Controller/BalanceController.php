<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserNotFoundException;
use App\Input\GetBalanceInput;
use App\Service\ActualBalanceService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class BalanceController extends AbstractController
{
    public function __construct(private ActualBalanceService $actualBalanceService, private LoggerInterface $logger)
    {
    }

    #[Route(path: 'v1/balance', name: 'get_balance', methods: ['GET'])]
    public function getBalanceAction(#[MapQueryParameter] string $userExternalId, #[MapQueryParameter] string $requestId): Response
    {
        try {
            $balance = $this->actualBalanceService->getBalance(
                new GetBalanceInput($requestId, $userExternalId),
                null,
            );
            return new JsonResponse(['balance' => $balance], Response::HTTP_OK);
        } catch (UserNotFoundException) {
            return new JsonResponse("Unknown user `{$userExternalId}`", Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return new JsonResponse('Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
