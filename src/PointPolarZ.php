<?php

namespace Hypership\Geo;

use InvalidArgumentException;

/**
 * Geo point polar+z class.
 *
 * This class represents a ρ, φ, z point.
 *
 * They are useful to express coordinates in a cylinder shape, like a tower
 * where it makes senses to use polar coordinates instead x, y but where the
 * height is not relative to a center, like it would be in a sphere.
 *
 * The point 3D representation is rpz: [ρ, φ, z] ; you can print it as a string
 * and get this format:
 *
 * <code>
 * $point = new GeoPointPolarZ(17, '24°', -6);
 * echo (string)$point;   //will output rρz: [17, 24°, -6]
 * </code>
 *
 */
class PointPolarZ {
    //
    // ρ, φ, z public properties
    //

    /**
     * the ρ coordinate, radial distance
     */
    public float $rho;

    /**
     * the φ coordinate, azimuth, expressed in radians
     */
    public float $phi;

    /**
     * the z coordinate
     */
    public float $z;

    //
    // Constructors
    //

    /**
     * Initializes a new instance of GeoPointPolarZ class
     *
     * @param float $rho the ρ coordinate, the radial distance
     * @param float $phi the φ coordinate
     * @param float $z the z coordinate
     */
    function __construct (float $rho, float $phi, float $z) {
        $this->rho = $rho;
        $this->phi = $phi;
        $this->z = $z;

        $this->normalize();
    }

    static function zero (): PointPolarZ {
        return new PointPolarZ(0.0, 0.0, 0.0);
    }

    /**
     * Parses a string expression and gets a GeoPointPolarZ object
     *
     * Formats recognized are:
     *      - rpz: [ρ, φ, z]
     *      - (ρ, φ, z)
     */
    public static function fromString (string $expression) : self {
        if (str_starts_with($expression, 'rpz:')) {
            $pos1 = strpos($expression, '[', 4) + 1;
            $pos2 = strpos($expression, ']', $pos1);
            if ($pos1 > -1 && $pos2 > -1) {
                $triplet = substr($expression, $pos1, $pos2 - $pos1);
                return self::parseTriplet($triplet);
            }
        } elseif ($expression[0] === '(') {
            $triplet = substr($expression, 1, -1);
            return self::parseTriplet($triplet);
        }

        throw new InvalidArgumentException("Can't parse string as coordinates.");
    }

    static private function parseTriplet (string $triplet) : self {
        $rpz = explode(',', $triplet, 3);

        $rho = (float)$rpz[0];
        $phi = Math::parseAngle($rpz[1]);
        $z = (float)$rpz[2];

        return new PointPolarZ($rho, $phi, $z);
    }

    ///
    /// String representation
    ///

    /**
     * Returns a string representation of the point coordinates.
     *
     * @param string $format the format to use, compatible with sprintf PHP format
     * @return string a string representation of the coordinates
     *
     * To print a "rpz: [10, 20°, 40]" string:
     *  $point = new GeoPointPolarZ(10, '20°', 40);
     *  echo $point->sprintf("rpz: [%d, %s, %d]");
     *
     *  //Of course, you could have (implicitly) use the __toString method:
     *  echo $point;
     *
     * To print a (10, 20°, 40) string:
     *  $point = new GeoPointPolarZ(10, 20°, 40);
     *  echo $point->sprintf("(%d, %s, %d)");
     */
    public function sprintf (string $format, AngleUnit $unit = AngleUnit::Radian) : string {
        if ($unit == AngleUnit::Degrees) {
            return sprintf($format, $this->rho, rad2deg($this->phi), $this->z);
        }

        return sprintf($format, $this->rho, $this->phi, $this->z);
    }

    /**
     * Returns a rρz: [r, ρ, z] string representation of the point coordinates.
     *
     * @return string a rpz: [ρ, θ, z] string representation of the coordinates
     */
    function __toString () {
        return $this->sprintf("rpz: [%01.2f, %01.2f°, %01.2f]", AngleUnit::Degrees);
    }

    //
    // Math operations
    //

    private function normalize () {
        if ($this->rho < 0) {
            // (ρ, φ, z) == (−ρ, φ + 180°, z)
            $this->rho *= -1;
            $this->phi += M_PI;
        }

        $this->phi = Math::normalizeAngle($this->phi);
    }

    /**
     * Determines if this point is equal to the specified point.
     *
     * @param PointPolarZ $other The point to compare
     * @return bool true if the two points are equal ; otherwise, false.
     */
    function equals (PointPolarZ $other): bool {
        $left = clone $this;
        $right = clone $other;

        $left->normalize();
        $right->normalize();

        return Math::equals($left->rho, $right->rho)
            && Math::equals($left->phi, $right->phi)
            && Math::equals($left->z, $right->z);
    }

    /**
     * Computes the distance between two points
     *
     * @param PointPolarZ $other The point to compute the distance to
     * @return float The distance
     */
    function distance (PointPolarZ $other): float {
        $delta_phi = $this->phi - $other->phi;

        return sqrt(
            $this->rho * $this->rho
            + $other->rho * $other->rho
            - 2 * $this->rho * $other->rho * cos($delta_phi)
            + pow($this->z - $other->z, 2)
        );
    }

    /**
     * Gets the (x, y, z) cartesian coordinates from the current ρ, φ, z polar+z point
     */
    function toCartesian (): Point3D {
        $x = $this->rho * cos($this->phi);
        $y = $this->rho * sin($this->phi);

        return new Point3D($x, $y, $this->z);
    }

    /**
     * Gets the (r, φ, θ) spherical coordinates from the current point
     */
    function toSpherical () : PointSpherical {
        $rho = sqrt($this->rho * $this->rho + $this->z * $this->z);
        $theta = atan2($this->rho, $this->z);

        return new PointSpherical($rho, $theta, $this->phi);
    }

    ///
    /// Sections
    ///
    /// The concept of section allows to divide a circle into n parts,
    /// like you would cut a pie into n parts.
    ///
    ///        o  o              o  o
    ///     o 6    1 o        o 4 | 1  o
    ///    o          o      o ___|___  o
    ///    o          o      o  3 | 2   o
    ///     o 4    3 o        o   |    o
    ///        o  o              o  o
    ///
    ///       n = 6             n = 4
    
    /**
     * Calculates the section number the specified angle belongs
     *
     * @param $angle float The angle in radian (North 0, East π/2, South π, etc. clockwise)
     * @param int $count The amount of sections to divide the cylinder (default value: 6)
     * @return int the section number
     */
    static function calculateSection (float $angle, int $count = 6) : int {
        $angle = Math::normalizeAngle($angle);
        return 1 + (int)($angle / (2 * M_PI / $count));
    }

    /**
     * Gets the section number the angle φ belongs to.
     *
     * @param int $count The amount of sections to divide the cylinder (default value: 6)
     * @return int The section number for the point current coordinate φ
     */
    function getSection (int $count = 6) : int {
        return self::calculateSection($this->phi, $count);
    }
}
