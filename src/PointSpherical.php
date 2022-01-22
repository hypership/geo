<?php

namespace Hypership\Geo;

class PointSpherical {

    //
    // Public properties
    //

    /**
     * the ρ coordinate, representing the radial distance,
     * ie the distance to origin
     */
    public float $rho;

    /**
     * the θ coordinate, in radian, representing the polar angle,
     * ie the angle with respect to polar axis
     */
    public float $theta;

    /**
     * the φ coordinate, in radian, representing the azimuthal angle,
     * ie the angle of rotation from the initial meridian plane
     */
    public float $phi;

    //
    // Constructors
    //

    /**
     * Initializes a new instance of PointSpherical class
     */
    public function __construct (float $rho, float $theta, float $phi) {
        $this->rho = $rho;
        $this->theta = $theta;
        $this->phi = $phi;

        $this->normalize();
    }

    static function zero (): PointSpherical {
        return new PointSpherical(0.0, 0.0, 0.0);
    }

    //
    // String representation
    //

    /**
     * Returns a string representation of the point coordinates.
     */
    public function sprintf (string $format, AngleUnit $unit = AngleUnit::Radian): string {
        if ($unit == AngleUnit::Degrees) {
            return sprintf($format, $this->rho, rad2deg($this->theta), rad2deg($this->phi));
        }

        return sprintf($format, $this->rho, $this->theta, $this->phi);
    }

    public function __toString () {
        return $this->sprintf("(%01.2f, %01.2f°, %01.2f°)", AngleUnit::Degrees);
    }

    //
    // Math operations
    //

    private function normalize () {
        if ($this->rho == 0) {
            $this->theta = 0;
            $this->phi = 0;

            return;
        }

        if ($this->rho < 0) {
            // (-r, -θ, φ + 180°) == (r, θ, φ)
            $this->rho *= -1;
            $this->theta *= -1;
            $this->phi -= M_PI;
        }

        if ($this->theta < 0) {
            // (r, -θ, φ) == (r, θ, φ + 180°)
            $this->theta *= -1;
            $this->phi += M_PI;
        }

        $this->theta = Math::normalizeAngle($this->theta);

        if ($this->theta == 0 || Math::areAngleEquals($this->theta, M_PI)) {
            $this->phi = 0;
            return;
        }

        $this->phi = Math::normalizeAngle($this->phi);
    }

    /**
     * Determines if this point is equal to the specified point.
     *
     * @param PointSpherical $other The point to compare
     * @return bool true if the two points are equal ; otherwise, false.
     */
    public function equals (PointSpherical $other): bool {
        $left = clone $this;
        $right = clone $other;

        $left->normalize();
        $right->normalize();

        return Math::equals($left->rho, $right->rho)
            && Math::equals($left->theta, $right->theta)
            && Math::equals($left->phi, $right->phi);
    }

    /**
     * Computes the distance between two points
     *
     * @param PointSpherical $other The point to compute the distance to
     * @return float The distance
     */
    function distance (PointSpherical $other): float {
        $delta_phi = $this->phi - $other->phi;
        $angular_correction =
            sin($this->theta) * sin($other->theta) * cos($delta_phi)
            + cos($this->theta) * cos($other->theta);

        return sqrt(
            $this->rho * $this->rho
                + $other->rho * $other->rho
            - 2 * $this->rho * $other->rho * $angular_correction
        );
    }

    //
    // Conversion to other coordinates systems
    //

    /**
     * Gets the (x, y, z) cartesian coordinates from the current point
     */
    public function toCartesian (): Point3D {
        /*
         * x = ρ sin θ cos φ
         * y = ρ sin θ sin φ
         * z = ρ cos θ
         */
        $x = $this->rho * sin($this->theta) * cos($this->phi);
        $y = $this->rho * sin($this->theta) * sin($this->phi);
        $z = $this->rho * cos($this->theta);

        return new Point3D($x, $y, $z);
    }

    public function toCylindrical() : PointPolarZ {
        /*
         * r = ρ sin θ
         * φ = φ
         * z = ρ cos θ
         */

        $r = $this->rho * sin($this->theta);
        $z = $this->rho * cos($this->theta);

        return new PointPolarZ($r, $this->phi, $z);
    }

}
