<?php

namespace SN1054\Timeset;

use DateTimeImmutable;
use DateInterval;
use Stringable;

abstract class Boundary implements Stringable
{
    protected DateTimeImmutable|string $point;
    protected bool $included;

    public const MINUS_INFINITY = 'minus_infinity';
    public const PLUS_INFINITY = 'plus_infinity';

    abstract public function lessThan(Boundary $boundary): bool;

    abstract public function isInfinite(): bool;

    abstract public function invert(): self;

    abstract public function add(DateInterval $interval): self;

    public function point(): DateTimeImmutable|string
    {
        return $this->point;
    }

    public function included(): bool
    {
        return $this->included;
    }

    public function equal(Boundary $boundary): bool
    {
        return $boundary->point() == $this->point() && $boundary->included() == $this->included();
    }

    public function greaterThan(Boundary $boundary): bool
    {
        return (!$this->equal($boundary) && !$this->lessThan($boundary));
    }

    public function greaterThanOrEqual(Boundary $boundary): bool
    {
        return !$this->lessThan($boundary);
    }

    public function lessThanOrEqual(Boundary $boundary): bool
    {
        return $this->lessThan($boundary) || $this->equal($boundary);
    }

    public function __toString(): string
    {
        $str = ($this->point instanceof DateTimeImmutable)
            ? $this->point->format(DateTimeImmutable::ATOM)
            : $this->point;

        //TODO implement inclusion
        //$str .= "..$this->included";

        return $str;
    }
}
