<?php

namespace Hypership\Geo\Tests;

use Hypership\Geo\Math;
use Hypership\Geo\Point3D;
use Hypership\Geo\PointPolarZ;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PointPolarZTest extends TestCase {

    ///
    /// Constructors
    ///

    public function testZero () : void {
        $expected = new PointPolarZ(0.0, 0.0, 0.0);
        $actual = PointPolarZ::zero();

        self::assertTrue($expected->equals($actual));
    }

    public function providePointsAndStrings() : iterable {
        yield ["(1, 2, 3)", new PointPolarZ(1, 2, 3)];
        yield ["(1, -2, 3)", new PointPolarZ(1, -2, 3)];
        yield ["(1, 90°, 3)", new PointPolarZ(1, M_PI_2, 3)];
        yield ["(1, -90°, 3)", new PointPolarZ(1, -M_PI_2, 3)];
        yield ["rpz: [1.70, 5, 0]", new PointPolarZ(1.7, 5, 0)];
    }

    /**
     * @dataProvider  providePointsAndStrings
     */
    public function testParse (string $expression, PointPolarZ $expected) : void {
        $actual = PointPolarZ::fromString($expression);
        self::assertTrue($expected->equals($actual));
    }

    public function testParseWithInvalidString() : void {
        $this->expectException(InvalidArgumentException::class);

        PointPolarZ::fromString("I'm at the center of the cylinder.");
    }

    ///
    /// Strings
    ///

    public function testOutputStringInRadians () : void {
        $expected = "(1.70, 1.57, 0.00)";
        $format = "(%01.2f, %01.2f, %01.2f)";

        $point = new PointPolarZ(1.7, M_PI_2, 0);
        $this->assertEquals($expected, $point->sprintf($format));
    }

    public function testOutputStringInDegrees () : void {
        $expected = "rpz: [1.70, 90.00°, 0.00]";

        $point = new PointPolarZ(1.7, M_PI_2, 0);
        $this->assertEquals($expected, $point->__toString());
    }

    ///
    /// Normalize
    ///

    public function testNormalizeWithNegativeRho () : void {
        $expected = new PointPolarZ(4, deg2rad(270), 5);
        $actual = new PointPolarZ(-4, M_PI_2, 5);

        self::assertTrue($actual->equals($expected));
    }

    ///
    /// Distance
    ///

    public function testIfDistanceBetweenTwoIdenticalPointsIsNull () {
        $point = new PointPolarZ(1, M_PI_4, 3);

        self::assertEquals(0.0, $point->distance(clone $point));
    }

    ///
    /// Convert to other coordinates systems
    ///

    public function testToCartesian () : void {
        $expected = new Point3D(5 * sqrt(3) / 2, 5 / 2, 4);

        $point = new PointPolarZ(5, M_PI / 6, 4);
        self::assertTrue($expected->equals($point->toCartesian()));
    }

    public function testToSphericalKeepsPhi () : void {
        $point = new PointPolarZ(1, M_PI_2, 3);

        self::assertEquals($point->phi, $point->toSpherical()->phi);
    }

    ///
    /// Sections
    ///

    public function provideAnglesAndSections () : iterable {
        // 0 and ɛ should always be in the first section
        yield [0, 4, 1];
        yield [0, 6, 1];
        yield [Math::M_EPSILON, 4, 1];
        yield [Math::M_EPSILON, 6, 1];

        // Some regular values
        yield [M_PI_2, 6, 2];

        yield [deg2rad( 30), 4, 1];
        yield [deg2rad(100), 4, 2];
        yield [deg2rad(250), 4, 3];
        yield [deg2rad(320), 4, 4];

        // Some border values
        yield [M_PI,   4, 3];
        yield [M_PI_2, 4, 2];
        yield [M_PI,   6, 4];

        // Just before the border
        yield [M_PI   - Math::M_EPSILON, 4, 2];
        yield [M_PI_2 - Math::M_EPSILON, 4, 1];
        yield [M_PI   - Math::M_EPSILON, 6, 3];

        // Just after the border
        yield [M_PI   + Math::M_EPSILON, 4, 3];
        yield [M_PI_2 + Math::M_EPSILON, 4, 2];
        yield [M_PI   + Math::M_EPSILON, 6, 4];

        // Zπ - ɛ should always be in the last section
        yield [-Math::M_EPSILON, 4, 4];
        yield [-Math::M_EPSILON, 6, 6];
    }

    /**
     * @dataProvider provideAnglesAndSections
     */
    public function testCalculateSection (float $angle, int $count, int $expectedSection) {
        $actualSection = PointPolarZ::calculateSection($angle, $count);

        $this->assertEquals($expectedSection, $actualSection);
    }

    /**
     * @dataProvider provideAnglesAndSections
     */
    public function testGetSection (float $phi, int $count, int $expectedSection) {
        $point = new PointPolarZ(1, $phi, 5);

        $this->assertEquals($expectedSection, $point->getSection($count));
    }

}
