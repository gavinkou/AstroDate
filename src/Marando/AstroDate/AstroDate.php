<?php

namespace Marando\AstroDate;

use \DateTime;
use \Exception;
use \Marando\Units\Time;
use \SplFileObject;

/**
 * @property int   $year
 * @property int   $month
 * @property int   $day
 * @property int   $hour
 * @property int   $min
 * @property float $sec
 *
 * @property float $jd
 * @property string $era
 * @property int $leapSec
 */
class AstroDate {

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct($year = null, $month = null, $day = null,
          $hour = null, $min = null, $sec = null, $tz = null, TimeStd $ts = null) {

    $this->jd = 2451544.5;
    $this->ts = TimeStd::UTC();

    if ($year)
      $this->year = (int)$year;

    if ($month)
      $this->month = (int)$month;

    if ($day)
      $this->day = (int)$day;

    if ($hour)
      $this->hour = (int)$hour;

    if ($min)
      $this->min = (int)$min;

    if ($sec)
      $this->sec = (int)$sec;

    if ($ts)
      $this->ts = $ts;
  }

  public static function jd($jd, TimeStd $ts = null) {
    $d = new AstroDate();

    $d->setJD($jd);
    $d->ts = $ts;

    return $d;
  }

  public static function parse($date) {
    try {
      $dt = (new DateTime($date))->format('Y-m-d H:i:s.u');

      $year  = (int)substr($dt, 0, 4);
      $month = (int)substr($dt, 5, 2);
      $day   = (int)substr($dt, 8, 2);
      $hour  = (int)substr($dt, 11, 2);
      $min   = (int)substr($dt, 14, 2);
      $sec   = (int)substr($dt, 17, strlen($dt) - 17);

      return new static($year, $month, $day, $hour, $min, $sec, null, null);
    }
    catch (Exception $ex) {
      throw new Exception("Unable to parse date '{$date}'");
    }
  }

  public static function now() {
    $now = date('Y-m-d H:i:s.u', time());
    return static::parse($now);
  }

  //
  // Properties
  //

      protected $year;
  protected $month;
  protected $day;
  protected $hour;
  protected $min;
  protected $sec;
  protected $tz;
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

  //
  // Functions
  //

      protected function getJD() {
    $jd          = static::CalToJD($this->year, $this->month, $this->day);
    $secMidnight = $this->hour * 3600 + $this->min * 60 + $this->sec;
    $dayMidnight = $secMidnight / 86400;

    return $jd + $dayMidnight;
  }

  protected function setJD($jd) {
    list($y, $m, $d) = static::JDtoCal($jd);

    $this->year  = $y;
    $this->month = $m;
    $this->day   = intval($d);

    $sec = 86400 * ($d - intval($d));

    $h   = intval($sec / 3600);
    $min = intval($sec % 3600 / 60);
    $s   = $sec - ($h * 3600 + $min * 60);


    $this->hour = $h;
    $this->min  = $min;
    $this->sec  = $s;
  }

  public function toTAI() {
    if ($this->ts == TimeStd::TAI())
      return $this;

    $this->toUTC();

    $ls       = $this->getLeapSec();
    $this->jd = $this->jd + $ls / 86400;
    $this->ts = TimeStd::TAI();

    return $this;
  }

  public function toUTC() {
    if ($this->ts == TimeStd::UTC())
      return $this;

    if ($this->ts == TimeStd::TAI()) {
      $ls       = $this->getLeapSec();
      $this->jd = $this->jd - $ls / 86400;
      $this->ts = TimeStd::UTC();
    }

    if ($this->ts == TimeStd::TT()) {
      $ls       = $this->getLeapSec();
      $this->jd = $this->jd - ($ls + 32.184) / 86400;
      $this->ts = TimeStd::UTC();
    }

    if ($this->ts == TimeStd::TDB()) {
      $ls       = $this->getLeapSec();
      $g        = 357.53 + 0.9856003 * ($this->jd - 2451545.0);
      $ttTDB    = 0.001658 * sin($g) + 0.000014 * sin(2 * $g);
      $jd       = $this->jd - $ttTDB / 86400;
      $this->jd = $jd - ($ls + 32.184) / 86400;
      $this->ts = TimeStd::UTC();
    }

    return $this;
  }

