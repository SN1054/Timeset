<?php

namespace SN1054\Timeset;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class ConnectedSet implements Set
{
    private DateTimeImmutable|string $beginning;
    private string $leftBoundary;
    private DateTimeImmutable|string $end;
    private string $rightBoundary;

    public function __construct(
        DateTimeInterface|string $aBeginning, 
        DateTimeInterface|string $anEnd,
        string $leftBoundary = self::INCLUDED,
        string $rightBoundary = self::INCLUDED
    ) {
        if (is_string($aBeginning) && $aBeginning !== self::MINUS_INFINITY) {
            throw new Exception();
        }

        if (is_string($anEnd) && $anEnd !== self::PLUS_INFINITY) {
            throw new Exception();
        }

        if ($beginning > $end) {
            throw new Exception();
        }

        $this->beginning = $aBeginning instanceof DateTime 
            ? DateTimeImmutable::createFromMutable($aBeginning)
            : $aBeginning;

        $this->end = $anEnd instanceof DateTime 
            ? DateTimeImmutable::createFromMutable($anEnd)
            : $anEnd;
    }

    private function proxyToArgument(Set $set): bool
    {
        $className = $set::class;

        return $className == EmptySet::class
            || $className == DisconnectedSet::class;
    }

    
    public function or(Set $set): Set
    {
        if ($this->proxyToArgument($set)) {
            return $set->or($this);
        }
        //TODO
    }
    
    public function and(Set $set): ConnectedSet
    {
        if ($this->proxyToArgument($set)) {
            return $set->or($this);
        }
        //TODO
    }

    public function xor(Set $set): Set
    {
        if ($this->proxyToArgument($set)) {
            return $set->or($this);
        }
        //TODO
    }

    public function not(): DisconnectedSet
    {
        //TODO
    }

    public function length(): DateInterval|string
    {
        //TODO
    }

    public function shift(DateInterval $interval): ConnectedSet
    {
        //TODO
    }
}
