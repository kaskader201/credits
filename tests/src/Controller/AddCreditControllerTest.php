<?php

namespace App\Tests\src\Controller;

use App\Entity\User;
use App\Enum\CreditPriority;
use App\Enum\CreditType;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AddCreditControllerTest extends WebTestCase
{
    public function testAddCreditAction(): void
    {

        $client = self::createClient([], ['CONTENT_TYPE' => 'application/json']);
        $client->catchExceptions(false);

        $externalId = Uuid::uuid4();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $entityManager->persist(new User($externalId));
        $entityManager->flush();
        $entityManager->clear();

        $client->request(
            'POST',
            'v1/credit',
            [
                'amount' => 500,
                'userExternalId' => $externalId,
                'creditPriority' => CreditPriority::FirstPriority->value,
                'type' => CreditType::Refund->value,
                'expiredAt' => null,
                'note' => null,
            ],
        );
        self::assertSame(201, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

    }
}
