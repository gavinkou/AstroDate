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

use \Exception;
use \Marando\IAU\IAU;
use \Marando\IERS\IERS;
use \Marando\Units\Time;

class AstroDate {

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct($year = null, $month = null, $day = null,
          $hour = null, $min = null, $sec = null, $timezone = null,
          $timescale = null) {

    $this->timescale = $timescale ? $timescale : TimeScale::UTC();

    if ($this->timescale != TimeScale::UTC())
      $this->timezone = Timezone::UTC();
    else
      $this->timezone = $timezone;

    // Civil date -> JD and fractional day
    $status = IAU::Dtf2d($this->timezone->name, (int)$year, (int)$month,
                    (int)$day, (int)$hour, (int)$min, (float)$sec, $this->jd,
                    $this->dayFrac);

    $this->checkDate($status);

    $this->add(Time::hours($this->timezone->offset));
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  protected $jd;
  protected $dayFrac;
  protected $timezone;
  protected $timescale;

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  public function setDate($year, $month, $day) {
    $status = IAU::Cal2jd((int)$year, (int)$month, (int)$day, $djm0, $djm);
    $this->checkDate($status);

    $this->jd = $djm0 + $djm;
    return $this;
  }

  public function setTime($hour, $min, $sec) {
    $status = IAU::Tf2d('+', $hour, $min, $sec, $days);
    $this->checkTime($status);

    $this->dayFrac = $days;
    return $this;
  }

  public function setTimezone(Timezone $timezone) {
    $this->toUTC();

    $tzOffset = $timezone->offset - $this->timezone->offset;
    $this->add(Time::hours($tzOffset));

    $this->timezone = $timezone;
    return $this;
  }

  public function add(Time $t) {
    // Interval to add as days
    $td = $t->days;

    // Days (jda) and day fraction (dfa) to add
    $jda = intval($td);
    $dfa = $dfa = $this->dayFrac + $td - $jda;

    // Additional day to add from above day frac in excess of 1 day
    $jda1 = intval($dfa);

    // Since additional day has been added, modulate day frac to range 0-1
    $dfa = fmod($dfa, 1);

    // Add the intervals
    $this->jd      = $this->jd + $jda + $jda1;
    $this->dayFrac = $dfa;

    return $this;
  }

  public function sub(Time $t) {
    return $this->add(Time::sec($t->sec * -1));
  }

  public function toUTC() {
    if ($this->timescale == TimeScale::UTC()) {
      // Remove the timezone and set to UTC
      $this->sub(Time::hours($this->timezone->offset));
      $this->timezone = Timezone::UTC();
      return $this;
    }

    if ($this->timescale == TimeScale::TAI()) {
      $tai1 = $this->jd;
      $tai2 = $this->dayFrac;
      IAU::Taiutc($tai1, $tai2, $utc1, $utc2);

      $this->jd        = $utc1;
      $this->dayFrac   = $utc2;
      $this->timescale = TimeScale::UTC();
      return $this;
    }

    if ($this->timescale == TimeScale::TT()) {
      $tt1 = $this->jd;
      $tt2 = $this->dayFrac;
      IAU::Tttai($tt1, $tt2, $tai1, $tai2);
      IAU::Taiutc($tai1, $tai2, $utc1, $utc2);

      $this->jd        = $utc1;
      $this->dayFrac   = $utc2;
      $this->timescale = TimeScale::UTC();
      return $this;
    }

     if ($this->timescale == TimeScale::UT1()) {
      $ut11 = $this->jd;
      $ut12 = $this->dayFrac;
      $dut1 = IERS::jd($ut11 + $ut12)->dut1();
      IAU::Ut1utc($ut11, $ut12, $dut1, $utc1, $utc2);

      $this->jd        = $utc1;
      $this->dayFrac   = $utc2;
      $this->timescale = TimeScale::UTC();
      return $this;
    }

    throw new Exception('Error converting to UTC');
  }

  public function toTAI() {
    if ($this->timescale == TimeScale::TAI())
      return $this;

    $this->toUTC();

    $utc1 = $this->jd;
    $utc2 = $this->dayFrac;
    IAU::Utctai($utc1, $utc2, $tai1, $tai2);

    $this->jd        = $tai1;
    $this->dayFrac   = $tai2;
    $this->timescale = TimeScale::TAI();
    return $this;
  }

  public function toTT() {
    if ($this->timescale == TimeScale::TT())
      return $this;

    $this->toTAI();

    $tai1 = $this->jd;
    $tai2 = $this->dayFrac;
    IAU::Taitt($tai1, $tai2, $tt1, $tt2);

    $this->jd        = $tt1;
    $this->dayFrac   = $tt2;
    $this->timescale = TimeScale::TT();
    return $this;
  }

  public function toUT1() {
    if ($this->timescale == TimeScale::UT1())
      return $this;

    $this->toUTC();

    $utc1 = $this->jd;
    $utc2 = $this->dayFrac;
    $dut1 = IERS::jd($utc1 + $utc2)->dut1();
    IAU::Utcut1($utc1, $utc2, $dut1, $ut11, $ut12);

    $this->jd        = $ut11;
    $this->dayFrac   = $ut12;
    $this->timescale = TimeScale::UT1();
    return $this;
  }

  // // // Protected

  protected function checkDate($status) {
    switch ($status) {
      case 3:
        throw new Exception('time is after end of day and dubious year');

      case 2:
        throw new Exception('time is after end of day');

      case 1:
        throw new Exception('dubious year');

      case -1:
        throw new Exception('bad year');

      case -2:
        throw new Exception('bad month');

      case -3:
        throw new Exception('bad day');

      case -4:
        throw new Exception('bad hour');

      case -5:
        throw new Exception('bad minute');

      case -6:
        throw new Exception('bad second');
    }
  }

  protected function checkTime($status) {
    switch ($status) {
      case 1:
        throw new Exception('hour outside range 0-23');

      case 2:
        throw new Exception('min outside range 0-59');

      case 3:
        throw new Exception('sec outside range 0-59.999...');
    }
  }

  // // // Overrides

  public function __toString() {
    $ihmsf = [];
    IAU::D2dtf($this->timescale, 14, $this->jd, $this->dayFrac, $iy, $im, $id,
            $ihmsf);

    $date = sprintf("%4d-%02.2d-%02.2d", $iy, $im, $id);
    $time = sprintf("%02d:%02.2d:%02.2d.%03.3d", $ihmsf[0], $ihmsf[1],
            $ihmsf[2], substr($ihmsf[3], 0, 3));

    if ($this->timescale == TimeScale::UTC())
      return "$date $time $this->timezone";
    else
      return "$date $time $this->timescale";
  }

}
