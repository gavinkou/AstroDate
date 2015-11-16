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

/**
 * @property float $era       Era
 * @property float $year      Year
 * @property float $month     Month
 * @property float $day       Day
 * @property float $hour      Hour
 * @property float $min       Minute
 * @property float $sec       Second
 * @property float $micro     Milliseconds
 * @property float $timezone  Time zone
 * @property float $timescale Astronomical time scale, e.g. UTC, TAI, TT
 */
class AstroDate {

  use FormatTrait;

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Modified Julian day epoch (0h Nov 17, 1858)
   */
  const MJD = 2400000.5;

  /**
   * Unix epoch (0h Jan 1, 1970)
   */
  const UJD = 2440587.5;

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
      $this->timezone = $timezone ? $timezone : Timezone::UTC();

    // Civil date -> JD and fractional day
    $status = IAU::Dtf2d($this->timezone->name, (int)$year, (int)$month,
                    (int)$day, (int)$hour, (int)$min, (float)$sec, $this->jd,
                    $this->dayFrac);

    $this->checkDate($status);

    if ($this->timezone != Timezone::UTC()) {
      $tzOffset = $this->dstOffset($this->timezone);
      $this->add(Time::hours($tzOffset));
    }
  }

  // // // Static

  public static function jd($jd, TimeScale $timescale = null) {
    $ihmsf = [];
    IAU::D2dtf($timescale, 14, $jd, 0, $year, $month, $day, $ihmsf);

    return new AstroDate($year, $month, $day, $ihmsf[0], $ihmsf[1], $ihmsf[2],
            null, $timescale);
  }

  public static function mjd($mjd, TimeScale $timescale = null) {
    return static::jd($mjd + static::MJD, $timescale);
  }

  /**
   * Creates a new AstroDate using the current date and time
   * @return static
   */
  public static function now(Timezone $timezone = null) {
    // Get current time as micro unix timestamp
    $now   = explode(' ', microtime());
    $unix  = $now[1];
    $micro = Time::sec($now[0]);

    // Compoute JD from unix timestamp
    $jd = ($unix / 86400.0) + static::UJD;

    // Add timezone if present
    if ($timezone == null)
      $timezone = Timezone::UTC();

    // Return the new date adding the micro portion and setting timezone
    return static::jd($jd)->add($micro)->setTimezone($timezone);
  }

  public static function parse($datetime) {
    // 2015-Nov-16 17:07:07.120 UTC
    $format1 = '^([\+\-]*[0-9]{1,7})-([a-zA-Z]{1,9})-([0-9]{1,2})\s([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})(\.*[0-9]*)\s*([a-zA-Z]*)$';
    if (preg_match("/$format1/", $datetime, $t)) {
      $m  = static::monthNum($t[2]);
      $dt = new AstroDate($t[1], $m, $t[3], $t[4], $t[5], $t[6]);

      $dt->add(Time::sec($t[7])); //->timescale = $t[8];
      return $dt;
    }

    // 2015-1-16 17:07:07.120 UTC
    $format2 = '^([\+\-]*[0-9]{1,7})-([0-9]{1,2})-([0-9]{1,2})\s([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})(\.*[0-9]*)\s*([a-zA-Z]*)$';
    if (preg_match("/$format2/", $datetime, $t)) {
      $dt = new AstroDate($t[1], $t[2], $t[3], $t[4], $t[5], $t[6]);

      $dt->add(Time::sec($t[7])); //->timescale = $t[8];
      return $dt;
    }
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  protected $jd;
  protected $dayFrac;
  protected $timezone;
  protected $timescale;
  protected $prec = 11;

  public function __get($name) {
    switch ($name) {
      case 'era':
        return $this->year < 1 ? 'B.C.' : 'A.D.';

      case 'year':
      case 'month':
      case 'day':
      case 'hour':
      case 'min':
      case 'sec':
      case 'micro':
        return $this->getComponent($name);

      case 'timezone':
      case 'timescale':
        return $this->{$name};
    }
  }

  public function __set($name, $value) {
    switch ($name) {
      case 'year':
        return $this->setDate($value, $this->month, $this->day);

      case 'month':
        return $this->setDate($this->year, $value, $this->day);

      case 'day':
        return $this->setDate($this->year, $this->month, $value);

      case 'hour':
        return $this->setTime($value, $this->min, $this->sec);

      case 'min':
        return $this->setTime($this->hour, $value, $this->sec);

      case 'sec':
        return $this->setTime($this->hour, $this->min, $value);
    }
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  public function setDate($year, $month, $day) {
    $status = IAU::Cal2jd((int)$year, (int)$month, (int)$day, $djm0, $djm);
    $this->checkDate($status);

    $this->jd = $djm0 + $djm;  // Only set JD, keep day frac to save time
    return $this;
  }

  public function setTime($hour, $min, $sec) {
    $status = IAU::Tf2d('+', $hour, $min, $sec, $days);
    $this->checkTime($status);

    $this->dayFrac = $days;
    return $this;
  }

  public function setDateTime($year, $month, $day, $hour, $min, $sec) {
    return $this->setDate($year, $month, $day)->setTime($hour, $min, $sec);
  }

  public function setTimezone(Timezone $timezone) {
    $this->toUTC();

    $tzOffset = $this->dstOffset($timezone) - $this->dstOffset($this->timezone);
    $this->add(Time::hours($tzOffset));

    $this->timezone = $timezone;
    return $this;
  }

  public function toJD($scale = null) {
    if ($scale)
      return bcadd((string)$this->jd, (string)$this->dayFrac, $scale);
    else
      return $this->jd + $this->dayFrac;
  }

  public function toMJD($scale = null) {
    $mjd = static::MJD;

    if ($scale)
      return bcsub(bcadd($this->jd, $this->dayFrac, $scale), $mjd, $scale);
    else
      return $this->jd + $this->dayFrac - $mjd;
  }

  public function add(Time $t) {
    // Interval to add as days
    $td = $t->days;

    // Days (jda) and day fraction (dfa) to add
    $jda = intval($td);

    //$dfa = $dfa = $this->dayFrac + $td - $jda;
    $dfa = $this->dayFrac + $td - $jda;

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
      $offset         = $this->dstOffset($this->timezone);
      $this->sub(Time::hours($offset));
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

    if ($this->timescale == TimeScale::TDB()) {
      $tt1  = $this->jd;
      $tt2  = $this->dayFrac;
      $ut   = $this->dayFrac;
      $dtr  = IAU::Dtdb($tt1, $tt2, $ut, 0, 0, 0);
      $tdb1 = $this->jd;
      $tdb2 = $this->dayFrac;

      IAU::Tdbtt($tdb1, $tdb2, $dtr, $tt1, $tt2);
      IAU::Tttai($tt1, $tt2, $tai1, $tai2);
      IAU::Taiutc($tai1, $tai2, $utc1, $utc2);

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

  public function toTDB() {
    if ($this->timescale == TimeScale::TDB())
      return $this;

    $this->toTT();
    $tt1 = $this->jd;
    $tt2 = $this->dayFrac;

    $this->toUT1();
    $ut = $this->dayFrac;

    $dtr = IAU::Dtdb($tt1, $tt2, $ut, 0, 0, 0);
    IAU::Tttdb($tt1, $tt2, $dtr, $tdb1, $tdb2);

    $this->jd        = $tdb1;
    $this->dayFrac   = $tdb2;
    $this->timescale = TimeScale::TDB();
    return $this;
  }

  public function monthName($full = false) {
    $months = [
        [1, 'Jan', 'January'],
        [2, 'Feb', 'February'],
        [3, 'Mar', 'March'],
        [4, 'Apr', 'April'],
        [5, 'May', 'May'],
        [6, 'Jun', 'June'],
        [7, 'Jul', 'July'],
        [8, 'Aug', 'August'],
        [9, 'Sep', 'September'],
        [10, 'Oct', 'October'],
        [11, 'Nov', 'November'],
        [12, 'Dec', 'December'],
    ];

    return $months[$this->month - 1][$full ? 2 : 1];
  }

  public function isLeapYear() {
    return ($this->year % 4 == 0 && $this->year % 100 != 0) ||
            $this->year % 400 == 0;
  }

  public function dayName($full = true) {
    $days = [
        [0, 'Sun', 'Sunday'],
        [1, 'Mon', 'Monday'],
        [2, 'Tue', 'Tuesday'],
        [3, 'Wed', 'Wednesday'],
        [4, 'Thu', 'Thursday'],
        [5, 'Fri', 'Friday'],
        [6, 'Sat', 'Saturday'],
    ];

    return $days[$this->weekDayNum()][$full ? 2 : 1];
  }

  public function diff(AstroDate $b) {
    $prec = 12;
    $jd1  = $this->jd($prec);
    $jd2  = $b->jd($prec);
    $days = bcsub($jd1, $jd2, $prec);

    return Time::days(-1 * $days);
  }

  public function dayOfYear() {
    $k = $this->isLeapYear() ? 1 : 2;
    $n = intval(275 * $this->month / 9) -
            $k * intval(($this->month + 9) / 12) +
            $this->day - 30;

    return (int)$n;
  }

  // // // Protected

  protected function checkDate($status) {
    switch ($status) {
      case 3:
      //throw new Exception('time is after end of day and dubious year');

      case 2:
        throw new Exception('time is after end of day');

      //case 1:
      //throw new Exception('dubious year');

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

  protected function getComponent($e) {
    $ihmsf = [];
    IAU::D2dtf($this->timescale, $this->prec, $this->jd, $this->dayFrac, $iy,
            $im, $id, $ihmsf);

    switch ($e) {
      case 'year':
        return $iy;

      case 'month':
        return $im;

      case 'day':
        return $id;

      case 'hour':
        return $ihmsf[0];

      case 'min':
        return $ihmsf[1];

      case 'sec':
        return $ihmsf[2];

      case 'micro':
        return $ihmsf[3];
    }
  }

  protected function dstOffset(Timezone $tz) {


    // DST start
    // $md1  = (new AstroDate($this->year, 3, 1));
    // $dst1 = $md1->dayOfYear() + 14 - $md1->weekDayNum() + (2 / 24);
    // DST end
    //$nd1  = (new AstroDate($this->year, 11, 1));
    //$dst2 = $nd1->dayOfYear() + 14 - $nd1->weekDayNum() + (2 / 24);
    $dst1 = 40;
    $dst2 = 300;

    $dyf = $this->dayOfYear() + $this->dayFrac;

    if ($tz->dst)
      if ($dyf >= $dst1 && $dyf < $dst2)
        return $tz->offset + 1;
      else
        return $tz->offset;
    else
      return $tz->offset;
  }

  protected function weekDayNum() {
    return ($this->jd + 1.5) % 7;
  }

  // // // Static

  protected static function monthNum($month) {
    switch (strtolower(substr($month, 0, 3))) {
      case 'jan':
        return 1;
      case 'feb':
        return 2;
      case 'mar':
        return 3;
      case 'apr':
        return 4;
      case 'may':
        return 5;
      case 'jun':
        return 6;
      case 'jul':
        return 7;
      case 'aug':
        return 8;
      case 'sep':
        return 9;
      case 'oct':
        return 10;
      case 'nov':
        return 11;
      case 'dec':
        return 12;
    }
  }

  protected static function ordinal($number) {
    $sn = (string)$number;

    if ($number < 11 || $number > 13) {
      if (substr($sn, strlen($sn) - 1, 1) == 1)
        return 'st';
      if (substr($sn, strlen($sn) - 1, 1) == 2)
        return 'nd';
      if (substr($sn, strlen($sn) - 1, 1) == 3)
        return 'rd';
      else
        return 'th';
    }
    else {
      return 'th';
    }
  }

  // // // Overrides

  public function __toString() {
    return $this->format('Y-M-d H:i:s.u T');


    $ihmsf = [];
    IAU::D2dtf($this->timescale, $this->prec, $this->jd, $this->dayFrac, $iy,
            $im, $id, $ihmsf);

    $date = sprintf("%4d-%s-%02.2d", abs($iy), $this->monthName(), $id);
    $time = sprintf("%02d:%02.2d:%02.2d%s", $ihmsf[0], $ihmsf[1], $ihmsf[2],
            $ihmsf[3] > 0 ? '.' . substr($ihmsf[3], 0, 3) : '');

    $era = $this->era == 'B.C.' ? "$this->era " : '';

    if ($this->timescale == TimeScale::UTC())
      return "{$era}{$date} $time $this->timezone";
    else
      return "{$era}{$date} $time $this->timescale";
  }

}
