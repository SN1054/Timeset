<?php

namespace SN1054\Timeset;

use DateTimeImmutable;
use Exception;
use DateTimeInterface;

abstract class Set
{
    public const INFINITY = 'infinity';
    abstract public function or(Set $set): Set;
    abstract public function and(Set $set): Set;
    abstract public function xor(Set $set): Set;
    abstract public function not(): Set;
    abstract public function toArray(): array;

    public function isEmpty(): bool
    {
        return static::class === EmptySet::class;
    }

    public static function create(array $values): Set
    {
        $sets = [];

        foreach ($values as $value) {
            if (is_string($value)) {
                if (!self::stringHasValidFormat($value)) {
                    throw new Exception();
                }

                $sets[] = ConnectedSet::createPoint(new DateTimeImmutable($value));

                continue;
            }

            if ($value instanceof DateTimeInterface) {
                $sets[] = ConnectedSet::createPoint($value);

                continue;
            }

            if (is_array($value)) {
                if ((!self::stringHasValidFormat($value[0])
                        && !($value[0] instanceof DateTimeInterface)
                    ) || (!self::stringHasValidFormat($value[1])
                        && !($value[1] instanceof DateTimeInterface)
                    ) || count($value) !== 2
                ) {
                    throw new Exception();
                }

                $value[0] = is_string($value[0]) ? new DateTimeImmutable($value[0]) : $value[0];
                $value[1] = is_string($value[1]) ? new DateTimeImmutable($value[1]) : $value[1];

                $sets[] = new ConnectedSet(
                    new LeftBoundary($value[0]),
                    new RightBoundary($value[1])
                );

                continue;
            }

            if ($value instanceof Set) {
                switch ($value::class) {
                    case ConnectedSet::class:
                        $sets[] = $value;
                        break;
                    case DisconnectedSet::class:
                        $sets = array_merge($sets, $value->sets());
                }

                continue;
            }

            throw new Exception();
        }

        if (count($sets) === 0) {
            return new EmptySet();
        }

        $sets = self::normalize(...$sets);

        return count($sets) === 1
            ? $sets[0]
            : new DisconnectedSet(...$sets);
    }

    private static function stringHasValidFormat(string $string): bool
    {
        try {
            new DateTimeImmutable($string);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public static function normalize(ConnectedSet ...$sets): array
    {
        $sets = ConnectedSet::sort(...$sets);

        for ($i = 0; $i < array_key_last($sets); $i++) {
            if (false === $sets[$i]->and($sets[$i + 1])->isEmpty()) {
                $sets[$i + 1] = $sets[$i]->or($sets[$i + 1]);
                unset($sets[$i]);
            }
        }

        return array_values($sets);
    }
}
