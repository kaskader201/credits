<?php

declare(strict_types=1);

namespace App\Native;

use App\Entity\Id\Credit\CreditUuid;
use App\Entity\Id\Credit\CreditUuidType;
use App\Entity\Id\Request\RequestUuid;
use App\Entity\Id\Request\RequestUuidType;
use App\Entity\Id\Transaction\TransactionUuid;
use App\Entity\Id\Transaction\TransactionUuidType;
use App\Entity\Id\User\UserUuid;
use App\Entity\Id\User\UserUuidType;
use App\Enum\RequestOperation;
use App\Exception\LogicException;
use App\Exception\UserNotFoundException;
use App\Input\AddCreditInput;
use App\Input\GetBalanceInput;
use App\Input\UseCreditInput;
use App\Provider\DateTimeProvider;
use Brick\Math\BigDecimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class NativeRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function getUserIdByExternalId(UuidInterface $externalId): UserUuid
    {
        $userId = $this->entityManager->getConnection()->fetchOne(
            'SELECT id FROM "user" WHERE external_id = :id;',
            ['id' => $externalId->toString()],
        );
        if ($userId === false) {
            throw UserNotFoundException::byExternalId($externalId);
        }

        return UserUuid::wrap(Uuid::fromBytes(stream_get_contents($userId)));
    }

    public function getBalance(UserUuid $userId): BigDecimal
    {
        $amount = $this->entityManager->getConnection()->fetchOne(
            'SELECT SUM(t.amount) AS amount
                    FROM credit c
                             INNER JOIN transaction t ON (c.id = t.credit_id AND c.user_id = t.user_id)
                    WHERE c.usable = true
                      AND c.user_id = :userId;',
            ['userId' => $userId->getBytes()],
            ['userId' => UserUuidType::NAME],
        );
        return BigDecimal::of($amount);
    }

    /**
     * @return array<array{creditId:CreditUuid, amount:BigDecimal}>
     */
    public function findExpiredCreditsWithRemainingAmount(UserUuid $userId): array
    {
        $result = $this->entityManager->getConnection()->fetchAllAssociative(
            'SELECT c.id AS id, SUM(t.amount) AS amount
                    FROM credit c
                             INNER JOIN transaction t ON (c.id = t.credit_id AND c.user_id = t.user_id)
                    WHERE c.usable = true
                      AND c.expired_at <= now()
                      AND c.user_id = :userId
                    GROUP BY c.id
                    ORDER BY c.expired_at ASC;',
            ['userId' => $userId->getBytes()],
            ['userId' => UserUuidType::NAME],
        );

        $credits = [];
        foreach ($result as $row) {
            $creditId = CreditUuid::wrap(Uuid::fromBytes(stream_get_contents($row['id'])));
            $amount = BigDecimal::of($row['amount']);
            $credits[] = ['creditId' => $creditId, 'amount' => $amount];
        }
        return $credits;
    }

    /**
     * @return array<array{creditId:CreditUuid, amount:BigDecimal}>
     */
    public function getAllUsableCreditsWitUsableAmountSorted(UserUuid $userId): array
    {
        $result = $this->entityManager->getConnection()->fetchAllAssociative(
            'SELECT c.id AS id, SUM(t.amount) AS amount
                    FROM credit c
                             INNER JOIN transaction t ON (c.id = t.credit_id AND c.user_id = t.user_id)
                    WHERE c.usable = true
                      AND c.expired_at <= now()
                      AND c.user_id = :userId
                    GROUP BY c.id
                    ORDER BY c.priority ASC, c.expired_at ASC;',
            ['userId' => $userId->getBytes()],
            ['userId' => UserUuidType::NAME],
        );

        $credits = [];
        foreach ($result as $row) {
            if(!isset($row['id'])){
               var_dump($row); die();
            }
            $creditId = CreditUuid::wrap(Uuid::fromBytes(stream_get_contents($row['id'])));
            $amount = BigDecimal::of($row['amount']);
            $credits[] = ['creditId' => $creditId, 'amount' => $amount];
        }
        return $credits;
    }

    public function markCreditAsFullyUsed(CreditUuid $creditUuid): void
    {
        $this->entityManager->getConnection()->update(
            'credit',
            [
                'usable' => null,
                'fully_used_at' => (new \DateTimeImmutable()),
            ],
            [
                'id' => $creditUuid,
            ],
            [
                'id' => CreditUuidType::NAME,
                'fully_used_at' => Types::DATETIMETZ_IMMUTABLE,
            ]
        );
    }

    public function createCredit(AddCreditInput $inputData, UserUuid $userId): CreditUuid
    {
        $id = CreditUuid::generate();
        $this->entityManager->getConnection()->insert(
            'credit',
            [
                'id' => $id->getBytes(),
                'amount' => $inputData->amount->toFloat(),
                'priority' => $inputData->creditPriority->value,
                'type' => $inputData->type->value,
                'note' => $inputData->note,
                'expired_at' => $inputData->expiredAt,
                'usable' => true,
                'fully_used_at' => null,
                'expired_amount' => 0,
                'created_at' => new \DateTimeImmutable(),
                'user_id' => $userId->getBytes(),
            ],
            [
                'id' => CreditUuidType::NAME,
                'user_id' => UserUuidType::NAME,
                'expired_at' => Types::DATETIMETZ_IMMUTABLE,
                'created_at' => Types::DATETIMETZ_IMMUTABLE,
            ]
        );

        return $id;
    }

    public function createTransaction(string $action, BigDecimal $amount, CreditUuid $creditId, UserUuid $userId, RequestUuid $requestId): TransactionUuid
    {
        $id = TransactionUuid::generate();
        // todo: some checks
        $this->entityManager->getConnection()->insert(
            'transaction',
            [
                'id' => $id->getBytes(),
                'action' => $action,
                'amount' => $amount->toFloat(),
                'created_at' => (new \DateTimeImmutable())->format(DateTimeProvider::FORMAT_TZ),
                'user_id' => $userId->getBytes(),
                'credit_id' => $creditId->getBytes(),
                'request_id' => $requestId->getBytes(),
            ],
            [
                'id' => TransactionUuidType::NAME,
                'user_id' => UserUuidType::NAME,
                'credit_id' => CreditUuidType::NAME,
                'request_id' => RequestUuidType::NAME,
            ]
        );

        return $id;
    }

    public function createRequest(GetBalanceInput|AddCreditInput|UseCreditInput $input, UserUuid $userId): RequestUuid
    {
        $id = RequestUuid::generate();
        $amount = BigDecimal::zero();
        $operation = RequestOperation::Check;
        if (!$input instanceof GetBalanceInput) {
            $operation = RequestOperation::Income;
            $amount = $input->amount->abs();
        }
        if ($input instanceof UseCreditInput) {
            $amount = $amount->negated();
            $operation = RequestOperation::Outcome;
        }
        if ($operation === RequestOperation::Income && $amount->isLessThan(0)) {
            throw new LogicException('Income error');
        }
        if ($operation === RequestOperation::Outcome && $amount->isGreaterThan(0)) {
            throw new LogicException('Outcome error');
        }
        $this->entityManager->getConnection()->insert(
            'request',
            [
                'id' => $id->getBytes(),
                'request_id' => $input->requestId,
                'amount' => $amount->toFloat(),
                'operation' => $operation->value,
                'created_at' => (new \DateTimeImmutable())->format(DateTimeProvider::FORMAT_TZ),
                'data' => $input->jsonSerialize(),
                'user_id' => $userId->getBytes(),
            ],
            [
                'id' => RequestUuidType::NAME,
                'user_id' => UserUuidType::NAME,
                'data' => Types::JSON,
            ]
        );

        return $id;
    }
}
