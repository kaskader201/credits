<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Credit;
use App\Entity\Transaction;
use App\Exception\LogicException;
use App\Repository\TransactionRepository;
use Brick\Math\BigDecimal;

final readonly class CreditService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function getUsableAmountOfCredit(Credit $credit): BigDecimal
    {
        $transactions = $this->transactionRepository->findAllUseOfCredit($credit);

        // There must always be a positive transaction
        if (count($transactions) === 0) {
            throw new LogicException("Credit {$credit->id->toString()} has no transaction.");
        }
        if (count($transactions) === 1) {
            return $transactions[0]->amount;
        }

        $amounts = array_map(
            fn(Transaction $transaction): BigDecimal => $transaction->amount,
            $transactions,
        );

        return BigDecimal::sum(...$amounts);
    }
}
