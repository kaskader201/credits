<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserAlreadyExistException;
use App\Exception\UserNotFoundException;
use App\Facade\AddCreditFacade;
use App\Facade\UserFacade;
use App\Input\AddCreditInput;
use App\Input\CreateUserInput;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;


class UserController extends AbstractController
{
    public function __construct(private UserFacade $userFacade, private LoggerInterface $logger)
    {
    }


    #[Route(path: 'v1/user', name: 'create_user', methods: ['POST'])]
    public function createUserAction(#[MapRequestPayload] CreateUserInput $input): Response
    {
        try {
            $this->userFacade->createNewUser($input);
            return new Response('Ok', Response::HTTP_CREATED);
        } catch (UserAlreadyExistException) {
            return new Response("User `{$input->userExternalId->toString()}` already exist.", Response::HTTP_BAD_REQUEST);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
