<?php

namespace SN1054\Timeset;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class ConnectedSet extends Set
{
    public function __construct(
        private LeftBoundary $leftBoundary,
        private RightBoundary $rightBoundary
        //TODO check for consistency of point
    ) {
    }

    public static function createPoint(DateTimeInterface $point): self
    {
        $point = DateTimeImmutable::createFromInterface($point);

        return new self(
            new LeftBoundary($point),
            new RightBoundary($point)
        );
    }

    public function leftBoundary(): LeftBoundary
    {
        return $this->leftBoundary;
    }

    public function rightBoundary(): RightBoundary
    {
        return $this->rightBoundary;
    }

    public function or(Set $set): Set
    {
        if ($set instanceof EmptySet) {
            return $set->or($this);
        }

        if ($set instanceof ConnectedSet) {
            return $this->orForConnected($set);
        }

        /** @var DisconnectedSet $set */
        return Set::create(array_merge($set->sets(), [$this]));
    }

    private function orForConnected(ConnectedSet $set): Set
    {
        list($first, $second) = self::sort($set, $this);

        if ($first->rightBoundary()->lessThan($second->leftBoundary())) {
            return new DisconnectedSet($first, $second);
        }

        $rightBoundary = $this->rightBoundary->greaterThanOrEqual($set->rightBoundary())
            ? $this->rightBoundary
            : $set->rightBoundary();

        return new ConnectedSet($first->leftBoundary(), $rightBoundary);
    }

    public static function sort(ConnectedSet ...$sets): array
    {
        usort($sets, function (ConnectedSet $a, ConnectedSet $b) {
            if ($a->leftBoundary()->equal($b->leftBoundary())) {
                if ($a->rightBoundary()->equal($b->rightBoundary())) {
                    return 0;
                }

                return $a->rightBoundary()->lessThan($b->rightBoundary()) ? -1 : 1;
            }

            return $a->leftBoundary()->lessThan($b->leftBoundary()) ? -1 : 1;
        });

        return $sets;
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function and(Set $set): Set
    {
        if ($set instanceof EmptySet) {
            return $set->and($this);
        }

        if ($set instanceof ConnectedSet) {
            return $this->andForConnected($set);
        }

        /** @var ConnectedSet[] $result */
        $result = [];

        /** @var DisconnectedSet $set */
        foreach ($set->sets() as $connectedSet) {
            if (false === ($element = $this->andForConnected($connectedSet))->isEmpty()) {
                $result[] = $element;
            }
        }

        switch (count($result)) {
            case 0:
                return new EmptySet();
                break;
            case 1:
                return $result[0];
                break;
            default:
                return new DisconnectedSet(...$result);
        }
    }

    private function andForConnected(ConnectedSet $set): ConnectedSet|EmptySet
    {
        list($first, $second) = self::sort($set, $this);

        if ($first->rightBoundary()->lessThan($second->leftBoundary())) {
            return new EmptySet();
        }

        if ($first->rightBoundary()->lessThan($second->rightBoundary())) {
            return new ConnectedSet($second->leftBoundary(), $first->rightBoundary());
        }

        return clone $second;
    }

    public function xor(Set $set): Set
    {
        if ($set instanceof EmptySet) {
            return $set->xor($this);
        }

        # (A or B) and (not(A and B))
        return $this->or($set)->and(
            ($this->and($set))->not()
        );
    }

    public function not(): Set
    {
        if ($this->leftBoundary->isInfinite()
            && $this->rightBoundary->isInfinite()
        ) {
            return new EmptySet();
        }

        if ($this->leftBoundary->isInfinite()) {
            return new ConnectedSet(
                $this->rightBoundary->invert(),
                new RightBoundary(Boundary::PLUS_INFINITY)
            );
        }

        if ($this->rightBoundary->isInfinite()) {
            return new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                $this->leftBoundary->invert()
            );
        }

        return new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                $this->leftBoundary->invert()
            ),
            new ConnectedSet(
                $this->rightBoundary->invert(),
                new RightBoundary(Boundary::PLUS_INFINITY)
            )
        );
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     * @psalm-suppress PossiblyInvalidMethodCall
     */
    public function length(): DateInterval|string
    {
        if ($this->leftBoundary->isInfinite()
            || $this->rightBoundary->isInfinite()
        ) {
            return Set::INFINITY;
        }

        return $this->leftBoundary->point()->diff($this->rightBoundary->point());
    }

    public function shift(DateInterval $interval): ConnectedSet
    {
        return new ConnectedSet(
            $this->leftBoundary->add($interval),
            $this->rightBoundary->add($interval)
        );
    }

    public function isPoint(): bool
    {
        return $this->leftBoundary->point() == $this->rightBoundary->point();
    }

    public function toArray(): array
    {
        return [
            (string) $this->leftBoundary,
            (string) $this->rightBoundary
        ];
    }
}
