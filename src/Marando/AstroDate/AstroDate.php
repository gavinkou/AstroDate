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
use \Marando\IAU\IAU;
use \Marando\IERS\IERS;
use \Marando\Units\Angle;
use \Marando\Units\Time;

/**
 * Represents a date and provides astronomy related functionality
 *
 * @property float     $era       Era
 * @property float     $year      Year
 * @property float     $month     Month
 * @property float     $day       Day
 * @property float     $hour      Hour
 * @property float     $min       Minute
 * @property float     $sec       Second
 * @property float     $micro     Milliseconds
 * @property TimeZone  $timezone  Time zone
 * @property TimeScale $timescale Astronomical time scale, e.g. UTC, TAI, TT
 */
class AstroDate {

  use \Marando\Units\Traits\CopyTrait,
      \Marando\AstroDate\Traits\FormatTrait;

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

  /**
   * 2015-Nov-16 17:07:07.120 UTC
   */
  const FORMAT_DEFAULT = 'Y-M-d H:i:s.u T';

  /**
   * A.D. 2015-Nov-03 19:43:43.180 TT
   */
  const FORMAT_JPL = 'r Y-M-d H:i:s.u T';

  /**
   * A.D. 2015-Nov-3.8223053 TT
   */
  const FORMAT_JPL_FRAC = 'r Y-M-c T';

  /**
   * Monday, November 16, 2015 8:20 AM (UTC)
   */
  const FORMAT_GOOGLE = 'l, F j, Y g:i A (T)';

  /**
   * 2010 Jan. 4.0 TT
   */
  const FORMAT_EPOCH = 'Y M. c T';

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new AstroDate
   *
   * @param int       $year      Year
   * @param int       $month     Month
   * @param int       $day       Day
   * @param int       $hour      Hour
   * @param int       $min       Minutes
   * @param float     $sec       Seconds
   * @param TimeZone  $timezone  Time zone
   * @param TimeScale $timescale Astronomical time standard
   */
  public function __construct($year = null, $month = null, $day = null,
          $hour = null, $min = null, $sec = null, $timezone = null,
          $timescale = null) {

    // Set time scale, default to UTC
    $this->timescale = $timescale ? $timescale : TimeScale::UTC();

    // If time scale is not UTC... set time zone to UTC, otherwise set time zone
    if ($this->timescale != TimeScale::UTC())
      $this->timezone = TimeZone::UTC();
    else
      $this->timezone = $timezone ? $timezone : TimeZone::UTC();

    // Civil date -> JD and fractional day
    $status = IAU::Dtf2d($this->timezone->name, (int)$year, (int)$month,
                    (int)$day, (int)$hour, (int)$min, (float)$sec, $this->jd,
                    $this->dayFrac);

    // Verify date is valid (will throw exceptions if not)
    $this->checkDate($status);

    // Add time zone offset
    if ($this->timezone != TimeZone::UTC()) {
      $tzOffset = $this->timezone->offset($this->toJD());
      $this->add(Time::hours($tzOffset));
    }

    // Set default format
    $this->format = static::FORMAT_DEFAULT;
  }

  // // // Static

  /**
   * Creates a new AstroDate from a Julian date
   *
   * @param  float     $jd        Julian date
   * @param  TimeScale $timescale Astronomical time standard, e.g. TAI or TT
   * @return static
   */
  public static function jd($jd, TimeScale $timescale = null) {
    // JD -> Y-M-D H:M:S.m
    $t = [];
    IAU::D2dtf($timescale, 14, $jd, 0, $y, $m, $d, $t);

    // Create new instance from date/time components
    return new static($y, $m, $d, $t[0], $t[1], $t[2], null, $timescale);
  }

  /**
   * Creates a new AstroDate from a Modified Julian date
   *
   * @param  float     $mjd       Modified Julian date
   * @param  TimeScale $timescale Astronomical time standard, e.g. TAI or TT
   * @return static
   */
  public static function mjd($mjd, TimeScale $timescale = null) {
    return static::jd($mjd + static::MJD, $timescale);
  }

