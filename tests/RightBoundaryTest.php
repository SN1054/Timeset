<?php

namespace SN1054\Timeset\Test;

use PHPUnit\Framework\TestCase;
use SN1054\Timeset\Boundary;
use SN1054\Timeset\LeftBoundary;
use SN1054\Timeset\RightBoundary;
use DateTimeImmutable;
use DateInterval;

class RightBoundaryTest extends TestCase
{
    private LeftBoundary $leftBoundary;
    private RightBoundary $rightBoundary;

    protected function setUp(): void
    {
        $this->leftBoundary = new LeftBoundary(new DateTimeImmutable('today 3am'));
        $this->rightBoundary = new RightBoundary(new DateTimeImmutable('today 5am'));
    }

    public function testConstruct(): void
    {
        $this->expectException(\Exception::class);
        new RightBoundary('invalid string argument');
    }

    public function testAdd(): void
    {
        $arg = new RightBoundary(Boundary::PLUS_INFINITY);
        $this->assertEquals($arg, $arg->add(new DateInterval('PT1H')));
    }


}
