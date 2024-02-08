<?php

namespace App\Doctrine;

use App\Entity\Entity;
use App\Exception\LogicException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;

abstract class Uuid7Type extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?TypedEntityUuid {
        if ($value === null) {
            return null;
        }

        if ($value instanceof TypedEntityUuid) {
            return $value;
        }

        $uuidClass = static::getUuidClass();
        $content = stream_get_contents($value);
        if ($content === false) {
            throw new LogicException('Content is not e resource');
        }
        return $uuidClass::wrap(Uuid::fromBytes($content));
    }

    public function convertToDatabaseValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?string {
        if ($value === null) {
            return null;
        }

        if ($value instanceof TypedEntityUuid) {
            return $value->getBytes();
        }
        if (is_a($value, Entity::class)) {
            return $value->getId()->getBytes();
        }

        try {
            return Uuid::fromBytes($value)->getBytes();
        } catch (\Throwable $e) {
            throw LogicException::fromException($e);
        }
    }

    abstract public static function getUuidClass(): string;

    public function getSQLDeclaration(
        array $column,
        AbstractPlatform $platform,
    ): string {
        return $platform->getBinaryTypeDeclarationSQL([
            'length' => 16,
            'fixed' => true,
        ]);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::BINARY;
    }
}
