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
use DateTimeImmutable;
use DateInterval;

class DisconnectedSetTest extends TestCase
{
    private EmptySet $emptySet;
    private ConnectedSet $connectedSet;
    private DisconnectedSet $disconnectedSet;

    protected function setUp(): void
    {
        $this->emptySet = new EmptySet();
        $this->connectedSet = Set::create([['today 5am', 'today 8am']]);
        $this->disconnectedSet = Set::create([
            ['today 5am', 'today 8am'],
            ['today 1pm', 'today 2pm']
        ]);
    }

    public function testOr(): void
    {
        # disconnected OR empty
        $this->assertSame($this->disconnectedSet, $this->disconnectedSet->or($this->emptySet));

        # disconnected OR connected
        $this->assertEquals(
            $this->connectedSet->or($this->disconnectedSet), 
            $this->disconnectedSet->or($this->connectedSet)
        );

        # connected OR connected 2
        $expected = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 8am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 9am')),
                new RightBoundary(new DateTimeImmutable('today 11am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 1pm')),
                new RightBoundary(new DateTimeImmutable('today 2pm'))
            )
        );
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 9am')),
                new RightBoundary(new DateTimeImmutable('today 11am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 1pm')),
                new RightBoundary(new DateTimeImmutable('today 2pm'))
            )
        );
        $this->assertEquals($expected, $this->disconnectedSet->or($arg));
    }

    public function testAnd(): void
    {
        # disconnected AND empty
        $this->assertEquals($this->emptySet, $this->disconnectedSet->and($this->emptySet));

        # disconnected AND connected
        $this->assertEquals(
            $this->connectedSet->and($this->disconnectedSet), 
            $this->disconnectedSet->and($this->connectedSet)
        );

        # disconnected AND disconnected
        $arg1 = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 1am')),
                new RightBoundary(new DateTimeImmutable('today 2am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 8am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 1pm')),
                new RightBoundary(new DateTimeImmutable('today 2pm'))
            )
        );
        $arg2 = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 6am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 7am')),
                new RightBoundary(new DateTimeImmutable('today 8am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 1pm')),
                new RightBoundary(new DateTimeImmutable('today 2pm'))
            )
        );
        $this->assertEquals($arg2, $arg1->and($arg2));
    }

    public function testNot(): void
    {
        $expected = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary(new DateTimeImmutable('today 5am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 8am'), false),
                new RightBoundary(new DateTimeImmutable('today 1pm'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 2pm'), false),
                new RightBoundary(Boundary::PLUS_INFINITY)
            )
        );
        $this->assertEquals($expected, $this->disconnectedSet->not());
    }

    public function testXor(): void
    {
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 6am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 7am')),
                new RightBoundary(new DateTimeImmutable('today 8am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 1pm')),
                new RightBoundary(new DateTimeImmutable('today 2pm'))
            )
        );
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 6am'), false),
            new RightBoundary(new DateTimeImmutable('today 7am'), false)
        );
        $this->assertEquals($expected, $this->disconnectedSet->xor($arg));
    }

    public function testLength(): void
    {
        # LENGTH finite
        $this->assertEquals(new DateInterval('PT4H'), $this->disconnectedSet->length());

        # LENGTH infinite
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary(new DateTimeImmutable('today 3am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 7am'))
            )
        );
        $this->assertEquals(Set::INFINITY, $arg->length());
    }

    public function testShift(): void
    {
        $expected = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 6am')),
                new RightBoundary(new DateTimeImmutable('today 9am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 2pm')),
                new RightBoundary(new DateTimeImmutable('today 3pm'))
            )
        );
        $this->assertEquals($expected, $this->disconnectedSet->shift(new DateInterval('PT1H')));
    }

    public function testConstruct(): void
    {
        $this->expectException(\Exception::class);
        new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 6am'))
            )
        );
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse($this->disconnectedSet->isEmpty());
    }

    public function testToArray(): void
    {
        $array = $this->disconnectedSet->toArray();

        $set = Set::create($array);

        $this->assertEquals($set, $this->disconnectedSet);
    }
}
