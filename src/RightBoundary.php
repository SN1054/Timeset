<?php

namespace SN1054\Timeset;

use DateTimeInterface;
use DateTimeImmutable;
use DateTime;
use DateInterval;
use Exception;

class RightBoundary extends Boundary
{
    public function __construct(DateTimeInterface|string $point, bool $included = true)
    {
        if (is_string($point)) {
            if ($point !== self::PLUS_INFINITY) {
                throw new Exception();
            }

            $included = false;
        }

        $this->point = $point instanceof DateTime 
            ? DateTimeImmutable::createFromMutable($point)
            : $point;

        $this->included = $included;
    }

    public function isInfinite(): bool
    {
        return $this->point === self::PLUS_INFINITY;
    }

    public function invert(): LeftBoundary
    {
        return new LeftBoundary(
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


    // TODO written poorly
    public function lessThan(Boundary $boundary): bool
    {
        if ($this->point == self::PLUS_INFINITY) {
            return false;
        }

        if ($this->point < $boundary->point()) {
            return true;
        }

        if ($this->equal($boundary)) {
            return false;
        }

        if ($this->point == $boundary->point()) {
            if ($boundary::class == $this::class) {
                return !$this->included;
            }

            return false;
        }

        return false;
    }
}
