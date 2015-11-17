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

use \DateTime;
use \DateTimeZone;
use \Marando\IAU\IAU;

/**
 * Represents a time zone
 *
 * @property bool   $dst  If this time zone observes daylight savings time
 * @property string $name Time zone name, e.g. EST or UTC
 */
class TimeZone {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new time zone from an UTC offset in hours a name and if the zone
   * observes daylight savings time
   *
   * @param float  $offset UTC offset in hours
   * @param bool   $dst    If daylight savings time is observed
   * @param string $name   Time zone name/code
   */
  protected function __construct($offset, $dst = true, $name = null) {
    $this->offset = $offset;
    $this->dst    = $dst;
    $this->name   = $name;
  }

  // // // Static

  /**
   * Creates a new UTC offset based time zone, e.g. UT-5
   *
   * @param  float  $offset UTC offset in hours
   * @param  bool   $dst    If daylight savings time is observed
   * @return static
   */
  public static function UT($offset, $dst = true) {
    return new static($offset, $dst);
  }

  /**
   * Creates a new timezone from name or abbreviation
   * @param  string $name
   * @return static
   */
  public static function name($name) {
    // Use PHP DateTimeZone to get info below...
    $dtz = new DateTimeZone($name);
    $dt  = new DateTime('2000-01-01');
    $dst = true;

    //                // Offset in hours                 // TZ name
    return new static($dtz->getOffset($dt) / 3600, $dst, $dtz->getName());
  }

  /**
   * Represents the Coordinated Universal Time zone
   * @return static
   */
  public static function UTC() {
    return new static(0, false, 'UTC');
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * UTC offset in hours
   * @var float
   */
  protected $offset;

  /**
   * If daylight savings time is observed
   * @var bool
   */
  protected $dst;

  /**
   * Time zone name/code
   * @var string
   */
  protected $name;

  public function __get($name) {
    switch ($name) {
      case 'dst':
      case 'name':
        return $this->{$name};
    }
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Calculates the offset of this time zone for a given Julian date. Accounts
   * for daylight savings time if relevant and based on the date.
   *
   * @param  float $jd Julian date to check for
   * @return float     Offset in hours including DST (if relevant)
   */
  public function offset($jd) {
    // Is DST observed for this timezone? If no, return offset as is
    if ($this->dst == false)
      return $this->offset;

    // Get YMD for provided JD and day of year number (with fractional day)
    IAU::Jd2cal($jd, 0, $y, $m, $d, $fd);
    $dayN = static::dayOfYear($y, $m, $d) + $fd;

    // DST begins at 2:00 a.m. on the second Sunday of March and...
    IAU::Cal2jd($y, 3, 1, $djm0, $djm);
    $dayB = static::dayOfYear($y, 2, 1) + 14 -
            static::weekDayNum($djm0 + $djm) + (2 / 24);

    // ...ends at 2:00 a.m. on the first Sunday of November
    IAU::Cal2jd($y, 11, 1, $djm0, $djm);
    $dayE = static::dayOfYear($y, 11, 1) + 14 -
            static::weekDayNum($djm0 + $djm) + (2 / 24);

    // Check if the given JD falls with in the DST range for that year
    if ($dayN >= $dayB && $dayN < $dayE)
      return $this->offset + 1;
    else
      return $this->offset;
  }

  // // // Protected

  /**
   * Finds the day of the year number for a given date
   *
   * @param  int $y Year
   * @param  int $m Month
   * @param  int $d Day
   * @return int    Day of the year number
   */
  protected static function dayOfYear($y, $m, $d) {
    $l = ((int)$y % 4 == 0 && (int)$y % 100 != 0) || (int)$y % 400 == 0;
    $k = $l ? 1 : 2;
    $n = intval(275 * (int)$m / 9) -
            $k * intval(((int)$m + 9) / 12) +
            (int)$d - 30;

    return (int)$n;
  }

  /**
   * Finds the numeric week day number of a julian date
   * @param  float $jd
   * @return int
   */
  protected static function weekDayNum($jd) {
    return ($jd + 1.5) % 7;
  }

  // // // Overrides

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    // If it has a name... use the name
    if ($this->name)
      return $this->name;

    // Format offset as UT-07:30 or UT-07 format
    $o  = $this->offset;
    $os = $o >= 0 ? '+' : '-';
    $oh = sprintf('%02d', abs(intval($o)));
    $om = sprintf('%02d', abs($o - intval($o)) * 60);

    // If hour has minutes, return format UT-07:30 otherwise return UT-07
    if ($om != '00')
      return "UT{$os}{$oh}:{$om}";
    else
      return "UT{$os}{$oh}";
  }

}
