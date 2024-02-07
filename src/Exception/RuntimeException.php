<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

abstract class RuntimeException extends \RuntimeException
{

    protected function __construct(
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

}
