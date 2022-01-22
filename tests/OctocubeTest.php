<?php

namespace Hypership\Geo\Tests;

use Hypership\Geo\Octocube;
use Hypership\Geo\Point3D;

use PHPUnit\Framework\TestCase;

use InvalidArgumentException;

class OctocubeTest extends TestCase {

    public function providesPointsAndSectors () : iterable {
        // sector, x, y, z
        yield [0, 0, 0, 0];
        yield [1, -42, 42, -42];
        yield [2, 42, 42, -42];
        yield [3, -42, -42, -42];
        yield [4, 42, -42, -42];
        yield [5, -42, 42, 42];
        yield [6, 42, 42, 42];
        yield [7, -42, -42, 42];
        yield [8, 42, -42, 42];
    }

    /**
     * @dataProvider providesPointsAndSectors
     */
    public function testGetSector ($sector, $x, $y, $z) {
        self::assertEquals($sector, Octocube::getSector($x, $y, $z));
    }

    public function testGetBaseVector () {
        self::assertEquals([1.0, 1.0 , 1.0], Octocube::getBaseVector(6));
    }

    public function testGetBaseVectorForNonExistingSector () {
        $this->expectException(InvalidArgumentException::class);

        Octocube::getBaseVector(666);
    }

    public function testGetSectorFromPoint3D () {
        $point = new Point3D(8, 8, 8);

        self::assertEquals(6, Octocube::getSectorFromPoint3D($point));
    }

    public function testGetSectorFromPoint3DAsString () {
        self::assertEquals(6, Octocube::getSectorFromPoint3D("(8, 8, 8)"));
    }
}
