<?php

namespace SN1054\Timeset;

use DateTimeInterface;
use DateTimeImmutable;
use DateInterval;
use Exception;

class LeftBoundary extends Boundary
{
    public function __construct(DateTimeInterface|string $point, bool $included = true)
    {
        if (is_string($point)) {
            if ($point !== self::MINUS_INFINITY) {
                throw new Exception();
            }
            $this->point = $point;

            $included = false;
        } else {
            $this->point = DateTimeImmutable::createFromInterface($point);
        }

        $this->included = $included;
    }

    public function isInfinite(): bool
    {
        return $this->point === self::MINUS_INFINITY;
    }

    public function invert(): RightBoundary
    {
        return new RightBoundary(
            $this->point,
            !$this->included
        );
    }

    /**
     * @psalm-suppress PossiblyInvalidMethodCall
     */
    public function add(DateInterval $interval): self
    {
        return $this->isInfinite()
            ? clone $this
            : new self($this->point->add($interval), $this->included);
    }

    public function lessThan(Boundary $boundary): bool
    {
        if ($boundary->point() === self::MINUS_INFINITY) {
            return false;
        }

        if (
            $this->isInfinite()
            || $boundary->point() === self::PLUS_INFINITY
            || $this->point < $boundary->point()
        ) {
            return true;
        }

        if ($boundary instanceof LeftBoundary) {
            return $this->point == $boundary->point() && $this->included && !$boundary->included();
        }

        return false;
    }
}
