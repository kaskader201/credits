<?php

declare(strict_types=1);

namespace App\Provider;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeProvider
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

}
