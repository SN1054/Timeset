<?php

namespace SN1054\Timeset;

abstract class Boundary
{
    public const MINUS_INFINITY = 'minus_infinity';
    public const PLUS_INFINITY = 'plus_infinity';

    abstract public function point(): DateTimeImmutable|string;
    abstract public function smallerThan(Boundary $boundary): bool;
    abstract public function biggerThan(Boundary $boundary): bool;

    public function equals(Boundary $boundary): bool
    {
        return $boundary == $this;
    }
}
