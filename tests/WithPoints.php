<?php

namespace Hypership\Geo\Tests;

use Hypership\Geo\Math;
use Hypership\Geo\Point3D;
use Hypership\Geo\PointSpherical;

trait WithPoints {

    public function providePoints (): iterable {
        yield [
            new Point3D(-28.232, -33.237, -30.422),
            new PointSpherical( 53.171817, 2.179915, -2.274951),
        ];
        yield [
            new Point3D(22.872, -75.829, -71.902),
            new PointSpherical(106.972254, 2.307913, -1.277848),
        ];
        yield [
            new Point3D(-92.964, -67.125, 1.834),
            new PointSpherical(114.679704, 1.554803, -2.516218),
        ];
        yield [
            new Point3D(7.327, -51.089, -48.228),
            new PointSpherical( 70.637885, 2.322316, -1.428351),
        ];
        yield [
            new Point3D(-31.358, 93.665, -62.046),
            new PointSpherical(116.645456, 2.131662, 1.893856),
        ];
        yield [
            new Point3D(70.400, -62.563, -63.704),
            new PointSpherical(113.703512, 2.165501, -0.726525),
        ];
    }

    public function providePointsCartesian() : iterable {
        foreach ($this->providePoints() as $point) {
            yield [$point[0]];
        }
    }

    public function providePointsSpherical() : iterable {
        foreach ($this->providePoints() as $point) {
            yield [$point[1]];
        }
    }

    public static function assertPointSphericalEquals(PointSpherical $expected, PointSpherical $actual, $message = '') {
        self::assertTrue(Math::equals($expected->rho, $actual->rho, 1E-6), "[rho] $message");

        // We're more tolerant for angles than regular epsilon as
        // we compare against canonical results, where trigonometry
        // calculation can produce slightly different results.
        self::assertTrue(Math::equals($expected->theta, $actual->theta, 1E-6), "[theta] $message");
        self::assertTrue(Math::equals($expected->phi, $actual->phi, 1E-6), "[phi] $message");
    }

    public static function assertPointCartesianEquals(Point3D $expected, Point3D $actual, $message = '') {
        self::assertTrue(Math::equals($expected->x, $actual->x, 1E-3), "[x] $message");
        self::assertTrue(Math::equals($expected->y, $actual->y, 1E-3), "[y] $message");
        self::assertTrue(Math::equals($expected->z, $actual->z, 1E-3), "[z] $message");
    }

}
