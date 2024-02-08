<?php

namespace App\Uuid;

class Uuid extends \Ramsey\Uuid\Uuid implements UuidInterface
{
    public static function fromString(string $uuid): UuidInterface
    {
        return parent::fromString($uuid);
//        new self(
//            $result->getFields(),
//            $result->getNumberConverter(),
//            $result->getC,
//        );
    }
}
