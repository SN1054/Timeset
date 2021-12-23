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

class ConnectedSetTest extends TestCase
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
        # connected OR empty
        $this->assertSame($this->connectedSet, $this->connectedSet->or($this->emptySet));

        # connected OR connected
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 5am')),
            new RightBoundary(new DateTimeImmutable('today 11am'))
        );
        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 6am')),
            new RightBoundary(new DateTimeImmutable('today 11am'))
        );
        $this->assertEquals($expected, $this->connectedSet->or($arg));

        # connected OR connected_with_excluded_boundaries
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 5am')),
            new RightBoundary(new DateTimeImmutable('today 11am'), false)
        );
        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 6am'), false),
            new RightBoundary(new DateTimeImmutable('today 11am'), false)
        );
        $this->assertEquals($expected, $this->connectedSet->or($arg));

        # connected OR connected 2
        $expected = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 8am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 9am')),
                new RightBoundary(new DateTimeImmutable('today 11am'), false)
            )
        );
        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 9am')),
            new RightBoundary(new DateTimeImmutable('today 11am'), false)
        );
        $this->assertEquals($expected, $this->connectedSet->or($arg));

        # connected OR disconnected
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 4am'), false),
                new RightBoundary(new DateTimeImmutable('today 6am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 7am'), false),
                new RightBoundary(new DateTimeImmutable('today 9am'), false)
            )
        );
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 4am'), false),
            new RightBoundary(new DateTimeImmutable('today 9am'), false)
        );
        $this->assertEquals($expected, $this->connectedSet->or($arg));
    }

    public function testAnd(): void
    {
        # connected AND empty
        $this->assertEquals($this->emptySet, $this->connectedSet->and($this->emptySet));

        # connected AND connected, result connected
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 7am')),
            new RightBoundary(new DateTimeImmutable('today 8am'))
        );

        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 7am')),
            new RightBoundary(new DateTimeImmutable('today 9am'))
        );
        $this->assertEquals($expected, $this->connectedSet->and($arg));

        # connected AND connected, result empty
        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 9am')),
            new RightBoundary(new DateTimeImmutable('today 10am'))
        );
        $this->assertEquals(new EmptySet(), $this->connectedSet->and($arg));

        # connected AND disconnected, result empty
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 2am'), false),
                new RightBoundary(new DateTimeImmutable('today 3am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 2pm'), false),
                new RightBoundary(new DateTimeImmutable('today 5pm'), false)
            )
        );
        $this->assertEquals(new EmptySet(), $this->connectedSet->and($arg));

        # connected AND disconnected, result connected
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 4am')),
                new RightBoundary(new DateTimeImmutable('today 11am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 7pm'), false),
                new RightBoundary(new DateTimeImmutable('today 8pm'))
            )
        );
        $this->assertEquals($this->connectedSet, $this->connectedSet->and($arg));

        # connected AND disconnected, result disconnected
        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 4am')),
                new RightBoundary(new DateTimeImmutable('today 6am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 7am'), false),
                new RightBoundary(new DateTimeImmutable('today 9am'))
            )
        );
        $expected = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 6am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 7am'), false),
                new RightBoundary(new DateTimeImmutable('today 8am'))
            )
        );
        $this->assertEquals($expected, $this->connectedSet->and($arg));
    }

    public function testNot(): void
    {
        # NOT finite
        $expected = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(Boundary::MINUS_INFINITY),
                new RightBoundary(new DateTimeImmutable('today 5am'), false)
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 8am'), false),
                new RightBoundary(Boundary::PLUS_INFINITY)
            )
        );
        $this->assertEquals($expected, $this->connectedSet->not());
        
        # NOT left infinite
        $expected = new ConnectedSet(
            new LeftBoundary(Boundary::MINUS_INFINITY),
            new RightBoundary(new DateTimeImmutable('today 5am'), false)
        );
        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 5am')),
            new RightBoundary(Boundary::PLUS_INFINITY)
        );
        $this->assertEquals($expected, $arg->not());

        # NOT right infinite
        $arg = new ConnectedSet(
            new LeftBoundary(Boundary::MINUS_INFINITY),
            new RightBoundary(new DateTimeImmutable('today 5am'), false)
        );
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 5am')),
            new RightBoundary(Boundary::PLUS_INFINITY)
        );
        $this->assertEquals($expected, $arg->not());

        # NOT infinite
        $arg = new ConnectedSet(
            new LeftBoundary(Boundary::MINUS_INFINITY),
            new RightBoundary(Boundary::PLUS_INFINITY)
        );
        $this->assertEquals(new EmptySet(), $arg->not());
    }

    public function testXor(): void
    {
        # connected XOR empty
        $this->assertEquals($this->connectedSet, $this->connectedSet->xor(new EmptySet()));

        # connected XOR connected
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 5am')),
            new RightBoundary(new DateTimeImmutable('today 7am'), false)
        );
        $arg = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 7am')),
            new RightBoundary(new DateTimeImmutable('today 8am'))
        );
        $this->assertEquals($expected, $this->connectedSet->xor($arg));
    }

    public function testLength(): void
    {
        # connected finite LENGTH
        $expected = new DateInterval('PT3H');
        $this->assertEquals($expected, $this->connectedSet->length());

        # connected infinite LENGTH
        $expected = Set::INFINITY;
        $set = new ConnectedSet(
            new LeftBoundary(Boundary::MINUS_INFINITY),
            new RightBoundary(Boundary::PLUS_INFINITY)
        );
        $this->assertEquals($expected, $set->length());
    }

    public function testShift(): void
    {
        $expected = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 6am')),
            new RightBoundary(new DateTimeImmutable('today 9am'))
        );
        $this->assertEquals($expected, $this->connectedSet->shift(new DateInterval('PT1H')));
    }

    public function testIsPoint(): void
    {
        $this->assertFalse($this->connectedSet->isPoint());
        $this->assertTrue(ConnectedSet::createPoint(new DateTimeImmutable('today'))->isPoint());
    }

    public function testSort(): void
    {
        # left Boundary equal, right not
        $shouldBeFirst = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 1pm')),
            new RightBoundary(new DateTimeImmutable('today 2pm')),
        );
        $shouldBeSecond = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 1pm')),
            new RightBoundary(new DateTimeImmutable('today 3pm')),
        );
        $this->assertSame($shouldBeFirst, ConnectedSet::sort($shouldBeFirst, $shouldBeSecond)[0]);

        # both boundaries equal
        $shouldBeFirst = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 1pm')),
            new RightBoundary(new DateTimeImmutable('today 2pm')),
        );
        $shouldBeSecond = new ConnectedSet(
            new LeftBoundary(new DateTimeImmutable('today 1pm')),
            new RightBoundary(new DateTimeImmutable('today 2pm')),
        );
        $this->assertSame($shouldBeFirst, ConnectedSet::sort($shouldBeFirst, $shouldBeSecond)[0]);
    }

    public function testToArray(): void
    {
        $array = $this->connectedSet->toArray();

        $set = Set::create($array);

        $this->assertEquals($set, $this->connectedSet);
    }
}
