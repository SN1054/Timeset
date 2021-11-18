<?php

namespace SN1054\Timeset\Test;

use PHPUnit\Framework\TestCase;
use SN1054\Timeset\Boundary;
use SN1054\Timeset\LeftBoundary;
use SN1054\Timeset\RightBoundary;
use DateTimeImmutable;
use DateInterval;

class BoundaryTest extends TestCase
{
    private LeftBoundary $leftBoundary;
    private RightBoundary $rightBoundary;

    protected function setUp(): void
    {
        $this->leftBoundary = new LeftBoundary(new DateTimeImmutable('today 3am'));
        $this->rightBoundary = new RightBoundary(new DateTimeImmutable('today 5am'));
    }

    public function testGreaterThan(): void
    {
        $this->assertTrue($this->rightBoundary->greaterThan($this->leftBoundary));
    }

    public function testLessThanOrEqual(): void
    {
        $this->assertTrue($this->leftBoundary->lessThanOrEqual($this->rightBoundary));
    }
}
