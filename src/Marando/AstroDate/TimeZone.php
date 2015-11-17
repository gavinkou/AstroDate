<?php

/*
 * Copyright (C) 2015 ashley
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

class TimeZone {

  public $offset;
  public $dst;
  public $name;

  public function __construct($offset, $dst = true, $name = null) {
    $this->offset = $offset;
    $this->dst    = $dst;
    $this->name   = $name;
  }

  protected static function dayOfYear($y, $m, $d) {
    $l = ($y % 4 == 0 && $y % 100 != 0) || $y % 400 == 0;
    $k = $l ? 1 : 2;
    $n = intval(275 * $m / 9) - $k * intval(($m + 9) / 12) + $d - 30;

    return $n;
  }

  protected static function weekDayNum($jd) {
    return ($jd + 1.5) % 7;
  }

  public function offset($jd) {
    // Is DST observed for this timezone? If no, return offset as is
    if ($this->dst == false)
      return $this->offset;

    // Get YMD for given JD as well as day of year with day fraction
    IAU::Jd2cal($jd, 0, $y, $m, $d, $fd);
    $dayN = static::dayOfYear($y, $m, $d) + $fd;

    // DST begins at 2:00 a.m. on the second Sunday of March and
    IAU::Cal2jd($y, 3, 1, $djm0, $djm);
    $dayB = static::dayOfYear($y, 2, 1) + 14 -
            static::weekDayNum($djm0 + $djm) + (2 / 24);

    // DST ends at 2:00 a.m. on the first Sunday of November
    IAU::Cal2jd($y, 11, 1, $djm0, $djm);
    $dayE = static::dayOfYear($y, 11, 1) + 14 -
            static::weekDayNum($djm0 + $djm) + (2 / 24);

    if ($dayN >= $dayB && $dayN < $dayE)
      return $this->offset + 1;
    else
      return $this->offset;
  }

  public static function UT($offset, $dst = true) {
    return new static($offset, $dst);
  }

  public static function name($name) {
    // Use PHP DateTimeZone
    $dtz = new DateTimeZone($name);
    $dt  = new DateTime('2000-01-01');

    // Get offset and time zone name
    $offset = $dtz->getOffset($dt) / 3600;
    $abr    = $dtz->getName();

    return new static($offset, true, $abr);
  }

  public static function UTC() {
    return new static(0, false, 'UTC');
  }

  public function __toString() {
    if ($this->name)
      return $this->name;

    $o  = $this->offset;
    $os = $o >= 0 ? '+' : '-';
    $oh = sprintf('%02d', abs(intval($o)));
    $om = sprintf('%02d', abs($o - intval($o)) * 60);

    if ($om != '00')
      return "UT{$os}{$oh}:{$om}";
    else
      return "UT{$os}{$oh}";
  }

}
