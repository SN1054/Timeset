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
    ) 
    {
    }

    public static function createPoint(DateTimeImmutable $point): self
    {
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
        extract($this->sort($set));

        if ($first->rightBoundary()->lessThan($second->leftBoundary())){
            return new DisconnectedSet($first, $second);
        }

        $rightBoundary = $this->rightBoundary->greaterThanOrEqual($set->rightBoundary()) 
            ? $this->rightBoundary 
            : $set->rightBoundary();

        return new ConnectedSet($first->leftBoundary(), $rightBoundary);
    }

    private function sort($set): array
    {
        if ($this->leftBoundary->equal($set->leftBoundary())) {
            return $this->rightBoundary->lessThanOrEqual($set->rightBoundary())
                ? ['first' => $this, 'second' => $set]
                : ['first' => $set, 'second' => $this];
        }

        return $this->leftBoundary->lessThan($set->leftBoundary())
            ? ['first' => $this, 'second' => $set]
            : ['first' => $set, 'second' => $this];
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
        extract($this->sort($set));

        if ($first->rightBoundary()->lessThan($second->leftBoundary())){
            return new EmptySet();
        }

        return new ConnectedSet($second->leftBoundary(), $first->rightBoundary());
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
        if ($this->leftBoundary->point() == Boundary::MINUS_INFINITY
            && $this->rightBoundary->point() == Boundary::PLUS_INFINITY
        ) {
            return new EmptySet();
        }

        if ($this->leftBoundary->point() == Boundary::MINUS_INFINITY) {
            return new ConnectedSet(
                new LeftBoundary($this->rightBoundary->point(), !$this->rightBoundary->included()),
                new RightBoundary(Boundary::PLUS_INFINITY)
            );
        }

        if ($this->rightBoundary->point() == Boundary::PLUS_INFINITY) {
            return new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary($this->leftBoundary->point(), !$this->leftBoundary->included())
            );
        }

        return new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary($this->leftBoundary->point(), !$this->leftBoundary->included())
            ),
            new ConnectedSet(
                new LeftBoundary($this->rightBoundary->point(), $this->rightBoundary->included()),
                new RightBoundary(Boundary::PLUS_INFINITY)
            )
        );
    }

    public function length(): DateInterval|string
    {
        if (
            $this->leftBoundary->point() == Boundary::MINUS_INFINITY
            && $this->rightBoundary->point() == Boundary::PLUS_INFINITY
        ) {
            return Set::INFINITY;
        }

        return $this->leftBoundary->point()->diff($this->rightBoundary->point());
    }

    public function shift(DateInterval $interval): ConnectedSet
    {
        //TODO Move all this logic to Boundary class. Boundary needs isInfinite() and add() methods
        if ($this->leftBoundary->point() == Boundary::MINUS_INFINITY) {
            return new ConnectedSet(
                $this->leftBoundary,
                new RightBoundary(
                    $this->rightBoundary->add($interval), 
                    $this->rightBoundary->included()
                )
            );
        }

        if ($this->rightBoundary->point() == Boundary::PLUS_INFINITY) {
            return new ConnectedSet(
                new LeftBoundary(
                    $this->leftBoundary->add($interval), 
                    $this->leftBoundary->included()
                ),
                $this->rightBoundary
            );
        }

        return new ConnectedSet(
            new LeftBoundary(
                $this->leftBoundary->add($interval), 
                $this->leftBoundary->included()
            ),
            new RightBoundary(
                $this->rightBoundary->add($interval), 
                $this->rightBoundary->included()
            )
        );
    }

    public function isPoint(): bool
    {
        return $this->leftBoundary->point() == $this->rightBoundary->point();
    }
}
