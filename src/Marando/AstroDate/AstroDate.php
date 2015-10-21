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
use \Exception;
use \Marando\AstroDate\TimeStandard;
use \Marando\Units\Time;
use \SplFileObject;

/**
 * @property string       $era     Era (A.D. / B.C.) of the set date
 * @property int          $year    Year number
 * @property int          $month   Month number
 * @property int          $day     Day number
 * @property int          $hour    Hour
 * @property int          $min     Minute
 * @property float        $sec     Second
 * @property float        $jd      Julian day number of the set date
 * @property int          $leapSec Current # of leap seconds as of the set date
 * @property TimeStandard $timeStd Astronomical time standard, ex. UTC or TDB
 */
class AstroDate {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new AstroDate instance
   *
   * @param int          $year  Year
   * @param int          $month Month number
   * @param int          $day   Day number
   * @param int          $hour  Hours
   * @param int          $min   Minute
   * @param float        $sec   Seconds
   * @param string       $tz    UT timezone
   * @param TimeStandard $ts    Astronomical time standard, ex. UTC or TDB
   */
  public function __construct($year = null, $month = null, $day = null,
          $hour = null, $min = null, $sec = null, $tz = null,
          TimeStandard $ts = null) {

    // Set a default date
    $this->jd = 2451544.5;
    $this->ts = TimeStandard::UTC();

    // Set the date
    if ($year)
      $this->year  = (int)$year;
    if ($month)
      $this->month = (int)$month;
    if ($day)
      $this->day   = (int)$day;
    if ($hour)
      $this->hour  = (int)$hour;
    if ($min)
      $this->min   = (int)$min;
    if ($sec)
      $this->sec   = (float)$sec;
    if ($ts)
      $this->ts    = $ts;
  }

  /**
   * Creates a new AstroDate instance from a Julian day number
   *
   * @param  float        $jd Julian day number
   * @param  TimeStandard $ts Astronomical time standard, ex UTC or TDB
   * @return static
   */
  public static function jd($jd, TimeStandard $ts = null) {
    $d = new static();

    $d->setJD($jd);
    $d->ts = $ts;

    return $d;
  }

  /**
   * Creates a new AstroDate instance from a string value
   *
   * @param  string    $date Date string to parse
   * @return static
   * @throws Exception       Occurs if the strinc cannot be parsed
   */
  public static function parse($date) {
    // Try parsing date
    try {
      // Use DateTime to parse for time being
      $dt = (new DateTime($date))->format('Y-m-d H:i:s.u');

      $year  = (int)substr($dt, 0, 4);
      $month = (int)substr($dt, 5, 2);
      $day   = (int)substr($dt, 8, 2);
      $hour  = (int)substr($dt, 11, 2);
      $min   = (int)substr($dt, 14, 2);
      $sec   = (int)substr($dt, 17, strlen($dt) - 17);

      // Parse astronomical time standard
      if (preg_match('/(UTC|TDB|TAI|TT)/', strtoupper($date), $matches))
        $ts = new TimeStandard($matches[0]);

      return new static($year, $month, $day, $hour, $min, $sec, null, $ts);
    }
    catch (Exception $ex) {
      throw new Exception("Unable to parse date '{$date}'");
    }
  }