  public function toTT() {
    if ($this->ts == TimeStd::TT())
      return $this;

    $this->toUTC();

    $ls       = $this->getLeapSec();
    $this->jd = $this->jd + ($ls + 32.184) / 86400;
    $this->ts = TimeStd::TT();

    return $this;
  }

  public function toTDB() {
    if ($this->ts == TimeStd::TDB())
      return $this;

    $this->toTT();
    //$this->toUTC();

    $g        = 357.53 + 0.9856003 * ($this->jd - 2451545.0);
    $ttTDB    = 0.001658 * sin($g) + 0.000014 * sin(2 * $g);
    $this->jd = $this->jd + $ttTDB / 86400;

    $this->ts = TimeStd::TDB();
    return $this;
  }

  /**
   *
   * @param AstroDate $d
   * @return Time
   */
  public function diff(AstroDate $d) {
    $jd  = $d->jd;
    $jd0 = $this->jd - $jd;

    return Time::days($jd0);
  }

  // // // Protected

  protected function getEra() {
    return $this->year > 0 ? 'A.D.' : 'B.C.';
  }

  protected function setEra() {
    throw new Exception();
  }

  protected function getLeapSecondsFile() {
    $filename = 'leap-seconds.list';
    $url      = 'https://www.ietf.org/timezones/data/leap-seconds.list';

    if (!file_exists($filename))
      exec("curl {$url} > {$filename}");

    return new SplFileObject($filename);
  }

  protected function getLeapSec() {
    $file = $this->getLeapSecondsFile();

    $data = [];
    for ($i = 0; $i < PHP_INT_MAX; $i++) {
      // Check for EOF
      if ($file->eof())
        break;

      // Seek ith line
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

      // Add the leap second data
      $epoch  = new AstroDate(1900, 1, 1);
      $data[] = [
          //Carbon::parse('Jan 1 1900')->addSeconds($values[0]),
          $epoch->add(Time::sec($values[0])),
          $values[1],
      ];
    }

    $leapSec = 0;
    foreach ($data as $row) {
      $diff = $this->diff($row[0]);
      if ($diff->sec < 0)
        break;

      //if ($row[0]->diffInSeconds(Carbon::create($this->year, $this->month,
      //                        $this->day, $this->hour, $this->min,
      //                       intval($this->sec)), false) < 0)
      //break;

      $leapSec = $row[1];
    }

    return $leapSec;
  }

  public function add(Time $t) {
    $this->jd += $t->sec / 86400;
    return $this;
  }

  public function subtract(Time $t) {
    $this->jd -= $t->sec / 86400;
    return $this;
  }

  public function formatStandard() {
    $year  = $this->year;
    $month = static::monthName($this->month);
    $hour  = sprintf('%02d', $this->hour);
    $min   = sprintf('%02d', $this->min);
    $sec   = str_pad(sprintf('%0.3f', $this->sec), 6, '0', STR_PAD_LEFT);
    $day   = sprintf('%02d', $this->day);
    $ts    = $this->ts;

    if ((int)$sec == intval($sec))
      $sec = sprintf('%02d', $this->sec);

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec} {$ts}";
  }

  public function formatJPL($time = false) {
    // A.D. 2015-Oct-21.0039931

    $era   = $this->era;
    $year  = $this->year;
    $month = static::monthName($this->month);
    $hour  = sprintf('%02d', $this->hour);
    $min   = sprintf('%02d', $this->min);
    $sec   = sprintf('%05.2f', $this->sec);
    $hours = ($this->hour * 3600 + $this->min * 60 + $this->sec) / 86400;
    $day   = sprintf('%02d', $this->day);
    $dayF  = round($this->day + $hours, 7);
    $ts    = $this->ts;

    if ($time)
      return trim("{$era} {$year}-{$month}-{$day} {$hour}:{$min}:{$sec} {$ts}");
    else
      return trim("{$era} {$year}-{$month}-{$dayF} {$ts}");
  }

  // // // Static

  protected static function monthName($month, $full = false) {
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

  protected static function FloorDiv($n, $d) {
    if ($d != 0)
      return intval($n / $d);
    else
      throw new Exception('Cannot divide by zero');
  }

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


  public function __toString() {
    //return $this->formatJPL();
    return $this->formatStandard();


    $sec = round($this->sec, 3);
    $min = sprintf('%02d', $this->min);



    return "{$this->year}-{$this->month}-{$this->day} "
            . "$this->hour:$min:$sec {$this->ts}";
  }

  ////
  ///////
}
