<?php

namespace SN1054\Timeset;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class Point implements Set
{
    private DateTimeImmutable $point;

    public function __construct(DateTimeInterface $aPoint) 
    {
        $this->point = $aPoint instanceof DateTime 
            ? DateTimeImmutable::createFromMutable($aBeginning)
            : $aBeginning;
    }

    public function or(Set $set): Set
    {
        //TODO
    }
    
    public function and(ConnectedSet $set): ConnectedSet
    {
        //TODO
    }

    public function xor(ConnectedSet $set): Set
    {
        //TODO
    }

    public function not(): DisconnectedSet
    {
        //TODO
    }

    public function shift(DateInterval $interval): ConnectedSet
    {
        //TODO
    }
}
