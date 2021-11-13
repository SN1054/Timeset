<?php

namespace SN1054\Timeset;

use DateTimeInterface;
use DateTimeImmutable;
use DateTime;
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
