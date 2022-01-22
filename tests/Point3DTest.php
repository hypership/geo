<?php

namespace Hypership\Geo\Tests;

use Hypership\Geo\Point3D;
use Hypership\Geo\PointPolarZ;
use Hypership\Geo\PointSpherical;

use PHPUnit\Framework\TestCase;

use InvalidArgumentException;

class Point3DTest extends TestCase {

    use WithPoints;

    ///
    /// Constructors
    ///

    public function testZero () : void {
        $expected = new Point3D(0.0, 0.0, 0.0);
        $actual = Point3D::zero();

        self::assertPointCartesianEquals($expected, $actual);
    }

    public function providePointsAndStrings() : iterable {
        yield ["(1, 2, 3)", new Point3D(1, 2, 3)];
        yield ["(1, -2, 3)", new Point3D(1, -2, 3)];
        yield ["xyz: [1.70, 5, 0]", new Point3D(1.7, 5, 0)];
    }

    /**
     * @dataProvider  providePointsAndStrings
     */
    public function testParse (string $expression, Point3D $expected) : void {
        $actual = Point3D::fromString($expression);
        self::assertPointCartesianEquals($expected, $actual);
    }

    public function testParseWithInvalidString() : void {
        $this->expectException(InvalidArgumentException::class);

        Point3D::fromString("I'm a point somewhere in the space.");
    }

    ///
    /// Comparison
    ///

    /**
     * @dataProvider providePointsCartesian
     */
    public function testEquals(Point3D $left): void {
        $right = new Point3D($left->x, $left->y, $left->z);

        self::assertTrue($left->equals($right));
    }

    ///
    /// String representation
    ///

    public function testToString() : void {
        $point = new Point3D(1.7, -5, 0);
        $actual = $point->__toString();

        self::assertEquals("xyz: [1.70, -5.00, 0.00]", $actual);
    }



    ///
    /// Convert to other coordinates systems
    ///

    /**
     * @dataProvider providePoints
     */
    public function testToSpherical (Point3D $point, PointSpherical $expected): void {
        self::assertPointSphericalEquals($expected, $point->toSpherical());
    }

    /**
     * @dataProvider providePointsCartesian
     */
    public function testToCylindricalKeepsZ (Point3D $point) : void {
        self::assertEquals($point->z, $point->toCylindrical()->z);
    }

    public function testToCylindrical () : void {
        $expected  = new PointPolarZ(5, M_PI / 6, 4);

        $point = new Point3D(5 * sqrt(3) / 2, 5 / 2, 4);
        self::assertTrue($expected->equals($point->toCylindrical()));
    }

    ///
    /// Distance
    ///

    /**
     * @dataProvider providePointsCartesian
     */
    public function testIfDistanceBetweenTwoIdenticalPointsIsNull (Point3D $point) {
        self::assertEquals(0.0, $point->distance(clone $point));
    }

    public function testDistance () {
        $a = new Point3D(7, 4,3);
        $b = new Point3D(17, 6, 2);

        self::assertEquals(sqrt(105), $a->distance($b));
    }

    ///
    /// Geometry operations :: do we support neutrals correctly?
    ///

    /**
     * @dataProvider providePointsCartesian
     */
    public function testTranslateWithNeutralVector (Point3D $point): void {
        $actual = clone $point;
        $actual->translate(0, 0, 0);

        self::assertPointCartesianEquals($point, $actual);
    }

    /**
     * @dataProvider providePointsCartesian
     */
    public function testScaleWithNeutralFactor (Point3D $point): void {
        $actual = clone $point;
        $actual->scale(1);

        self::assertPointCartesianEquals($point, $actual);
    }

    /**
     * @dataProvider providePointsCartesian
     */
    public function testMoveToOriginWithSameZeroOrigin (Point3D $point): void {
        $actual = clone $point;
        $actual->moveOriginTo(0, 0, 0);

        self::assertPointCartesianEquals($point, $actual);
    }

    ///
    /// Geometry operations :: do we support zero correctly?
    ///

    /**
     * @dataProvider providePointsCartesian
     */
    public function testScaleWithZero (Point3D $point): void {
        $expected = Point3D::zero();

        $actual = clone $point;
        $actual->scale(0);

        self::assertPointCartesianEquals($expected, $actual);
    }

    ///
    /// Geometry operations :: regular scenarii
    ///

    public function testPointForDojoViewer () : void {
        $expected = new Point3D(150, -129, 10);

        $actual = new Point3D(800, 42, 220);
        $actual
            ->moveOriginTo(500, 300, 200)
            ->scale(1/2);

        self::assertPointCartesianEquals($expected, $actual);
    }

}
