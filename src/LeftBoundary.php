<?php

namespace SN1054\Timeset;

class LeftBoundary extends Boundary
{
    private DateTimeImmutable|string $point;
    private bool $included;

    public function __construct(DateTimeInterface|string $point, bool $included = true)
    {
        if (is_string($point)) {
            if ($point !== self::MINUS_INFINITY) {
                throw new Exception();
            }

            $included = false;
        }

        $this->point = $point instanceof DateTime 
            ? DateTimeImmutable::createFromMutable($point)
            : $point;

        $this->included = $included;
    }

    public function point(): DateTimeImmutable|string
    {
        return $this->point;
    }
}
