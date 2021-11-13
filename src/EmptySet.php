<?php

namespace SN1054\Timeset;

class EmptySet extends Set
{
    public function or(Set $set): Set
    {
        return $set;
    }
    
    public function and(Set $set): EmptySet
    {
        return new self();
    }

    public function xor(Set $set): Set
    {
        return $set;
    }

    public function not(): ConnectedSet
    {
        return new ConnectedSet(Set::MINUS_INFINITY, Set::PLUS_INFINITY);
    }
}
