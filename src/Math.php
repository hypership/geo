<?php

namespace Hypership\Geo;

use InvalidArgumentException;

class Math {

    /**
     * @var float
     */
    const M_EPSILON = 1E-6;

    /**
     * @param float $left The first float to compare
     * @param float $right The second float to compare
     * @param float $epsilon The admissible delta difference between two numbers, so they're still considered as equals.
     * @return bool true if equals ; otherwise, false.
     */
    public static function equals (float $left, float $right,
                                   float $epsilon = self::M_EPSILON): bool {
        return abs($left - $right) < $epsilon;
    }

    ///
    /// Angles
    ///

    /**
     * @param float $angle The angle to normalize in radian
     * @param float $from The minimal value for the angle
     *
     * Normalize an angle to express it in the open interval [$from, $from + 2π[
     */
    public static function normalizeAngle (float $angle, float $from = 0.0): float {
        $to = $from + 2 * M_PI;

        while ($angle < $from) {
            $angle += 2 * M_PI;
        }

        while ($angle >= $to) {
            $angle -= 2 * M_PI;
        }

        return $angle;
    }

    public static function areAngleEquals (float $left, float $right): bool {
        return self::equals(
            self::normalizeAngle($left),
            self::normalizeAngle($right)
        );
    }

    /**
     * Parse a string representing an angle. The angle will be interpreted
     * in radian if the string is only a number or in degrees if the string
     * contains the ° symbol.
     *
     * For example, "90°" will be parsed as π/4,
     * while "3" will be parsed as 3 radians.
     *
     * @param string $angle A string expression to parse
     * @return float The angle in radian
    */
    public static function parseAngle (string $angle) : float {
        $inDegrees = false;

        $angle = str_replace(' ' , '', $angle);

        if (str_contains($angle, '°')) {
            $inDegrees = true;
            $angle = str_replace('°' , '', $angle);
        }

        if (!is_numeric($angle)) {
            throw new InvalidArgumentException("Can't parse string as an angle.");
        }
        $angle = (float)$angle;

        if ($inDegrees) {
            return deg2rad($angle);
        }

        return $angle;
    }

}
