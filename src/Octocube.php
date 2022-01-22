<?php

namespace Hypership\Geo;

use InvalidArgumentException;

/**
 * Geo octocube class
 *
 * An octocube is a cube divided in 8 parts (sliced in two in x, y and z)
 *
 * The coordinates (0, 0, 0) represents the octocube center.
 */
class Octocube {

    /**
     * Gets the sector from the (x, y, z) specified coordinates.
     *
     * Sector will be:
     *
     * <code>
     * //             _____ _____
     * //           /  5  /  6  /|
     * //          /- - -/- - -/ |
     * //         /_____/____ /| |
     * //        |     |     | |/|
     * //        |  7  |  8  | / | 2
     * //        |_____|_____|/| |
     * //        |     |     | |/
     * //        |  3  |  4  | /
     * //        |_____|_____|/
     * </code>
     *
     * @return int The number of the sector, 0 if x = y = z = 0 ; otherwise, 1 to 8.
     */
    public static function getSector (int $x, int $y, int $z): int {
        // Cube center
        if ($x == 0 && $y == 0 && $z == 0) {
            return 0;
        }

        // One of the 8 cubes
        $sector = 1;

        if ($x >= 0) {
            $sector++;     //we're at right
        }

        if ($y < 0) {
            $sector += 2;  //we're at bottom
        }

        if ($z >= 0) {
            $sector += 4;  //we're on the top layer
        }

        return $sector;
    }

    /**
     * Gets the sector from the (x, y, z) specified coordinates.
     *
     * @param mixed $point a Point3D object for the x, y, z coordinates or a parsable string
     * @return int the number of the sector (0 if x = y = z 0 ; otherwise, 1 to 8)
     * @see Octocube::getSector()
     *
     */
    public static function getSectorFromPoint3D (Point3D|string $point): int {
        if (is_string($point)) {
            $point = Point3D::fromString($point);
        }

        return self::getSector($point->x, $point->y, $point->z);
    }

    /**
     * Gets the base vector for the specified sector.
     *
     * Example code:
     *
     * $vector = Octocube::getBaseVector(4);
     * // $vector is a (1, -1, -1) array
     *
     * @param int $sector the sector number (0-8)
     * @return int[] if the sector is 0, (0, 0, 0) ; otherwise, an array with three signed 1 values.
     */
    public static function getBaseVector (int $sector): array {
        $base_vectors = [
            0 => [0, 0, 0],

            1 => [-1, 1, -1],
            2 => [1, 1, -1],
            3 => [-1, -1, -1],
            4 => [1, -1, -1],
            5 => [-1, 1, 1],
            6 => [1, 1, 1],
            7 => [-1, -1, 1],
            8 => [1, -1, 1]
        ];

        if (array_key_exists($sector, $base_vectors)) {
            return $base_vectors[$sector];
        }

        throw new InvalidArgumentException("Not a valid sector");
    }

}
