# hypership/geo

## Introduction

This library provides features to represent and manipulate geographical objects
in a 3D system.

It's initially built to represent several bodies in a galaxy.

It requires PHP 8.1 to use enumerations.

## Classes to represent coordinates

These classes allow to represent a point in different 3D coordinates systems.

Each class allows converting to other coordinates systems.

### Point3D

Represents a 3D point, identified by a (x, y, z) cartesian coordinates.

### PointSpherical

Represents a 3D point, identified by a (r, θ, φ) spherical coordinates,
as used in physics and following ISO 80000-2:2019 convention:

* r denotes the radial distance
* θ denotes the inclination
* φ denotes the azimuth 

This class could be used to denote the elevation instead of inclination as θ,
but if so, switch back to the inclination before converting to cylindrical
or cartesian coordinates:

```php
  $point->theta += M_PI_2
  $cylindricalPoint = $point->toCylindrical()
```

### PointPolarZ

Represents a 3D point, identified by a (ρ, φ, z) cylindrical coordinates:

* (ρ, φ) denotes the polar coordinates like in 2D:
  * ρ the radial distance
  * φ the azimuth
* z the height or axial coordinate

This notation follows the ISO 31-11:1992 standard. 

## Alternatives to represent coordinates

### Octocube

Maps 3D coordinates into a cube, divided in 8 sections.

Each section is called sector, numbered from 1 to 8:

       _____ _______
      /  5  /  6  / |
     /- - -/- - -/  |
    /_____/____ / | |
    |     |     | |/|
    |  7  |  8  | / | 2
    |_____|_____|/| |
    |     |     | |/
    |  3  |  4  | /
    |_____|_____|/

The point (0, 0, 0) is at the cube center.

**Use the octocube to represent octants*

As a 2D plane can be divided into quadrants, a 3D space can be divided
in octants. See https://en.wikipedia.org/wiki/Octant_(solid_geometry).

To get the list of sign of an octant, instead of its number,
you can use the method `getBaseVector`.

**Gets a point furthermore far away from the centre**

If you've a point P and wants to get another point, with the warranties
it will go furthermore from the center and never reach another octant
(ie the point will belong to the same octant), you can use:

```
$point = new Point3D(-7, 4, -5);
$sector = Octocube::getSectorFromPoint3D($point);
echo "Point belongs to sector C", $sector;

$vector = Octocube::getBaseVector($sector);
$point->translate(...$vector);
echo $point;
```

This code will output:

```
Point belongs to sector C1
xyz: [-8.00, 5.00, -6.00]
```

This technique has been tested with  map builders, where you need to increase
the content built indefinitely.

## Helper classes

### Math

Methods:

* **equals(a, b, Ɛ)**: compare two floats `a` and `b`, using `|a - b| < Ɛ` formula.
* **normalizeAngle(a)**: normalize the angle `a` into a `[0, 2π[` interval.
* **normalizeAngle(a, λ)**: normalize the angle `a` into a `[λ, λ + 2π[` interval.

Constants:

* **M_EPSILON**: default value for Ɛ.

## Pitfalls

### Don't compare floats using == or === operator

When comparing two float numbers, our library takes care to use `|a - b| < Ɛ`
and provide a `Math::equals` method for that.

In your own code, this is something you also need to do. For example, you want
to avoid this kind of scenario:

```php
$point = new PointSpherical(...);
// Some transformations for $point
if ($point->phi === 0.0) {
   // No inclination
}
```

You can instead use `if (Math::equals($point->phi, 0.0)) {}`.

### Trigonometry and floats create big error margins

A situation where you need to be very careful is when a lot of trigonometry
operations are involved. There is one place in our library that especially
happens: PointSpherical::distance multiplies several times cosines and sinus
by r.r'. The more far away your points is, the more you multiply roundings
inserted both by float numbers and cos/sin functions.

An example of such code:

```php
$point_a = new PointSpherical(116.645456, 2.131662, 1.893856);
$point_b = new PointSpherical(113.703512, 2.165501, -0.726525);
$distance = $point_a->distance($point_b);
```

This distance has a 10^-5 error margin.

If you need to compute distances, you'll get the better accuracy using Point3D.
The PointPolarZ distance method is also fairly precise.  
