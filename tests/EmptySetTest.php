<?php

namespace SN1054\Timeset\Test;

use PHPUnit\Framework\TestCase;
use SN1054\Timeset\Set;
use SN1054\Timeset\EmptySet;
use SN1054\Timeset\ConnectedSet;
use SN1054\Timeset\DisconnectedSet;
use SN1054\Timeset\Boundary;
use SN1054\Timeset\LeftBoundary;
use SN1054\Timeset\RightBoundary;

class EmptySetTest extends TestCase
{
    private EmptySet $emptySet;
    private ConnectedSet $connectedSet;
    private DisconnectedSet $disconnectedSet;

    protected function setUp(): void
    {
        $this->emptySet = new EmptySet();
        $this->connectedSet = Set::create([['today 5am', 'today 8am']]);
        $this->disconnectedSet = Set::create(
            ['today 5am', 'today 8am'],
            ['today 1pm', 'today 2pm']
        );
    }

    public function testOr(): void
    {
        $this->assertSame($this->emptySet, $this->emptySet->or($this->emptySet));
        $this->assertSame($this->connectedSet, $this->emptySet->or($this->connectedSet));
        $this->assertSame($this->disconnectedSet, $this->emptySet->or($this->disconnectedSet));
    }

    public function testAnd(): void
    {
        $this->assertEquals($this->emptySet, $this->emptySet->and($this->emptySet));
        $this->assertEquals($this->emptySet, $this->emptySet->and($this->connectedSet));
        $this->assertEquals($this->emptySet, $this->emptySet->and($this->disconnectedSet));
    }

    public function testNot(): void
    {
        $this->assertEquals(
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary(Boundary::PLUS_INFINITY)
            ),
            $this->emptySet->not()
        );
    }

    public function testXor(): void
    {
        $this->assertSame($this->emptySet, $this->emptySet->xor($this->emptySet));
        $this->assertSame($this->connectedSet, $this->emptySet->xor($this->connectedSet));
        $this->assertSame($this->disconnectedSet, $this->emptySet->xor($this->disconnectedSet));
    }

}
