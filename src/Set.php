<?php

namespace SN1054\Timeset;

interface Set
{
    public const INCLUDED = 'included';
    public const EXCLUDED = 'excluded';
    public const INFINITY = 'infinity';
    public const MINUS_INFINITY = 'minus_infinity';
    public const PLUS_INFINITY = 'plus_infinity';
    public function or(Set $set): Set;
    public function and(Set $set): Set;
    public function xor(Set $set): Set;
    public function not(): Set;
}
