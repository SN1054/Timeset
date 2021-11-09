<?php

namespace SN1054\Timeset;

interface Set
{
    public const INFINITY = 'infinity';
    public function or(Set $set): Set;
    public function and(Set $set): Set;
    public function xor(Set $set): Set;
    public function not(): Set;
}
