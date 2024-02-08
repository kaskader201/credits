<?php

declare(strict_types=1);

namespace App\Exception;

use Brick\Math\BigDecimal;

class BalanceToLowException extends RuntimeException
{

    public static function create(BigDecimal $actualBalance): self
    {
        return new self("Insufficient balance. Actual balance is: {$actualBalance}.");
    }

}
