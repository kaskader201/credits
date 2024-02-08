<?php

declare(strict_types=1);

namespace App\Provider;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeProvider
{
    public const FORMAT = 'Y-m-d H:i:s';
    public const FORMAT_TZ = 'Y-m-d\TH:i:sP';
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

}
