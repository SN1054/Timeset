<?php

namespace SN1054\Timeset\Test;

use PHPUnit\Framework\TestCase;
use SN1054\Timeset\Set;
use SN1054\Timeset\EmptySet;
use SN1054\Timeset\ConnectedSet;
use SN1054\Timeset\DisconnectedSet;
use SN1054\Timeset\LeftBoundary;
use SN1054\Timeset\RightBoundary;
use DateTimeImmutable;
use DateInterval;

class SetTest extends TestCase
{
    public function testCreate(): void
    {
        $this->assertTrue(Set::create(['today 1am'])->isPoint());
        $this->assertTrue(Set::create([new DateTimeImmutable('today 3am')])->isPoint());
        $this->assertInstanceOf(EmptySet::class, Set::create([]));

        $arg = new DisconnectedSet(
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 2am')),
                new RightBoundary(new DateTimeImmutable('today 3am'))
            ),
            new ConnectedSet(
                new LeftBoundary(new DateTimeImmutable('today 5am')),
                new RightBoundary(new DateTimeImmutable('today 6am'))
            ),
        );

        $this->assertEquals($arg, Set::create([$arg]));
    }

    public function testCreateException1(): void
    {
        $this->expectException(\Exception::class);
        Set::create(['invalid string']);
    }

    public function testCreateException2(): void
    {
        $this->expectException(\Exception::class);
        Set::create([['invalid string']]);
    }

    public function testCreateException3(): void
    {
        $this->expectException(\Exception::class);
        Set::create([new DateInterval('P3D')]);
    }

}
