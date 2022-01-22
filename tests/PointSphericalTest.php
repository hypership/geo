<?php

namespace Hypership\Geo\Tests;

use Hypership\Geo\Math;
use Hypership\Geo\Point3D;
use Hypership\Geo\PointSpherical;
use PHPUnit\Framework\TestCase;

class PointSphericalTest extends TestCase {

    use WithPoints;

    ///
    /// Constructors
    ///

    public function testZero () : void {
        $expected = new PointSpherical(0.0, 0.0, 0.0);
        $actual = PointSpherical::zero();

        self::assertPointSphericalEquals($expected, $actual);
    }

    ///
    /// Comparison
    ///

    /**
     * @dataProvider providePointsSpherical
     */
    public function testEquals(PointSpherical $left) {
        $right = new PointSpherical($left->rho, $left->theta, $left->phi);
        self::assertTrue($left->equals($right));
    }

    ///
    /// Distance
    ///

    /**
     * @dataProvider providePointsSpherical
     */
    public function testIfDistanceBetweenTwoIdenticalPointsIsNull (PointSpherical $point) {
        $distance = $point->distance(clone $point);

        // With so much trigonometry we've an error of 1.91E-6 on amd64.
        self::assertTrue(Math::equals(0.0, $distance, 1E-5));
    }

    ///
    /// Convert
    ///

    /**
     * @dataProvider providePoints
     */
    public function testToCartesian (Point3D $expected, PointSpherical $point) {
        self::assertPointCartesianEquals($expected, $point->toCartesian());
    }

    /**
     * @dataProvider providePointsSpherical
     */
    public function testToCylindricalKeepsPhi (PointSpherical $point) : void {
        self::assertEquals($point->phi, $point->toCylindrical()->phi);
    }

    ///
    /// Normalize
    ///

    public function testEqualsWithNormalizedRho() {
        // (-r, -θ, φ + 180°) == (r, θ, φ)

        $left = new PointSpherical(-1, -M_PI_4, M_PI);
        $right = new PointSpherical(1, M_PI_4, 0);

        $this->assertTrue($left->equals($right));
    }

    public function testEqualsWithNormalizedTheta() {
        // (r, -θ, φ) == (r, θ, φ + 180°)

        $left = new PointSpherical(1, -M_PI_4, M_PI);
        $right = new PointSpherical(1, M_PI_4, 0);

        $this->assertTrue($left->equals($right));
    }

    public function provideArbitraryEqualPoints() : iterable {
        // If r is zero, both azimuth and inclination are arbitrary.
        yield [
            new PointSpherical(0, 0, 0),
            new PointSpherical(0, M_PI_4, M_PI_2),
        ];

        // If θ is zero or 180°, the azimuth angle is arbitrary.
        yield [
            new PointSpherical(4, 0, 0),
            new PointSpherical(4, 0, M_PI_2),
        ];

        yield [
            new PointSpherical(4, M_PI, 0),
            new PointSpherical(4, M_PI, M_PI_2),
        ];
    }

    /**
     * @dataProvider provideArbitraryEqualPoints
     */
    public function testEqualsWhenRhoOrThetaIsNull(PointSpherical $reference, PointSpherical $another_one) {
        $this->assertTrue($reference->equals($another_one));
    }

    ///
    /// String manipulation
    ///

    public function testSprintfInRadians() {
        $point = new PointSpherical(70.637885, 2.322316, -1.428351);
        $format = "(%01.2f, %01.2f, %01.2f)";

        $expected = "(70.64, 2.32, 4.85)"; // phi will be normalized: + 2π
        $this->assertEquals($expected, $point->sprintf($format));
    }

    public function testSprintfInDegrees() {
        $point = new PointSpherical(4, M_PI_2, M_PI_4);
        $expected = "(4.00, 90.00°, 45.00°)";

        $this->assertEquals($expected, $point->__toString());
    }

}