  /**
   * Creates a new AstroDate instance with the current time
   * @return static
   */
  public static function now() {
    // Get unix Timestamp with milliseconds
    $mt   = explode(' ', microtime());
    $unix = $mt[1];  // Unix timestamp

    $year  = (int)date('Y', $unix);
    $month = (int)date('m', $unix);
    $day   = (int)date('d', $unix);
    $hour  = (int)date('H', $unix);
    $min   = (int)date('i', $unix);
    $sec   = (int)date('s', $unix);
    $micro = (float)str_replace('0.', '.', $mt[0]);  // Remove 0.

    return new static($year, $month, $day, $hour, $min, $sec + $micro);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Year
   * @var int
   */
  protected $year;

  /**
   * Month
   * @var int
   */
  protected $month;

  /**
   * Day
   * @var int
   */
  protected $day;

  /**
   * Hour
   * @var int
   */
  protected $hour;

  /**
   * Minute
   * @var int
   */
  protected $min;

  /**
   * Second
   * @var float
   */
  protected $sec;

  /**
   * Timezone
   * @var type
   */
  protected $tz;

  /**
   * Time standard
   * @var TimeStandard
   */
  protected $ts;

  public function __get($name) {
    if ($name == 'jd')
      return $this->getJD();

    if ($name == 'era')
      return $this->getEra();

    if ($name == 'leapSec')
      return $this->getLeapSec();

    throw new Exception("{$name} is not a valid property");
  }

  public function __set($name, $value) {
    switch ($name) {
      case 'jd':
        $this->setJD($value);
        break;

      // Pass through to property
      case 'era':
      case 'year':
      case 'month':
      case 'day':
      case 'hour':
      case 'min':
      case 'sec':
        $this->{$name} = $value;
        break;

      default:

        throw new Exception("{$name} is not a valid property");
    }
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Converts this instance to Coordinated Universal Time (UTC)
   * @return static
   */
  public function toUTC() {
    // Check if instance already in UTC
    if ($this->ts == TimeStandard::UTC())
      return $this;

    /**
     * TAI -> UTC
     * ----------
     * UTC = TAI - (number of leap seconds)
     */
    if ($this->ts == TimeStandard::TAI()) {
      $leapSec  = $this->getLeapSec();
      $this->jd = $this->jd - ($leapSec / Time::SEC_IN_DAY);
      $this->ts = TimeStandard::UTC();
    }

    /**
     * TT -> UTC
     * ---------
     * UTC = TT - (number of leap seconds) - 32.184
     */
    if ($this->ts == TimeStandard::TT()) {
      $leapSec  = $this->getLeapSec();
      $this->jd = $this->jd - (($leapSec + 32.184) / Time::SEC_IN_DAY);
      $this->ts = TimeStandard::UTC();
    }

    /**
     * TDB -> UTC
     */
    if ($this->ts == TimeStandard::TDB()) {
      $leapSec  = $this->getLeapSec();
      $g        = 357.53 + 0.9856003 * ($this->jd - 2451545.0);
      $ttTDB    = 0.001658 * sin($g) + 0.000014 * sin(2 * $g);
      $jd       = $this->jd - ($ttTDB / Time::SEC_IN_DAY);
      $this->jd = $jd - (($leapSec + 32.184) / Time::SEC_IN_DAY);
      $this->ts = TimeStandard::UTC();
    }

    return $this;
  }

  /**
   * Converts this instance to International Atomic Time (TAI)
   * @return static
   */
  public function toTAI() {
    // Check if instance is already TAI
    if ($this->ts == TimeStandard::TAI())
      return $this;

    // Convert to UTC
    $this->toUTC();

    /**
     * UTC -> TAI
     * ----------
     * TAI = UTC + (number of leap seconds)
     */
    $leapSec  = $this->getLeapSec();
    $this->jd = $this->jd + ($leapSec / Time::SEC_IN_DAY);
    $this->ts = TimeStandard::TAI();

    return $this;
  }

  /**
   * Converts this instance to Terrestrial Dynamic Time (TT or TDT)
   * @return static
   */
  public function toTT() {
    // Check if instance already in TT
    if ($this->ts == TimeStandard::TT())
      return $this;

    // Convert to UTC
    $this->toUTC();

    /**
     * UTC -> TT
     * ---------
     * TT = UTC + (number of leap seconds) + 32.184
     */
    $leapSec  = $this->getLeapSec();
    $this->jd = $this->jd + (($leapSec + 32.184) / Time::SEC_IN_DAY);
    $this->ts = TimeStandard::TT();

    return $this;
  }

  /**
   * Converts this instance to Barycentric Dynamic Time (TDB)
   * @return static
   */
  public function toTDB() {
    // Check if already in TDB
    if ($this->ts == TimeStandard::TDB())
      return $this;

    // Convert to TT
    $this->toTT();

    /**
     * TT -> TDB
     * ---------
     * g   = 357.53 + 0.9856003 ( JD - 2451545.0 )	      degrees
     * TDB = TT + 0.001658 sin( g ) + 0.000014 sin( 2g )  seconds
     */
    $g        = 357.53 + 0.9856003 * ($this->jd - 2451545.0);
    $ttTDB    = 0.001658 * sin($g) + 0.000014 * sin(2 * $g);
    $this->jd = $this->jd + $ttTDB / 86400;

    $this->ts = TimeStandard::TDB();
    return $this;
  }

  /**
   * Finds the time difference between this date and another
   * @param AstroDate $dateB
   * @return Time
   */
  public function diff(AstroDate $dateB) {
    $diffJD = $this->jd - $dateB->jd;
    return Time::days($diffJD);
  }

  /**
   * Adds a time interval to this instance
   * @param Time $t
   * @return static
   */
  public function add(Time $t) {
    $this->jd += $t->sec / Time::SEC_IN_DAY;
    return $this;
  }

  /**
   * Subtracts a time interval from this instance
   * @param Time $t
   * @return static
   */
  public function subtract(Time $t) {
    $this->jd -= $t->sec / Time::SEC_IN_DAY;
    return $this;
  }

  /**
   * Gets the total time elapsed since midnight for this instance
   * @return Time
   */
  public function sinceMidnight() {
    $secSinceMidnight = $this->hour * 3600 + $this->min * 60 + $this->sec;
    return Time::sec($secSinceMidnight);
  }

  /**
   * Gets the total time left until midnight for this instance
   * @return Time
   */
  public function untilMidnight() {
    return Time::hours(24)->subtract($this->sinceMidnight());
  }

  /**
   * Represents this instance as a string in the format:
   *
   *   2015-Oct-21 01:34:59.884 UTC
   *
   *
   * @return string
   */
  public function formatDefault() {
    $year  = $this->year;
    $month = static::findMonthName($this->month);
    $hour  = sprintf('%02d', $this->hour);
    $min   = sprintf('%02d', $this->min);
    $sec   = str_pad(sprintf('%0.3f', $this->sec), 6, '0', STR_PAD_LEFT);
    $day   = sprintf('%02d', $this->day);
    $ts    = $this->ts;

    // Check if milliseconds, if not round sec to 0
    if ((float)$sec == intval($sec))
      $sec = sprintf('%02d', $this->sec);

    // Format string
    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec} {$ts}";
  }

  /**
   * Represents this instance as a string in JPL's format:
   *
   *   A.D. 2015-Oct-21.0970332 UTC
   *
   *
   * If time is true, then shows the time with hh:mm:ss
   *
   *   A.D. 2015-Oct-21 02:19:43.671 UTC
   *
   *
   * @param bool $time True to show time as hh:mm:ss
   * @return string
   */
  public function formatJPL($time = false) {
    $era   = $this->era;
    $year  = $this->year;
    $month = static::findMonthName($this->month);
    $hour  = sprintf('%02d', $this->hour);
    $min   = sprintf('%02d', $this->min);
    $sec   = sprintf('%05.3f', $this->sec);
    $hours = ($this->hour * Time::SEC_IN_HOUR + $this->min *
            Time::SEC_IN_MIN + $this->sec) / Time::SEC_IN_DAY;
    $day   = sprintf('%02d', $this->day);
    $dayF  = round($this->day + $hours, 7);
    $ts    = $this->ts;

    if ($time)
      return trim("{$era} {$year}-{$month}-{$day} {$hour}:{$min}:{$sec} {$ts}");
    else
      return trim("{$era} {$year}-{$month}-{$dayF } {$ts}");
  }

  // // // Protected

  /**
   * Gets the Julian day count of this instance
   * @return float
   */
  protected function getJD() {
    // Get base JD and add day fraction since midnight
    $jd = static::CalToJD($this->year, $this->month, $this->day);
    $jd += $this->sinceMidnight()->days;

    return $jd;
  }

  /**
   * Sets the properties of this instance based on a Julian day count
   * @param float $jd
   */
  protected function setJD($jd) {
    // Get base JD
    list($year, $month, $day) = static::JDtoCal($jd);

    // Set base YMD
    $this->year  = $year;
    $this->month = $month;
    $this->day   = intval($day);

    // Get total remaining seconds
    $rsec = 86400 * ($day - intval($day));

    // Figure out HMS
    $hour = intval($rsec / Time::SEC_IN_HOUR);
    $min  = intval($rsec % Time::SEC_IN_MIN);
    $sec  = $rsec - ($hour * Time::SEC_IN_HOUR + $min * Time::SEC_IN_MIN);

    // Set HMS
    $this->hour = $hour;
    $this->min  = $min;
    $this->sec  = $sec;
  }

  /**
   * Gets the era of this instance, A.D. or B.C.
   * @return string
   */
  protected function getEra() {
    return $this->year > 0 ? 'A.D.' : 'B.C.';
  }

  /**
   * Sets the era of this instance
   * @throws Exception
   */
  protected function setEra() {
    throw new Exception('Not implemented');
  }

  /**
   * Loads a file containing leap second data from IETF
   * @return SplFileObject
   */
  protected function loadLeapSecFile() {
    $fname = 'leap-seconds.list';
    $url   = 'https://www.ietf.org/timezones/data/leap-seconds.list';

    // Only downloads if no file...
    // TODO: check the file's check expiration date
    if (!file_exists($fname))
      exec("curl {$url} > {$fname}");

    return new SplFileObject($fname);
  }

  /**
   * Finds the number of leap seconds as of the date of this instance
   * @return int
   */
  protected function getLeapSec() {
    // Get leap second file
    $file = $this->loadLeapSecFile();

    $data = [];  // Iterate through each line in the file
    for ($i = 0; $i < PHP_INT_MAX; $i++) {
      // Check for EOF
      if ($file->eof())
        break;

      // Seek i-th line
      $file->seek($i);

      // Split the line into an array of values
      $line     = str_replace("\t", ' ', $file->current());
      $split    = explode(' ', $line);
      $filtered = array_filter($split);
      $values   = array_values($filtered);

      // No values, continue
      if (count($values) == 0)
        continue;

      // Comment, continue
      if (substr($values[0], 0, 1) == "#")
        continue;

      /**
       * The first column shows an epoch as a number of seconds since
       * 1900-Jan-1, while the second column shows the number of seconds that
       * must be added to UTC to compute TAI for any timestamp at or after that
       * epoch.
       *
       * ...so the procedure is to add that second value to the epoch to get a
       * date instance of the leap second.
       */
      $epoch = new AstroDate(1900, 1, 1);
      $date  = $epoch->add(Time::sec($values[0]));

      // Add the leap second data [leap_sec_date, total_leap_sec]
      $data[] = [$date, $values[1]];
    }

    // Find leap seconds
    $leapSec = 0;
    foreach ($data as $row) {
      // Diff the current time until this date is reached
      $diff = $this->diff($row[0]);
      if ($diff->sec < 0)
        break;

      // Use last leap second value
      $leapSec = $row[1];
    }

    return $leapSec;
  }

  // // // Static

  /**
   * Finds the month name for a month integer
   * @param  int    $month Month number
   * @param  bool   $full  True returns full name, false returns abbreviation
   * @return string
   */
  protected static function findMonthName($month, $full = false) {
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

    return $months[$month - 1][$full ? 2 : 1];
  }

  /**
   * Converts a calendar date to a Julian day number
   *
   * @param  int   $y Year
   * @param  int   $m Month
   * @param  int   $d Day
   * @return float    Resulting Julian day
   *
   * @see Meeus, Jean. "Calculation of the JD." Astronomical Algorithms.
   *          Richmond, Virg.: Willmann-Bell, 2009. 60. Print.
   */
  protected static function CalToJD($y, $m, $d) {
    if ($m == 1 || $m == 2) {
      $y--;
      $m += 12;
    }

    $a = static::FloorDiv($y, 100);
    $b = 2 - $a + static::FloorDiv($a, 4);

    // Equation 7.1 (p.61)
    $jd = intval(365.25 * ($y + 4716)) +
            intval(30.6001 * ($m + 1)) +
            $d + $b - 1524.5;

    return $jd;
  }

  /**
   * Calculates integer division on a numerator and denominator
   *
   * @param  float $n  Numerator
   * @param  float $d  Denominator
   * @return int
   * @throws Exception Occurs if denominator is zero
   */
  protected static function FloorDiv($n, $d) {
    if ($d != 0)
      return intval($n / $d);
    else
      throw new Exception('Cannot divide by zero');
  }

  /**
   * Calculates the calendar year, month, and day of a Julian day number
   *
   * @param  float $jd
   * @return array [y, m, d]
   *
   * @see Meeus, Jean. "Calculation of the JD." Astronomical Algorithms.
   *          Richmond, Virg.: Willmann-Bell, 2009. 60. Print.
   */
  protected static function JDtoCal($jd) {
    $jd += 0.5;
    $z = intval($jd);
    $f = ($jd * 100 - $z * 100) / 100;   // * 100 to avoid float round issues.

    $a = $z;
    if ($z >= 2291161) {
      $α = static::FloorDiv($z - 1867216.25, 36524.25);
      $a = $z + 1 + $α - static::FloorDiv($α, 4);
    }

    $b = $a + 1524;
    $c = static::FloorDiv($b - 122.1, 365.25);
    $d = intval(365.25 * $c);
    $e = static::FloorDiv($b - $d, 30.6001);

    $d = $b - $d - intval(30.6001 * $e) + $f;
    $m = $e < 14 ? $e - 1 : $e - 13;
    $y = $m > 2 ? $c - 4716 : $c - 4715;

    return [$y, $m, $d];
  }

  // // // Overrides

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    return $this->formatDefault();
  }

}
