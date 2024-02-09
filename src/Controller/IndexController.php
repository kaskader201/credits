<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Id\User\UserUuidType;
use App\Entity\Request;
use App\Entity\Transaction;
use App\Entity\User;
use App\Provider\DateTimeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function indexAction(): Response
    {
        $body = '<html><head>
                    <style>table {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

table td, table th {
  border: 1px solid #ddd;
  padding: 8px;
}

table tr:nth-child(even){background-color: #f2f2f2;}

table tr:hover {background-color: #ddd;}

table th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}</style>
                    </head><body>';

        $result = $this->entityManager->getConnection()->fetchAllAssociative('SELECT schemaname,relname,n_live_tup,
       pg_size_pretty(pg_total_relation_size(quote_ident(relname))) as sizeX,
        pg_size_pretty(pg_indexes_size(relid)) as "Index"
  FROM pg_stat_user_tables 
ORDER BY n_live_tup DESC;');

        $tableCount = count($result);
        $body .= "<h1>Tabulky ({$tableCount})</h1><table>
                    <tr>
                        <th>name</th>
                        <th>size total</th>
                        <th>size indexs</th>
                        <th>row count</th>
                    </tr>";
        foreach ($result as $row){
            $rows = number_format($row['n_live_tup']);
            $body .= "<tr>
                                <td>{$row['relname']}</td>
                                <td>{$row['sizex']}</td>
                                <td>{$row['Index']}</td>
                                <td>{$row['n_live_tup']}</td>
                            </tr>";
        }
        $body .= '</table>';

        /** @var User[] $users */
        $users = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();
        $userCount = count($users);
        $body .= "<h1>Uživatele ({$userCount})</h1><table>
                    <tr>
                        <th>id</th>
                        <th>externalId</th>
                        <th>rows</th>
                        <th>external_id</th>
                        <th>createdAt</th>
                    </tr>";
        foreach ($users as $user) {
            $countOfCredits = $this->entityManager->createQueryBuilder()
                ->select('count(c)')
                ->from(Credit::class, 'c')
                ->andWhere('c.user = :user')
                ->setParameter('user', $user->id, UserUuidType::NAME)
                ->getQuery()
                ->getSingleScalarResult();
            $countOfTransaction = $this->entityManager->createQueryBuilder()
                ->select('count(t)')
                ->from(Transaction::class, 't')
                ->andWhere('t.user = :user')
                ->setParameter('user', $user->id, UserUuidType::NAME)
                ->getQuery()
                ->getSingleScalarResult();
            $countOfRequest = $this->entityManager->createQueryBuilder()
                ->select('count(r)')
                ->from(Request::class, 'r')
                ->andWhere('r.user = :user')
                ->setParameter('user', $user->id, UserUuidType::NAME)
                ->getQuery()
                ->getSingleScalarResult();
            $body .= "<tr>
                        <td>{$user->id->toString()}</td>
                        <td>{$user->externalId}</td>
                        <td>Credits: {$countOfCredits} | Transaction: {$countOfTransaction} | Request {$countOfRequest}</td>
                        <td>{$user->externalId}</td>
                        <td>{$user->createdAt->format(DateTimeProvider::FORMAT)}</td>
                    </tr>";
        }
        $body .= '</table>';

        $body .= '</body></html>';
        return new Response($body, 200, ['Content-Type' => 'text/html']);
    }
    #[Route(path: '/detail', name: 'detail', methods: ['GET'])]
    public function detailAction(#[MapQueryParameter] string $userExternalId): Response
    {
        $body = '<html><head>
                    <style>table {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

table td, table th {
  border: 1px solid #ddd;
  padding: 8px;
}

table tr:nth-child(even){background-color: #f2f2f2;}

table tr:hover {background-color: #ddd;}

table th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}</style>
                    </head><body><a href="#credit">Kredity</a>|<a href="#transakce">Transakce</a>|<a href="#request">Request</a>';
        /** @var User $users */
        $user = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->andWhere('u.externalId =:externalid')
            ->setParameter('externalid', $userExternalId)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
        $users= [$user];
        $userCount = count($users);
        $body .= "<h1>Uživatele ({$userCount})</h1><table>
                    <tr>
                        <th>id</th>
                        <th>external_id</th>
                        <th>createdAt</th>
                    </tr>";
        foreach ($users as $user) {
            $body .= "<tr>
                        <td>{$user->id->toString()}</td>
                        <td>{$user->externalId}</td>
                        <td>{$user->createdAt->format(DateTimeProvider::FORMAT)}</td>
                    </tr>";
        }
        $body .= '</table>';

        /** @var Credit[] $credits */
        $credits = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Credit::class, 'c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user->id, UserUuidType::NAME)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        $creditsCount = count($credits);
        $body .= "<h1 id=\"credit\">Kredity ({$creditsCount})</h1><table>
                    <tr>
                        <th>id</th>
                        <th>user_id</th>
                        <th>amount</th>
                        <th>priorita</th>
                        <th>type</th>
                        <th>expiration date</th>
                        <th>is usable</th>
                        <th>createdAt</th>
                        <th>FullyUsedAt</th>
                    </tr>";
        foreach ($credits as $credit) {
            $usable = $credit->isUsable() ? 'yes' : 'no';
            $body .= "<tr>
                        <td>{$credit->id->toString()}</td>
                        <td>{$credit->user->id->toString()}</td>
                        <td>{$credit->amount}</td>
                        <td>{$credit->priority->value}</td>
                        <td>{$credit->type->value}</td>
                        <td>{$credit->expiredAt?->format(DateTimeProvider::FORMAT)}</td>
                        <td>{$usable}</td>
                        <td>{$credit->createdAt->format(DateTimeProvider::FORMAT)}</td>
                        <td>{$credit->getFullyUsedAt()?->format(DateTimeProvider::FORMAT)}</td>
                    </tr>";
        }
        $body .= '</table>';

        /** @var Transaction[] $transactions */
        $transactions = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Transaction::class, 't')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user->id, UserUuidType::NAME)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();

        $transactionsCount = count($transactions);
        $body .= "<h1 id=\"transakce\">Transakce ({$transactionsCount})</h1><table>
                    <tr>
                        <th>id</th>
                        <th>user_id</th>
                        <th>credit_id</th>
                        <th>action</th>
                        <th>amount</th>
                        <th>RequestId</th>
                        <th>createdAt</th>
                    </tr>";
        foreach ($transactions as $transaction) {
            $body .= "<tr>
                        <td>{$transaction->id->toString()}</td>
                        <td>{$transaction->user->id->toString()}</td>
                        <td>{$transaction->credit->id->toString()}</td>
                        <td>{$transaction->action->value}</td>
                        <td>{$transaction->amount}</td>
                        <td>{$transaction->request->id->toString()}</td>
                        <td>{$transaction->createdAt->format(DateTimeProvider::FORMAT)}</td>
                    </tr>";
        }
        $body .= '</table>';

        /** @var Request[] $requests */
        $requests = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Request::class, 'r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user->id, UserUuidType::NAME)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
        $requestsCount = count($requests);
        $body .= "<h1 id=\"request\">Request log ({$requestsCount})</h1><table>
                    <tr>
                        <th>id</th>
                        <th>user_id</th>
                        <th>RequestId</th>
                        <th>amount</th>
                        <th>operation</th>
                        <th>data</th>
                        <th>createdAt</th>
                    </tr>";
        foreach ($requests as $request) {
            $body .= "<tr>
                        <td>{$request->id->toString()}</td>
                        <td>{$request->user->id->toString()}</td>
                        <td>{$request->requestId}</td>
                        <td>{$request->amount}</td>
                        <td>{$request->operation->value}</td>
                        <td>{$request->getDataAsJson()}</td>
          
                        <td>{$request->createdAt->format(DateTimeProvider::FORMAT)}</td>
                    </tr>";
        }
        $body .= '</table>';

        $body .= '</body></html>';
        return new Response($body, 200, ['Content-Type' => 'text/html']);
    }
}
