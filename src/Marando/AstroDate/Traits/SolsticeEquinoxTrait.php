<?php

/*
 * Copyright (C) 2015 Ashley Marando
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Marando\AstroDate\Traits;

trait SolsticeEquinoxTrait {

  /**
   * Finds the date of the time of the March Equinox for a given year
   *
   * @param float $year The year
   * @param bool  $vsop If true calculates the result using the VSOP model; If
   *                    false approximates the result.
   *
   * @return float The JD and time of the March Equinox in TD
   */
  protected static function equinoxMarch($year, $vsop = true) {
    $month = 3;

    if ($vsop)
      return static::accurate($year, static::meanTerms($month, $year), $month);
    else
      return static::approx($year, static::meanTerms($month, $year));
  }

  /**
   * Finds the date of the time of the September Equinox for a given year
   *
   * @param float $year The year
   * @param bool  $vsop If true calculates the result using the VSOP model; If
   *                    false approximates the result.
   *
   * @return float The JD and time of the September Equinox in TD
   */
  protected static function equinoxSeptember($year, $vsop = true) {
    $month = 9;

    if ($vsop)
      return static::accurate($year, static::meanTerms($month, $year), $month);
    else
      return static::approx($year, static::meanTerms($month, $year));
  }

  /**
   * Finds the date of the time of the June Solstice for a given year
   *
   * @param float $year The year
   * @param bool  $vsop If true calculates the result using the VSOP model; If
   *                    false, approximates the result
   *
   * @return float The JD time of the June Solstice in TD
   */
  protected static function solsticeJune($year, $vsop = true) {
    $month = 6;

    if ($vsop)
      return static::accurate($year, static::meanTerms($month, $year), $month);
    else
      return static::approx($year, static::meanTerms($month, $year));
  }

  /**
   * Finds the date of the time of the December Solstice for a given year
   *
   * @param float $year The year
   * @param bool  $vsop If true calculates the result using the VSOP model; If
   *                    false, approximates the result
   *
   * @return float The JD and time of the December Solstice in TD
   */
  protected static function solsticeDecember($year, $vsop = true) {
    $month = 12;

    if ($vsop)
      return static::accurate($year, static::meanTerms($month, $year), $month);
    else
      return static::approx($year, static::meanTerms($month, $year));
  }

  // // // Private

  /**
   * Finds the approximate time of a solstice or equinox in TD
   *
   * @param int   $year  The year to find a solstice or equinox for
   * @param array $terms An array of the mean terms for the desired equinox or
   *                     solstice
   *
   * @return float The JD of the solstice or equinox in TD
   */
  private static function approx($year, array $terms) {
    // Find appropriate year term and calculate approximate JDE
    $Y    = $year < 1000 ? (int)$year / 1000 : ((int)$year - 2000) / 1000;
    $jde0 = static::Horner($Y, $terms);

    // Calculate T and other required values
    $T  = ($jde0 - 2451545.0 ) / 36525;
    $W  = deg2rad(35999.373 * $T - 2.47);
    $Δλ = 1 + 0.0334 * cos($W) + 0.0007 * cos(2 * $W);

    // Sum the periodic terms for the solstice or equinox
    $S = 0;
    foreach (static::periodicTerms() as $term)
      $S += $term[0] * cos(deg2rad($term[1]) + deg2rad($term[2]) * $T);

    // Calculate the time of the solstice or equinox
    $jde = $jde0 + ((0.00001 * $S) / $Δλ);
    return $jde;
  }

  /**
   * Finds the accurate time of a solstice or equinox using the complete VSOP87
   * theory
   *
   * @param int   $year  The year to find a solstice or equinox for
   * @param array $terms An array of the mean terms for the desired equinox or
   *                     solstice
   * @param int   $month The month of the equinox or solstice to find
   *
   * @return float The JD of the solstice or equinox in TD
   */
  private static function accurate($year, array $terms, $month) {
    $Y = $year < 1000 ? (int)$year / 1000 : ((int)$year - 2000) / 1000;
    $q = intval($month / 3) - 1;

    $jde0 = static::Horner($Y, $terms);
    for ($i = 0; $i < 100; $i++) {
      // TODO:: use vsop 87, but use IAU apparent stuff?
      $λ = Solar::ApparentEclVSOP87(AstroDate::fromJD($jde0))->λ->rad;
      $Δ = 58 * sin(deg2rad($q * 90) - $λ);
      $jde0 += $Δ;

      if (abs($Δ) < 5e-7)
        break;
    }

    return $jde0;
  }

  /**
   * Gets the mean equinox/solstice terms for a given month and year grouping
   *
   * @param int $month The month of the equinox or solstice
   * @param int $year  The start maximum year of the terms, options are
   *                   1000 = -1000 to 1000 and 3000 = 1000 to 3000
   *
   * @return array The mean equinox/solstice terms
   */
  private static function meanTerms($month, $year) {
    $terms = [
        3  => [
            1000 => [1721139.29189, 365242.13740, .06134, .00111, -.00071],
            3000 => [2451623.80984, 365242.37404, .05169, -.00411, -.00057]
        ],
        6  => [
            1000 => [1721233.25401, 365241.72562, -.05232, .00907, .00025],
            3000 => [2451716.56767, 365241.62603, .00325, .00888, -.00030]
        ],
        9  => [
            1000 => [1721325.70455, 365242.49558, -.11677, -.00297, .00074],
            3000 => [2451810.21715, 365242.01767, -.11575, .00337, .00078]
        ],
        12 => [
            1000 => [1721414.39987, 365242.88257, -.00769, -.00933, -.00006],
            3000 => [2451900.05952, 365242.74049, -.06223, -.00823, .00032]
        ],
    ];

    $y = $year < 1000 ? 1000 : 3000;
    return $terms[$month][$y];
  }

  /**
   * Gets the periodic terms used in the calculation of equinoxes and solstices
   * @return array
   */
  private static function periodicTerms() {
    return [
        [485, 324.96, 1934.136],
        [203, 337.23, 32964.467],
        [199, 342.08, 20.186],
        [182, 27.85, 445267.112],
        [156, 73.14, 45036.886],
        [136, 171.52, 22518.443],
        [77, 222.54, 65928.934],
        [74, 296.72, 3034.906],
        [70, 243.58, 9037.513],
        [58, 119.81, 33718.147],
        [52, 297.17, 150.678],
        [50, 21.02, 2281.226],
        [45, 247.54, 29929.562],
        [44, 325.15, 31555.956],
        [29, 60.93, 4443.417],
        [18, 155.12, 67555.328],
        [17, 288.79, 4562.452],
        [16, 198.04, 62894.029],
        [14, 199.76, 31436.921],
        [12, 95.39, 14577.848],
        [12, 287.11, 31931.756],
        [12, 320.81, 34777.259],
        [9, 227.73, 1222.114],
        [8, 15.45, 16859.074],
    ];
  }

  /**
   * Evaluates a polynomial with coefficients c at x of which x is the constant
   * term by means of the Horner method
   *
   * @param float $x The constant term
   * @param array $c The coefficients of the polynomial
   *
   * @return float                    The value of the polynomial
   * @throws InvalidArgumentException Occurs if no coefficients are provided
   *
   * @see p.10-11, 'Avoiding powers'
   */
  private static function horner($x, $c) {
    if (count($c) == 0)
      throw new InvalidArgumentException('No coefficients were provided');

    $i = count($c) - 1;
    $y = $c[$i];
    while ($i > 0) {
      $i--;
      $y = $y * $x + $c[$i];
    }

    return $y;
  }

}
