<?php

declare(strict_types=1);

namespace App\Facade;

use App\Entity\User;
use App\Exception\UserAlreadyExistException;
use App\Exception\UserNotFoundException;
use App\Input\AddCreditInput;
use App\Input\CreateUserInput;
use App\Repository\UserRepository;
use App\Service\AddCreditService;
use App\Service\ExpirationCreditService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserFacade
{

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws UserAlreadyExistException
     */
    public function createNewUser(CreateUserInput $inputData): void
    {
        try {
            $this->userRepository->getByExternalId($inputData->userExternalId);
            throw UserAlreadyExistException::byExternalId($inputData->userExternalId);
        } catch (UserNotFoundException) {
            $this->entityManager->wrapInTransaction(
                static function (EntityManagerInterface $entityManager) use ($inputData): void {
                    $entityManager->persist(new User($inputData->userExternalId->toString()));
                }
            );
        }
    }

}
