<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\BalanceToLowException;
use App\Exception\UserNotFoundException;
use App\Facade\UseCreditFacade;
use App\Input\UseCreditInput;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class UseCreditController extends AbstractController
{
    public function __construct(private UseCreditFacade $useCreditFacade, private LoggerInterface $logger)
    {
    }

    #[Route(path: 'v1/credit/spend', name: 'useCredit', methods: ['POST'])]
    public function useCreaditAction(#[MapRequestPayload] UseCreditInput $input): Response
    {
        try {
            $this->useCreditFacade->useCredits($input);
            return new JsonResponse('Ok', Response::HTTP_CREATED);
        } catch (UserNotFoundException) {
            return new JsonResponse("Unknown user `{$input->userExternalId->toString()}`", Response::HTTP_NOT_FOUND);
        } catch (BalanceToLowException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return new JsonResponse('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
