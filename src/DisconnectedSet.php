<?php

namespace SN1054\Timeset;

use DateInterval;
use Exception;

class DisconnectedSet extends Set
{
    /**
     * @var Set[]
     */
    private array $sets;

    public function __construct(ConnectedSet ...$sets)
    {
        if ($sets !== Set::normalize(...$sets)) {
            var_dump($sets, Set::normalize(...$sets));
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

        # $set instanceof DisconnectedSet
        return Set::fromArray(array_merge($this->sets(), $set->sets()));
    }
    
    public function and(Set $set): Set
    {
        if ($set instanceof EmptySet || $set instanceof ConnectedSet) {
            return $set->and($this);
        }

        # $set instanceof DisconnectedSet
        $result = [];

        foreach ($set as $connectedSet) {
            $temp = $connectedSet->and($this);

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

        return Set::fromArray($result);
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
                $this->set
            ),
            fn(Set $carry, Set $item): Set => $carry->and($item)
        );
    }

    public function length(): DateInterval|string
    {
        $counter = clone $reference = (new DateTime())->setTimestamp(0);

        foreach ($this->sets as $set) {
            if (Set::INFINITY === $length = $set->length()) {
                return Set::INFINITY;
            }

            $counter->add($length);
        }

        return $reference->diff($counter);
    }

    public function shift(DateInterval $interval): Set
    {
        return array_map(
            fn(Set $set): Set => $set->shift($interval),
            $this->sets
        );
    }
}
