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

namespace Marando\AstroDate;

/**
 * Represents an astronomical epoch reference point
 *
 * @property float    $jd   Julian day count of the epoch
 * @property float    $year Year of the epoch
 * @property YearType $type Year type, Julian or Besselian
 */
class Epoch {
  //----------------------------------------------------------------------------
  // Constants
  //----------------------------------------------------------------------------

  /**
   * Number of days in one Julian year
   */
  const DaysJulianYear = 365.25;

  /**
   * Number of days in one Besselian year
   */
  const DaysBesselianYear = 365.242198781;

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new Epoch instance
   *
   * @param float    $jd   Julian day count of the epoch
   * @param YearType $type Year type, Julian or Bessilian
   */
  public function __construct($jd, YearType $type = null) {
    $this->jd   = $jd;
    $this->type = $type ? $type : YearType::Julian();
  }

  // // // Stati

  /**
   * Creates a new Epoch instance from a Julian day count
   *
   * @param float    $jd   Julian day count of the epoch
   * @param YearType $type Year type, Julian or Bessilian
   */
  public static function jd($jd, YearType $type = null) {
    return new static($jd, $type);
  }

  /**
   * Creates a new Epoch instance from an AstroDate instance
   *
   * @param  AstroDate $dt AstroDate instance
   * @return static
   */
  public static function dt(AstroDate $dt) {
    return new static($dt->toTT()->jd);
  }

  /**
   * Creates a new epoch from a Julian year number
   * @param float $year Year number in Julian years
   */
  public static function J($year) {
    // Get JD of the epoch
    $jd = static::J2000()->jd + ($year - 2000) * static::DaysJulianYear;

    // Create and return new epoch
    $epoch       = new static($jd);
    $epoch->type = YearType::Julian();
    return $epoch;
  }

  /**
   * Creates a new epoch from a Besselian year number
   * @param float $year Year number in Besselian years
   */
  public static function B($year) {
    // Get JD of the epoch
    $jd = static::B1900()->jd + ($year - 1900) * static::DaysBesselianYear;

    // Create and return new epoch
    $epoch       = new static($jd);
    $epoch->type = YearType::Besselian();
    return $epoch;
  }

  /**
   * Represents the Modified Julian epoch
   * @return static
   */
  public static function JMod() {
    return new static(2400000.5);
  }

  /**
   * Represents the J1900 epoch (JD 2415020.0 TT)
   * @return static
   */
  public static function J1900() {
    return new static(2415020.0);
  }

  /**
   * Represents the J2000 epoch (2451545.0 TT)
   * @return static
   */
  public static function J2000() {
    return new static(2451545.0);
  }

  /**
   * Represents the B1900 epoch (2415020.31352 TT)
   * @return static
   */
  public static function B1900() {
    $epoch       = new static(2415020.31352);
    $epoch->type = YearType::Besselian();
    return $epoch;
  }

  /**
   * Represents the B1950 epoch (2433282.4235 TT)
   * @return static
   */
  public static function B1950() {
    $epoch       = new static(2433282.4235);
    $epoch->type = YearType::Besselian();
    return $epoch;
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Julian day count of the epoch
   * @var float
   */
  protected $jd;

  /**
   * Year of the epoch
   * @var float
   */
  protected $year;

  /**
   * Year type, Julian or Besselian
   * @var YearTYpe
   */
  protected $type;

  public function __get($name) {
    switch ($name) {
      case 'jd':
      case 'type':
        return $this->{$name};

      case 'year':
        return $this->getYear();

      default:
        throw new Exception("{$name} is not a valid or writable property.");
    }
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Converts this instance to an AstroDate instance
   * @return AstroDate
   */
  public function toDate() {
    return AstroDate::jd($this->jd, TimeStandard::TT());
  }

  // // // Protected

  /**
   * Finds the year of this epoch
   * @return float
   */
  protected function getYear() {
    $year = 0;
    if ($this->type == YearType::Besselian())
      $year = 1900 + ($this->jd - Epoch::B1900()->jd) / static::DaysBesselianYear;
    else
      $year = 2000 + ($this->jd - Epoch::J2000()->jd) / static::DaysJulianYear;

    return round($year, 6);
  }

  // // // Overrides

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    $yearType = $this->type == YearType::Besselian() ? 'B' : 'J';
    return "{$yearType}{$this->year}";
  }

}
