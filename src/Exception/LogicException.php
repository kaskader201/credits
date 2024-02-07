<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

class LogicException extends \LogicException
{
    public function __construct(
        string $message,
        ?Throwable $previous = null,
    )
    {
        parent::__construct($message, 0, $previous);
    }

    public static function fromException(Throwable $e): self
    {
        return new self($e->getMessage(), $e);
    }

}
