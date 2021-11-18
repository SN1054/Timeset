<?php

namespace SN1054\Timeset\Test;

use PHPUnit\Framework\TestCase;
use SN1054\Timeset\Boundary;
use SN1054\Timeset\LeftBoundary;
use SN1054\Timeset\RightBoundary;
use DateTimeImmutable;
use DateInterval;

class LeftBoundaryTest extends TestCase
{
    private LeftBoundary $leftBoundary;
    private RightBoundary $rightBoundary;

    protected function setUp(): void
    {
        $this->leftBoundary = new LeftBoundary(new DateTimeImmutable('today 3am'));
        $this->rightBoundary = new RightBoundary(new DateTimeImmutable('today 5am'));
    }

    public function testLessThan(): void
    {
        # infinite Left and Left
        $boundary = new LeftBoundary(Boundary::MINUS_INFINITY);
        $this->assertFalse($this->leftBoundary->lessThan($boundary));
        $this->assertTrue($boundary->lessThan($this->leftBoundary));

        #finite equal Left and Left
        $boundary = new LeftBoundary(new DateTimeImmutable('today 3am'), false);
        $this->assertTrue($this->leftBoundary->lessThan($boundary));

        #finite Left and Right
        $arg = new RightBoundary(new DateTimeImmutable('today 1am'));
        $this->assertFalse($this->leftBoundary->lessThan($arg));
    }

    public function testConstruct(): void
    {
        $this->expectException(\Exception::class);
        new LeftBoundary('invalid string argument');
    }

    public function testAdd(): void
    {
        $arg = new LeftBoundary(Boundary::MINUS_INFINITY);
        $this->assertEquals($arg, $arg->add(new DateInterval('PT1H')));
    }


}
