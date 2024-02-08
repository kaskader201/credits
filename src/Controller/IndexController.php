<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Request;
use App\Entity\Transaction;
use App\Entity\User;
use App\Provider\DateTimeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        /** @var User[] $users */
        $users = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
        $body .= '<h1>Uživatele</h1><table>
                    <tr>
                        <th>id</th>
                        <th>external_id</th>
                        <th>createdAt</th>
                    </tr>';
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
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
        $body .= '<h1>Kredity</h1><table>
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
                        <th>ExpiredAmount</th>
                    </tr>';
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
                        <td>{$credit->getExpiredAmount()}</td>
                    </tr>";
        }
        $body .= '</table>';

        /** @var Transaction[] $transactions */
        $transactions = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Transaction::class, 't')
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();

        $body .= '<h1>Transakce</h1><table>
                    <tr>
                        <th>id</th>
                        <th>user_id</th>
                        <th>credit_id</th>
                        <th>action</th>
                        <th>amount</th>
                        <th>RequestId</th>
                        <th>createdAt</th>
                    </tr>';
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
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        $body .= '<h1>Request log</h1><table>
                    <tr>
                        <th>id</th>
                        <th>user_id</th>
                        <th>RequestId</th>
                        <th>amount</th>
                        <th>operation</th>
                        <th>data</th>
                        <th>createdAt</th>
                    </tr>';
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
