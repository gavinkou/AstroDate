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

use \Exception;

/**
 * Represents an astronomical time scale, e.g. TDB, TT, TAI, etc...
 *
 * @property string $name Time scale name
 * @property string $abr  Time scale abbreviation
 */
class TimeScale {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new TimeScale from an abbreviation
   * @param type $abr
   */
  protected function __construct($abr) {
    $this->abr  = $abr;
    $this->name = $this->names[$abr];  // Lookup full name
  }

  // // // Static

  /**
   * Parses a time standard from a string value
   * @param  string $str
   * @return static
   */
  public static function parse($str) {
    switch (strtoupper($str)) {
      case 'TAI':
      case 'TDB':
      case 'TT':
      case 'UT1':
      case 'UTC':
        return new static($str);

      default:
        return new static('UTC');
        //throw new Exception("Unable to parse time standard '{$str}'");
    }
  }

  /**
   * Represents Coordinated Universal Time
   * @return static
   */
  public static function UTC() {
    return new static('UTC');
  }

  /**
   * Represents International Atomic Time
   * @return static
   */
  public static function TAI() {
    return new static('TAI');
  }

  /**
   * Represents Terrestrial Dynamic Time (TT)
   * @return static
   */
  public static function TT() {
    return new static('TT');
  }

  /**
   * Universal Time
   * @return static
   */
  public static function UT1() {
    return new static('UT1');
  }

  /**
   * Represents Barycentric Dynamic Time (TDB)
   * @return static
   */
  public static function TDB() {
    return new static('TDB');
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Time scale abbreviation, e.g. TDB
   * @var string
   */
  protected $abr;

  /**
   * Time scale full name
   * @var string
   */
  protected $name;

  /**
   * An array of time scale abbreviations and full names
   * @var array
   */
  protected $names = [
      'TAI' => 'International Atomic Time',
      'TDB' => 'Barycentric Dynamic Time',
      'TT'  => 'Terrestrial Dynamic Time',
      'UT1' => 'Universal Time',
      'UTC' => 'Coordinated Universal Time',
  ];

  public function __get($name) {
    switch ($name) {
      case 'abr':
      case 'name':
        return $this->{$name};
    }
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    return $this->abr;
  }

}
