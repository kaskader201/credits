<?php

declare(strict_types=1);

namespace App\Doctrine;

use Brick\Math\BigDecimal;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use UnexpectedValueException;

class BigDecimalType extends Type
{
    public const NAME = 'big_decimal';
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDecimalTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     *
     * @return BigDecimal|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        return BigDecimal::of($value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BigDecimal) {
            return (string)$value;
        }

        throw new UnexpectedValueException(BigDecimal::class, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
