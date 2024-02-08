<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\TypedEntityUuid;

interface Entity
{
    public function getId(): TypedEntityUuid;
}
