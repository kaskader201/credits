<?php

namespace App\Tests\src;

use App\Enum\CreditPriority;
use App\Enum\CreditType;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GenerateRandomDataTest extends WebTestCase
{

    /**
     * @group a
     */
    public function testGenerateRandomData(): void
    {
        $client = self::createClient([], ['CONTENT_TYPE' => 'application/json']);
        $client->catchExceptions(false);

        $numberOfUsers = 100;
        $numberOfTransaction = 50000000;

        $externalId = 'bd067e8b-4b2d-4c6e-9d63-56971bbbe239';
        $externalIds = [];
        for ($y = 0; $y < $numberOfUsers; $y++) {
            $this->createUser($externalId);
            $externalIds[] = $externalId;
            $externalId = Uuid::uuid4()->toString();
        }

        for ($i = 0; $i < $numberOfTransaction * $numberOfUsers; $i++) {
            $randomNumber = rand(1, 4);
            $externalId = $externalIds[rand(0, $numberOfUsers-1)];
            switch ($randomNumber) {
                case 1:
                    $this->addCredit($externalId);
                    break;
                case 2:
                    $this->spend($externalId);
                    break;
                case 3:
                    $this->addExpiredCredit($externalId);
                    break;
                default:
                    if (rand(0, 1) === 1) {
                        $this->addExpiredCredit($externalId);
                    } else {
                        $this->spend($externalId);
                    }
            }
        }

    }

    private function createUser($externalId): void
    {
        $client = self::getContainer()->get('test.client');
        $client->catchExceptions(false);
        $client->jsonRequest(
            'POST',
            'v1/user',
                [
                    'userExternalId' => $externalId,
                ],
        );
    }

    private function spend($externalId): void
    {
        $client = self::getContainer()->get('test.client');
        $client->catchExceptions(false);
        $amount = rand(1, 500);
        $client->jsonRequest(
            'POST',
            'v1/credit/spend',[
                'amount' => $amount,
                'userExternalId' => $externalId,
                'requestId' => uniqid("req_"),
            ],
        );
    }

    private function addCredit($externalId): void
    {
        $client = self::getContainer()->get('test.client');
        $client->catchExceptions(false);
        $client->jsonRequest(
            'POST',
            'v1/credit',[
                'amount' => rand(1, 200),
                'userExternalId' => $externalId,
                'creditPriority' => CreditPriority::FirstPriority->value,
                'type' => CreditType::Refund->value,
                'expiredAt' => null,
                'note' => null,
                'requestId' => uniqid("req_"),
            ],
        );
    }

    private function addExpiredCredit($externalId): void
    {
        $client = self::getContainer()->get('test.client');
        $client->catchExceptions(false);
        $client->jsonRequest(
            'POST',
            'v1/credit',
            [
                'amount' => rand(1, 200),
                'userExternalId' => $externalId,
                'creditPriority' => CreditPriority::SecondPriority->value,
                'type' => CreditType::Marketing->value,
                'expiredAt' => date('Y-m-d H:i:s', strtotime('+' . rand(1, 120) . ' second')),
                'note' => null,
                'requestId' => uniqid("req_"),
            ],
        );
    }
}
