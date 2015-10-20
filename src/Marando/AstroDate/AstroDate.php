<?php

namespace Marando\AstroDate;

use \Carbon\Carbon;

/**
 * @property int   $year
 * @property int   $month
 * @property int   $day
 * @property int   $hour
 * @property int   $min
 * @property float $sec
 * @property float $jd
 */
class AstroDate {

  protected $year;
  protected $month;
  protected $day;
  protected $hour;
  protected $min;
  protected $sec;
  protected $tz;
  protected $ts;

  public function __construct($year, $month, $day, $hour, $min, $sec,
          $tz = null, TimeStd $ts = null) {

    $this->year  = $year;
    $this->month = $month;
    $this->day   = $day;
    $this->hour  = $hour;
    $this->min   = $min;
    $this->sec   = $sec;
  }

  protected function getJD() {
    $jd          = $this->calToJD($this->year, $this->month, $this->day);
    $secMidnight = $this->hour * 3600 + $this->min * 60 + $this->sec;
    $dayMidnight = $secMidnight / 86400;

    return $jd + $dayMidnight;
  }

  protected function setJD($jd) {
    list($y, $m, $d) = $this->jdToCal($jd);

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

  public function __get($name) {
    if ($name == 'jd')
      return $this->getJD();
  }

  public function __set($name, $value) {
    if ($name == 'jd')
      $this->setJD($value);
  }

  protected function calToJD($y, $m, $d) {
    if ($m == 1 || $m == 2) {
      $y--;
      $m += 12;
    }

    $a = $this->floorDiv($y, 100);
    $b = 2 - $a + $this->floorDiv($a, 4);

    // Equation 7.1 (p.61)
    $jd = intval(365.25 * ($y + 4716)) +
            intval(30.6001 * ($m + 1)) +
            $d + $b - 1524.5;

    return $jd;
  }

  protected function floorDiv($n, $d) {
    if ($d != 0)
      return intval($n / $d);
    else
      throw new Exception('Cannot divide by zero');
  }

  protected function jdToCal($jd) {
    $jd += 0.5;
    $z = intval($jd);
    $f = ($jd * 100 - $z * 100) / 100;   // * 100 to avoid float round issues.

    $a = $z;
    if ($z >= 2291161) {
      $α = $this->floorDiv($z - 1867216.25, 36524.25);
      $a = $z + 1 + $α - $this->floorDiv($α, 4);
    }

    $b = $a + 1524;
    $c = $this->floorDiv($b - 122.1, 365.25);
    $d = intval(365.25 * $c);
    $e = $this->floorDiv($b - $d, 30.6001);

    $d = $b - $d - intval(30.6001 * $e) + $f;
    $m = $e < 14 ? $e - 1 : $e - 13;
    $y = $m > 2 ? $c - 4716 : $c - 4715;

    return [$y, $m, $d];
  }

  public function __toString() {
    $sec = round($this->sec, 3);
    $min = sprintf('%02d', $this->min);

    return "{$this->year}-{$this->month}-{$this->day} "
            . "$this->hour:$min:$sec {$this->ts}";
  }

  protected function getLeapSecondsFile() {
    $filename = 'leap-seconds.list';
    $url      = 'https://www.ietf.org/timezones/data/leap-seconds.list';

    if (!file_exists($filename))
      exec("curl {$url} > {$filename}");

    return new \SplFileObject($filename);
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
      $data[] = [
          Carbon::parse('Jan 1 1900')->addSeconds($values[0]),
          $values[1],
      ];
    }

    $leapSec = 0;
    foreach ($data as $row) {
      if ($row[0]->diffInSeconds(Carbon::create($this->year, $this->month,
                              $this->day, $this->hour, $this->min,
                              intval($this->sec)), false) < 0)
        break;

      $leapSec = $row[1];
    }

    return $leapSec;
  }

}
