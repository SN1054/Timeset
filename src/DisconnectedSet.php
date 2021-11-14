<?php

namespace SN1054\Timeset;

use DateInterval;
use DateTime;
use Exception;

class DisconnectedSet extends Set
{
    /**
     * @var ConnectedSet[]
     */
    private array $sets;

    public function __construct(ConnectedSet ...$sets)
    {
        if ($sets !== Set::normalize(...$sets)) {
            throw new Exception();
        }

        $this->sets = $sets;
    }

    public function sets(): array
    {
        return $this->sets;
    }

    public function or(Set $set): Set
    {
        if ($set instanceof EmptySet || $set instanceof ConnectedSet) {
            return $set->or($this);
        }

        /** @var DisconnectedSet $set */
        return Set::create(...array_merge($this->sets(), $set->sets()));
    }

    public function and(Set $set): Set
    {
        if ($set instanceof EmptySet || $set instanceof ConnectedSet) {
            return $set->and($this);
        }

        $result = [];

        foreach ($this->sets as $connectedSet) {
            /** @var DisconnectedSet $set */
            $temp = $connectedSet->and($set);

            switch ($temp::class) {
                case EmptySet::class:
                    continue 2;
                case ConnectedSet::class:
                    $result[] = $temp;
                    break;
                case DisconnectedSet::class:
                    $result = array_merge($result, $temp->sets());
            }
        }

        return new self(...$result);
    }

    public function xor(Set $set): Set
    {
        # (A or B) and (not(A and B))
        return $this->or($set)->and(
            ($this->and($set))->not()
        );
    }

    # -(A or B or ...) = -A and -B and ...
    public function not(): Set
    {
        return array_reduce(
            array_map(
                fn(ConnectedSet $set): Set => $set->not(),
                $this->sets
            ),
            fn(Set $carry, Set $item): Set => $carry->and($item),
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary(Boundary::PLUS_INFINITY)
            )
        );
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function length(): DateInterval|string
    {
        $accum = clone $reference = (new DateTime())->setTimestamp(0);

        foreach ($this->sets as $set) {
            if (Set::INFINITY === $length = $set->length()) {
                return Set::INFINITY;
            }

            $accum->add($length);
        }

        return $reference->diff($accum);
    }

    public function shift(DateInterval $interval): self
    {
        return new self(
            ...array_map(
                fn(ConnectedSet $set): ConnectedSet => $set->shift($interval),
                $this->sets
            )
        );
    }
}
