<?php

namespace Hypership\Geo;

use InvalidArgumentException;

class Point3D {

    //
    // Public properties
    //

    /**
     * the x coordinate
     */
    public float $x;

    /**
     * the y coordinate
     */
    public float $y;

    /**
     * the z coordinate
     */
    public float $z;

    //
    // Constructors
    //

    /**
     * Initializes a new instance of Point3D class
     */
    function __construct (float $x, float $y, float $z) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    static function zero (): Point3D {
        return new Point3D(0.0, 0.0, 0.0);
    }

    /**
     * Parses a string expression and gets a Point3D object
     *
     * Formats recognized are:
     *      - xyz: [x, y, z]
     *      - (x, y, z)
     *
     * @param string $expression The expression to parse
     * @return Point3D If the specified expression could be parsed, a GeoPoint3D instance ; otherwise, null.
     */
    static function fromString (string $expression): Point3D {
        if (str_starts_with($expression, 'xyz:')) {
            $pos1 = strpos($expression, '[', 4) + 1;
            $pos2 = strpos($expression, ']', $pos1);

            if ($pos1 > -1 && $pos2 > -1) {
                $expression = substr($expression, $pos1, $pos2 - $pos1);
                $xyz = explode(',', $expression, 3);

                return new Point3D((float)$xyz[0], (float)$xyz[1], (float)$xyz[2]);
            }
        } elseif ($expression[0] === '(') {
            $expression = substr($expression, 1, -1);
            $xyz = explode(',', $expression, 3);

            return new Point3D((float)$xyz[0], (float)$xyz[1], (float)$xyz[2]);
        }

        throw new InvalidArgumentException("Not a valid expression");
    }

    //
    // String representation
    //

    /**
     * Returns a string representation of the point coordinates.
     *
     * @param string $format the format to use
     * @return string a string representation of the coordinates
     *
     * To print a "xyz: [10, 20, 40]" string:
     *     $point = new GeoPoint3D(10, 20, 40);
     *     echo $point->sprintf("xyz: [%d, %d, %d]");
     *
     *     // Of course, you could have (implicitly) use the __toString method:
     *     echo $point;
     *
     * To print a (10, 20, 40) string:
     *     $point = new GeoPoint3D(10, 20, 40);
     *     echo $point->sprintf("(%d, %d, %d)");
     */
    function sprintf (string $format): string {
        return sprintf($format, $this->x, $this->y, $this->z);
    }

    /**
     * Returns a xyz: [x, y, z] string representation of the point coordinates.
     *
     * @return string a xyz: [x, y, z] string representation of the coordinates
     */
    function __toString () {
        return $this->sprintf("xyz: [%01.2f, %01.2f, %01.2f]");
    }

    //
    // Implement operations
    //

    /**
     * Determines if this point is equal to the specified point.
     *
     * @param Point3D $other The point to compare
     * @return bool true if the two points are equal ; otherwise, false.
     */
    function equals (Point3D $other): bool {
        return Math::equals($this->x, $other->x)
            && Math::equals($this->y, $other->y)
            && Math::equals($this->z, $other->z);
    }

    /**
     * Computes the distance between two points
     *
     * @param Point3D $other The point to compute the distance to
     * @return float The distance
     */
    function distance (Point3D $other): float {
        return sqrt(
            pow($this->x - $other->x, 2)
            + pow($this->y - $other->y, 2)
            + pow($this->z - $other->z, 2)
        );
    }

    //
    // Conversion to other coordinates systems
    //

    /**
     * Gets the (ρ, φ, θ) spherical coordinates from the current x, y, z cartesian point
     */
    function toSpherical (): PointSpherical {
        /*
         * ρ = sqrt(x² + y² + z²)
         * θ = acos z/φ
         * φ = atan2 $y $x
         */
        $rho   = sqrt($this->x * $this->x + $this->y * $this->y + $this->z * $this->z);
        $theta = acos($this->z / $rho);
        $phi   = atan2($this->y, $this->x);

        return new PointSpherical($rho, $theta, $phi);
    }

    function toCylindrical (): PointPolarZ {
        /*
         * ρ = sqrt(x² + y²)
         * φ = atan2 $y $x
         * z = z
         */
        $rho = sqrt($this->x * $this->x + $this->y * $this->y);
        $phi = atan2($this->y, $this->x);

        return new PointPolarZ($rho, $phi, $this->z);
    }

    //
    // Geometry operations
    //

    /**
     * Translates the center.
     *
     * This method allow helping to represent coordinate in a new system.
     *
     *
     * @param float $dx the difference between the old x and new x (ie the value of x = 0 in the new system)
     * @param float $dy the difference between the old y and new y (ie the value of y = 0 in the new system)
     * @param float $dz the difference between the old y and new z (ie the value of z = 0 in the new system)
     */
    function translate (float $dx, float $dy, float $dz) : self {
        $this->x = $this->x + $dx;
        $this->y = $this->y + $dy;
        $this->z = $this->z + $dz;

        return $this;
    }

    function moveOriginTo(float $x, float $y, float $z) : self {
        $this->translate(-$x, -$y, -$z);

        return $this;
    }

    function scale (float $scale) : self {
        $this->x *= $scale;
        $this->y *= $scale;
        $this->z *= $scale;

        return $this;
    }

}
