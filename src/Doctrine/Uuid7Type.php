<?php

namespace App\Doctrine;

use App\Entity\Entity;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LogicException;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\Uuid;

/**
 * @template T of TypedEntityUuid
 */
abstract class Uuid7Type extends Type
{
    /**
     * @param string|T|null $value
     * @return T|null
     */
    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?TypedEntityUuid
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof TypedEntityUuid) {
            return $value;
        }

        $uuidClass = static::getUuidClass();
        return $uuidClass::wrap(Uuid::fromBytes(stream_get_contents($value)));
    }

    public function convertToDatabaseValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof TypedEntityUuid) {
            return $value->getBytes();
        }
        if (is_a($value, Entity::class)) {
            return $value->id->getBytes();
        }
        if (is_string($value)) {
            try {
                /** @throws UuidExceptionInterface */
                return Uuid::fromBytes($value)->getBytes();
            } catch (UuidExceptionInterface $e) {
                throw new LogicException('Trying to fetch entity by invalid (or not binary-uuid) string ' . $value, $e);
            }
        }

        throw new LogicException('Unexpected UUID 7 value: ' . get_debug_type($value));
    }

    /**
     * @return class-string<T>
     */
    abstract public static function getUuidClass(): string;

    public function getSQLDeclaration(
        array $column,
        AbstractPlatform $platform,
    ): string
    {
        return $platform->getBinaryTypeDeclarationSQL([
            'length' => 16,
            'fixed' => true,
        ]);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::BINARY;
    }

}