  /**
   * Creates a new AstroDate from the current date and time
   * @return static
   */
  public static function now(TimeZone $timezone = null) {
    // Get current time as micro unix timestamp
    $now   = explode(' ', microtime());
    $unix  = $now[1];
    $micro = Time::sec($now[0]);

    // Compoute JD from unix timestamp
    $jd = ($unix / 86400.0) + static::UJD;

    // Add timezone if none present
    if ($timezone == null)
      $timezone = TimeZone::UTC();

    // Return the new date adding the micro portion and provided timezone
    return static::jd($jd)->add($micro)->setTimezone($timezone);
  }

  /**
   * Parses a string into a new AstroDate instance
   * @param  string $datetime String date/time representation
   * @return static
   */
  public static function parse($datetime) {
    // 2015-Nov-16 17:07:07.120 UTC
    $format1 = '^([\+\-]*[0-9]{1,7})-([a-zA-Z]{1,9})-([0-9]{1,2})\s([0-9]{1,2}):([0-9]{1,2}):*([0-9]{0,2})(\.*[0-9]*)\s*([a-zA-Z]*)$';
    if (preg_match("/{$format1}/", $datetime, $t)) {
      $m  = static::monthNum($t[2]);
      $dt = new static($t[1], $m, $t[3], $t[4], $t[5], $t[6]);
      $dt->add(Time::sec($t[7]));

      try {
        $dt->setTimezone($t[8]);
      }
      catch (Exception $e) {
        $dt->timescale = $t[8];
      }

      return $dt;
    }

    // 2015-1-16 17:07:07.120 UTC
    $format2 = '^([\+\-]*[0-9]{1,7})-([0-9]{1,2})-([0-9]{1,2})\s([0-9]{1,2}):([0-9]{1,2}):*([0-9]{0,2})(\.*[0-9]*)\s*([a-zA-Z]*)$';
    if (preg_match("/{$format2}/", $datetime, $t)) {
      $dt = new static($t[1], $t[2], $t[3], $t[4], $t[5], $t[6]);
      $dt->add(Time::sec($t[7]));

      try {
        $dt->setTimezone($t[8]);
      }
      catch (Exception $e) {
        $dt->timescale = $t[8];
      }

      return $dt;
    }
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Julian day count (whole day) ...used as IAU 2 part JD with $dayFrac
   * @var float
   */
  protected $jd;

  /**
   * Fractional day ...used as IAU 2 part JD with $jd
   * @var float
   */
  protected $dayFrac;

  /**
   * Time zone of the instance
   * @var TimeZone
   */
  protected $timezone;

  /**
   * The last set timezone. Used for when the last timezone is needed when
   * converting from something like TDB back to UTC
   * @var TimeZone
   */
  protected $timezone0;

  /**
   * Astronomical time scale, e.g. UTC, TAI, TT
   * @var TimeScale
   */
  protected $timescale;

  /**
   * Current toString format
   * @var string
   */
  protected $format;

  /**
   * Decimal precision of this instance (NDP for IAU methods)
   * @var int
   */
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

      default:
        throw new Exception("{$name} is not a valid or public property");
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

  /**
   * Sets the date for this instance
   *
   * @param  int    $year  Year
   * @param  int    $month Month
   * @param  int    $day   Day
   * @return static
   */
  public function setDate($year, $month, $day) {
    $status = IAU::Cal2jd((int)$year, (int)$month, (int)$day, $djm0, $djm);
    $this->checkDate($status);  // Check date is valid

    $this->jd = $djm0 + $djm;  // Only set JD, keep day frac to save time
    return $this;
  }

  /**
   * Sets the time of this instance
   *
   * @param  int    $hour Hour
   * @param  int    $min  Minute
   * @param  float  $sec  Second
   * @return static
   */
  public function setTime($hour, $min, $sec) {
    $status = IAU::Tf2d('+', (int)$hour, (int)$min, (float)$sec, $days);
    $this->checkTime($status);  // Check time is valid

    $this->dayFrac = $days;  // Only set the day fraction
    return $this;
  }

  /**
   * Sets the date and time of this instance
   *
   * @param  int    $year  Year
   * @param  int    $month Month
   * @param  int    $day   Day
   * @param  int    $hour  Hour
   * @param  int    $min   Minute
   * @param  float  $sec   Second
   * @return static
   */
  public function setDateTime($year, $month, $day, $hour, $min, $sec) {
    return $this->setDate($year, $month, $day)->setTime($hour, $min, $sec);
  }

  /**
   * Sets the time zone of this instance
   *
   * @param  TimeZone|string $timezone Either a TimeZone instance or string
   * @return static
   *
   * @throws InvalidArgumentException Occurs if the timezone is an invalid type
   */
  public function setTimezone($timezone) {
    // Check type, and parse string if present
    if (is_string($timezone))
      $timezone = TimeZone::name($timezone);
    else if ($timezone instanceof TimeZone == false)
      throw new \InvalidArgumentException();

    // Convert back to UTC
    $this->toUTC();

    // Compute new UTC offset
    $jd       = $this->toJD();
    $tzOffset = $timezone->offset($jd) - $this->timezone->offset($jd);
    $this->add(Time::hours($tzOffset));

    // Set the timezone
    $this->timezone  = $timezone;
    $this->timezone0 = $timezone;

    return $this;
  }

  /**
   * Returns a Julian date representation of this instance.
   *
   * @param  int          $scale Optional additional decimal precision
   * @return float|string
   */
  public function toJD($scale = null) {
    if ($scale)
      return bcadd((string)$this->jd, (string)$this->dayFrac, $scale);
    else
      return $this->jd + $this->dayFrac;
  }

  /**
   * Returns a Modified Julian date representation of this instance.
   *
   * @param  int          $scale Optional additional decimal precision
   * @return float|string
   */
  public function toMJD($scale = null) {
    $mjd = static::MJD;

    if ($scale)
      return bcsub(bcadd($this->jd, $this->dayFrac, $scale), $mjd, $scale);
    else
      return $this->jd + $this->dayFrac - $mjd;
  }

  /**
   * Adds a Time interval to this instance
   *
   * @param  Time   $t
   * @return static
   */
  public function add(Time $t) {
    // Interval to add as days
    $td = $t->days;

    // Days (jda) and day fraction (dfa) to add
    $jda = intval($td);
    $dfa = $this->dayFrac + $td - $jda;

    // Handle the event that the day fraction becomes negative
    if ($dfa < 0) {
      $dfa += 1;
      $jda -= 1;
    }

    // Additional day to add from above day frac in excess of 1 day
    $jda1 = intval($dfa);

    // Since additional day has been added, modulate day frac to range 0-1
    $dfa = fmod($dfa, 1);

    // Add the intervals
    $this->jd      = $this->jd + $jda + $jda1;
    $this->dayFrac = $dfa;

    return $this;
  }

  /**
   * Subtracts a Time interval from this instance
   *
   * @param  Time   $t
   * @return static
   */
  public function sub(Time $t) {
    return $this->add(Time::sec($t->sec * -1));
  }

  /**
   * Converts this instance to the Coordinated Universal Time scale (UTC), or
   * if a time zone was set then that timezone
   *
   * @return static
   * @throws Exception Occurs if UTC cannot be computed
   */
  public function toUTC() {
    if ($this->timescale == TimeScale::UTC()) {
      // Remove the timezone and set to UTC
      $offset         = $this->timezone->offset($this->toJD());
      $this->sub(Time::hours($offset));
      $this->timezone = $this->timezone0 ? $this->timezone0 : TimeZone::UTC();
      return $this;
    }

    // TAI -> UTC
    if ($this->timescale == TimeScale::TAI()) {
      $tai1 = $this->jd;
      $tai2 = $this->dayFrac;
      IAU::Taiutc($tai1, $tai2, $utc1, $utc2);

      $this->jd        = $utc1;
      $this->dayFrac   = $utc2;
      $this->timescale = TimeScale::UTC();
      return $this;
    }

    // TT -> UTC
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

    // UT1 -> UTC
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

    // TDB -> UTC
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

  /**
   * Converts this instance to International Atomic Time (TAI)
   * @return static
   */
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

  /**
   * Converts this instance to Terrestrial Dynamic Time (TT)
   * @return static
   */
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

  /**
   * Converts this instance to Universal Time (UT1)
   * @return static
   */
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

  /**
   * Converts this instance to Barycentric Dynamic Time (TDB)
   * @return static
   */
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

  /**
   * Finds the month name of this instance
   *
   * @param  bool   $full True for full name, false for abbreviation
   * @return string
   */
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

  /**
   * Returns if this instance is a leap year
   * @return bool
   */
  public function isLeapYear() {
    return ($this->year % 4 == 0 && $this->year % 100 != 0) ||
            $this->year % 400 == 0;
  }

  /**
   * Returns the week day name of this instance
   *
   * @param  bool   $full True for full name, false for abbreviation
   * @return string
   */
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

  /**
   * Finds the difference between this AstroDate and another
   * @param  AstroDate $b
   * @return static
   */
  public function diff(AstroDate $b) {
    $prec = 12;
    $jd1  = $this->jd($prec);
    $jd2  = $b->jd($prec);
    $days = bcsub($jd1, $jd2, $prec);

    return Time::days(-1 * $days);
  }

  /**
   * Returns the day of the year number, 0-356 or 0-366 for leap year
   * @return int
   */
  public function dayOfYear() {
    $k = $this->isLeapYear() ? 1 : 2;
    $n = intval(275 * (int)$this->month / 9) -
            $k * intval(((int)$this->month + 9) / 12) +
            (int)$this->day - 30;

    return (int)$n;
  }

  /**
   * Finds the sidereal time of this intsance
   *
   * @param type  $mode Type of sidereal time... (a = apparent, m = mean)
   * @param Angle $lon  If a longitude is supplied, finds local sidereal time,
   *                    otherwise returns sidereal time at Greenwich
   */
  public function sidereal($mode = 'a', Angle $lon = null) {
    // Get UT1 time
    $ut  = $this->copy()->toUT1();
    $uta = $ut->jd;
    $utb = $ut->dayFrac;
    $ut  = null;

    // Get TT time
    $tt  = $this->copy()->toTT();
    $tta = $tt->jd;
    $ttb = $tt->dayFrac;
    $tt  = null;

    // Compute either GMST or GAST
    $st;
    if ($mode == 'a')
      $strad = IAU::Gst06a($uta, $utb, $tta, $ttb);
    else
      $strad = IAU::Gmst06($uta, $utb, $tta, $ttb);

    // Add longitude if relevant
    if ($lon)
      $st = Angle::rad($strad)->add($lon)->norm()->toTime();
    else
      $st = Angle::rad($strad)->toTime();

    // Return as hours
    return $st->setUnit('hours');
  }

  /**
   * Returns the time elapsed since midnight
   * @return Time
   */
  public function sinceMidnight() {
    return Time::days($this->dayFrac)->setUnit('hours');
  }

  /**
   * Returns the time left until since midnight
   * @return Time
   */
  public function untilMidnight() {
    return Time::days(1 - $this->dayFrac)->setUnit('hours');
  }

  // // // Protected

  /**
   * Throws an exception for a provided IAU date error code
   *
   * @param  int       $status IAU date error
   * @throws Exception
   */
  protected function checkDate($status) {
    switch ($status) {
      case 3:  // Ignore dubious year
      //throw new Exception('time is after end of day and dubious year');

      case 2:
        throw new Exception('time is after end of day');

      //case 1:   // Ignore dubious year
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

      default:
        return;
    }
  }

  /**
   * Throws an exception for a provided IAU time error code
   *
   * @param  int       $status IAU time error
   * @throws Exception
   */
  protected function checkTime($status) {
    switch ($status) {
      case 1:
        throw new Exception('hour outside range 0-23');

      case 2:
        throw new Exception('min outside range 0-59');

      case 3:
        throw new Exception('sec outside range 0-59.999...');

      default:
        return;
    }
  }

  /**
   * Gets a component of this date
   *
   * @param  string    $e Component name, e.g. year, month, etc...
   * @return int|float
   */
  protected function getComponent($e) {
    // JD -> Date
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

  /**
   * Calculates a number representing the day of the week for this instance
   * @return int
   */
  protected function weekDayNum() {
    return ($this->jd + 1.5) % 7;
  }

  // // // Static

  /**
   * Finds the number for a month represented as a string
   * @param  string $month
   * @return int
   */
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

  /**
   * Finds the ordinal suffix for a number, e.g. 1st, 2nd, 3rd, etc...
   * @param  int    $number
   * @return string
   */
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

  /**
   * Represents this instance as a string with the current format
   * @return string
   */
  public function __toString() {
    return $this->format($this->format);
  }

}
