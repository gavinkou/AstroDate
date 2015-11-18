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
 * Represents a year type, as in Julian or Besselian
 * @property float $days Number of days in the year
 */
class YearType {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new YearType from a number of days
   * @param float $days
   */
  protected function __construct($days) {
    $this->days = $days;
  }

  // // // Static

  /**
   * Represents a Julian year (365.25 days)
   * @return static
   */
  public static function Julian() {
    return new static(365.25);
  }

  /**
   * Represents a Besselian year (365.2421988 days)
   * @return static
   */
  public static function Besselian() {
    return new static(365.242198781);
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Number of days per the year
   * @var float
   */
  protected $days;

  public function __get($name) {
    switch ($name) {
      case 'days':
        return $this->{$name};
    }
  }

}
