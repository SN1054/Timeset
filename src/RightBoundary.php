<?php

namespace SN1054\Timeset;

class LeftBoundary extends Boundary
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

    public function smallerThan(Boundary $boundary): bool
    {
        if ($this->point == self::PLUS_INFINITY) {
            return $boundary->point() !== self::PLUS_INFINITY;
        }

        if ($this->point < $boundary->point()) {
            return true;
        }

        if ($this->equals($boundary)) {
            return false;
        }

        if ($this->point == $boundary->point()) {
            if ($boundary::class == $this::class) {
                return !$this->included;
            }

            return false;
        }
    }
}
