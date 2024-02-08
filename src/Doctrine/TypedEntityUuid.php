<?php

declare(strict_types=1);

namespace App\Doctrine;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class TypedEntityUuid
{
    private UuidInterface $original;

    final private function __construct(?UuidInterface $original = null)
    {
        $this->original = $original ?? Uuid::uuid7();
    }

    public static function generate(): static
    {
        return new static();
    }
    public static function wrap(UuidInterface $original): static
    {
        return new static($original);
    }
    public function toString(): string
    {
        return $this->original->toString();
    }

    public function getBytes(): string
    {
        return $this->original->getBytes();
    }

    /**
     * @internal should be only called internally by Doctrine, use getBytes() if you need to get the raw bytes
     */
    public function __toString(): string
    {
        return $this->getBytes();
    }
}
