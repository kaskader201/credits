<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Id\Request\RequestUuid;
use App\Entity\Id\Request\RequestUuidType;
use App\Entity\Request;
use App\Exception\LogicException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;

readonly class RequestRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }


    public function getById(RequestUuid $id): Request
    {
        $request = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Request::class, 'r')
            ->andWhere('r.id = :id')
            ->setParameter('id', $id, RequestUuidType::NAME)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if ($request === null || !$request instanceof Request) {
            throw new LogicException('Invalid request id.');
        }
        return $request;
    }
}
