<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\CreditPriority;
use App\Enum\CreditType;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Ramsey\Uuid\v1;

class GenerateData extends Command
{

    public function __construct(private EntityManagerInterface $em,  private HttpClientInterface $client,)
    {
        parent::__construct('generate:data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $numberOfUsers = 100;
        $numberOfActionPerUser = 500;


        $externalIds = $this->em->createQueryBuilder()
            ->select('u.externalId')
            ->from(User::class, 'u')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        if (count($externalIds) < $numberOfUsers) {
//            $externalId = 'bd067e8b-4b2d-4c6e-9d63-56971bbbe239';

            for ($y = 0; $y < $numberOfUsers; $y++) {
                $externalId = Uuid::uuid4()->toString();
                $this->createUser($externalId);
                $externalIds[] = $externalId;

            }
        }
        $total = $numberOfActionPerUser * $numberOfUsers;
        $progressBar = new ProgressBar($output, $total);
        $progressBar->setFormat('very_verbose');
        for ($i = 0; $i < $total; $i++) {
            $randomNumber = rand(1, 4);
            $externalId = $externalIds[rand(0, $numberOfUsers - 1)];
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
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    private function createUser(string $externalId): void
    {
        $client = $this->client;
        $client->request(
            'POST',
            'http://localhost/v1/user',
            [
                'body' =>json_encode([
                'userExternalId' => $externalId,
                ]),
            ],
        );
    }

    private function spend(string $externalId): void
    {
        $amount = rand(1, 500);
        $client = $this->client;
        try {
           $client->request(
                'POST',
                'http://localhost/v1/credit/spend',
                [
                    'body' => [
                        'amount' => $amount,
                        'userExternalId' => $externalId,
                        'requestId' => uniqid("req_"),
                    ],
                ],
            );
        } catch (\Throwable $e){
          $this->addCredit($externalId);
          $this->spend($externalId);
        }

    }

    private function addCredit(string $externalId): void
    {
        $client = $this->client;
        $client->request(
            'POST',
            'http://localhost/v1/credit',
            [
                'body' => [
                    'amount' => rand(1, 200),
                    'userExternalId' => $externalId,
                    'creditPriority' => CreditPriority::FirstPriority->value,
                    'type' => CreditType::Refund->value,
                    'expiredAt' => null,
                    'note' => null,
                    'requestId' => uniqid("req_"),
                ],
            ],
        );
    }

    private function addExpiredCredit(string $externalId): void
    {
        $client = $this->client;
        $client->request(
            'POST',
            'http://localhost/v1/credit',
            [
                'body' =>[
                'amount' => rand(1, 200),
                'userExternalId' => $externalId,
                'creditPriority' => CreditPriority::SecondPriority->value,
                'type' => CreditType::Marketing->value,
                'expiredAt' => date('Y-m-d H:i:s', strtotime('+' . rand(1, 120) . ' second')),
                'note' => null,
                'requestId' => uniqid("req_"),
                ],
            ],
        );
    }
}
