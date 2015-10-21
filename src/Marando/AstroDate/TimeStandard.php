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
 * Represents an astronomical time standard such as TT or TDB
 */
class TimeStandard {

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct($value) {
    $this->value = $value;
  }

  // // // Static

  /**
   * Coordinated Universal Time (UTC)
   * @return static
   */
  public static function UTC() {
    return new static('UTC');
  }

  /**
   * International Atomic Time (TAI)
   * @return static
   */
  public static function TAI() {
    return new static('TAI');
  }

  /**
   * Terrestrial Dynamic Time (TT or TDT)
   * @return static
   */
  public static function TT() {
    return new static('TT');
  }

  /**
   * Barycentric Dynamic Time (TDB)
   * @return static
   */
  public static function TDB() {
    return new static('TDB');
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Value of this instance
   * @var string
   */
  protected $value;

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------
  // // // Overrides

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    return (string)$this->value;
  }

}
