<?php

namespace SN1054\Timeset;

abstract class Boundary
{
    private DateTimeImmutable|string $point;
    private bool $included;

    public const MINUS_INFINITY = 'minus_infinity';
    public const PLUS_INFINITY = 'plus_infinity';

    abstract public function smallerThan(Boundary $boundary): bool;

    public function point(): DateTimeImmutable|string
    {
        return $this->point;
    }

    public function included(): bool
    {
        return $this->included;
    }

    public function equals(Boundary $boundary): bool
    {
        return $boundary->point() == $this->point() && $boundary->included() == $this->included();
    }

    public function biggerThan(Boundary $boundary): bool
    {
        return (!$this->equals($boundary) && !$this->smallerThan($boundary));
    }
}
