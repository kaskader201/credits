<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserNotFoundException;
use App\Facade\AddCreditFacade;
use App\Input\AddCreditInput;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;


class BalanceController extends AbstractController
{
    public function __construct(private AddCreditFacade $addCreditFacade, private LoggerInterface $logger)
    {
    }

    #[Route(path: 'v1/credit', name: 'addCredit', methods: ['POST'])]
    public function addCreditAction(#[MapRequestPayload] AddCreditInput $input): Response
    {
        try {
            $this->addCreditFacade->addCredits($input);
            return new Response('Ok', Response::HTTP_CREATED);
        } catch (UserNotFoundException) {
            return new Response("Uknown user `{$input->userExternalId}`", Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}