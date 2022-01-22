<?php

namespace Hypership\Geo\Tests;

use Hypership\Geo\Math;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase {

    public function testEquals () {
        self::assertTrue(Math::equals(0, 0));
        self::assertTrue(Math::equals(0.01, 0.01));
        self::assertTrue(Math::equals(2.4 - 0.6, 1.8));
    }

    public function testEqualsWhenIsNotAsEpsilonIsTooLow () {
        /*
            In this test, the numbers won't be equal as the epsilon
            value is too low.

            For example, on amd64 architecture, we've:
            2.4 - 0.6 - 1.8 = -2.2204460492503E-16
         */

        self::assertFalse(Math::equals(2.4 - 0.6, 1.8, 1E-23));
    }

    ///
    /// Angles
    ///

    public function provideAngles (): iterable {
        yield [0, 0];
        yield [M_PI, M_PI];
        yield [2 * M_PI, 0];
        yield [deg2rad(450), M_PI_2];
    }

    public function provideAnglesForMinusPiToPiInterval (): iterable {
        yield [0, 0];
        yield [M_PI, -M_PI];
        yield [2 * M_PI, 0];
        yield [deg2rad(270), -M_PI_2];
    }

    public function provideAnglesToParse (): iterable {
        yield ["0", 0];
        yield ["3.1415926535898", M_PI];
        yield [" 3.1415926535898   ", M_PI];
        yield ["90°", M_PI_2];
        yield ["90 °", M_PI_2];
    }

    /**
     * @dataProvider provideAngles
     */
    public function testNormalizeAngle (float $raw, float $normalized) {
        self::assertTrue(Math::equals(
            $normalized,
            Math::normalizeAngle($raw)
        ));
    }

    /**
     * @dataProvider provideAnglesForMinusPiToPiInterval
     */
    public function testNormalizeAngleWithMinusPiToPiInterval (float $raw, float $normalized) {
        self::assertTrue(Math::equals(
            $normalized,
            Math::normalizeAngle($raw, -M_PI)
        ));
    }

    /**
     * @dataProvider provideAngles
     */
    public function testAreAngleEquals (float $left, float $right) {
        self::assertTrue(Math::areAngleEquals($left, $right));
    }

    /**
     * @dataProvider provideAnglesToParse
     */
    public function testParseAngle (string $toParse, float $expected) {
        self::assertTrue(Math::equals(
            $expected,
            Math::parseAngle($toParse)
        ));
    }

    public function testParseAngleWithInvalidString () {
        $this->expectException(InvalidArgumentException::class);

        Math::parseAngle("I'm a strange angle.");
    }

}
