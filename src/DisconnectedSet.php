<?php

namespace SN1054\Timeset;

use DateInterval;

class DisconnectedSet implements Set
{
    /**
     * @var Set[]
     */
    private array $sets;

    public function or(Set $set): Set
    {
        //TODO
    }
    
    public function and(Set $set): Set
    {
        //TODO
    }

    public function xor(Set $set): Set
    {
        //TODO
    }

    public function not(): Set
    {
        //TODO
    }

    public function length(): DateInterval|string
    {
        //TODO
    }

    public function shift(DateInterval $interval): Set
    {
        //TODO
    }
}
