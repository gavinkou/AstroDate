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

trait FormatTrait {

  /**
   * Formats this instance to a string based on a PHP DateTime format string. A
   * few additional formats have been added for the AstroDate library, for more
   * informatin see the documentation.
   *
   * @param  string $format Format, e.g. 'D, d M Y H:i:s'
   * @return string http://php.net/manual/en/function.date.php
   * @see
   */
  public function format($format) {
    // Persist format
    $this->format = $format;

    // Escape format key letters and escaped characters
    $format = preg_replace('/([a-zA-Z])/', '%$1', $format);
    $format = preg_replace('/\\\\%(.)/', '\\\\$1', $format);

    /////////
    // DAY //
    /////////

    $this->format_d($format);
    $this->formatD($format);
    $this->format_j($format);
    $this->format_l($format);
    $this->formatL($format);
    $this->formatN($format);
    $this->formatS($format);
    $this->format_w($format);
    $this->format_z($format);

    //////////
    // WEEK //
    //////////

    $this->formatW($format);

    ///////////
    // MONTH //
    ///////////

    $this->formatF($format);
    $this->format_m($format);
    $this->formatM($format);
    $this->format_n($format);
    $this->format_t($format);

    //////////
    // YEAR //
    //////////

    $this->formatY($format);
    $this->format_y($format);

    //////////
    // TIME //
    //////////

    $this->format_a($format);
    $this->formatA($format);
    $this->format_g($format);
    $this->formatG($format);
    $this->format_h($format);
    $this->formatH($format);
    $this->format_i($format);
    $this->format_s($format);
    $this->format_u($format);

    //////////////
    // TIMEZONE //
    //////////////

    $this->format_e($format);
    $this->formatO($format);
    $this->formatP($format);
    $this->formatZ($format);
    $this->format_r($format);
    $this->format_c($format);

    // Replace escape character and return formatted string
    return str_replace('\\', '', $format);
  }

  // // // // // // // // // //
  // // //  Protected  // // //
  // // // // // // // // // //

  /**
   * Day of the month, 2 digits with leading zeros
   * @param string $str
   */
  private function format_d(&$str) {
    if (strstr($str, '%d'))
      $str = str_replace('%d', sprintf('%02d', $this->day), $str);
  }

  /**
   * Textual representation of a day, three letters
   * @param string $str
   */
  private function formatD(&$str) {
    if (strstr($str, '%D'))
      $str = str_replace('%D', $this->dayName(false), $str);
  }

  /**
   * Day of the month without leading zeros
   * @param type $str
   */
  private function format_j(&$str) {
    if (strstr($str, '%j'))
      $str = str_replace('%j', sprintf('%01d', $this->day), $str);
  }

  /**
   * A full textual uppercase representation of the day of the week
   * @param type $str
   */
  private function format_l(&$str) {
    if (strstr($str, '%l'))
      $str = str_replace('%l', $this->dayName(true), $str);
  }

  /**
   * A full textual lowercase representation of the day of the week
   * @param type $str
   */
  private function formatL(&$str) {
    if (strstr($str, '%L'))
      $str = str_replace('%L', strtolower($this->dayName(true)), $str);
  }

  /**
   * ISO-8601 numeric representation of the day of the week 1=Mon, 7=Sun
   * @param type $str
   */
  private function formatN(&$str) {
    if (strstr($str, '%N')) {
      $wdn = $this->weekDayNum();  // Convert 0=Mon 6=Sun to above format
      $str = str_replace('%N', $wdn == 0 ? 7 : $wdn, $str);
    }
  }

  /**
   * English ordinal suffix for the day of the month, 2 characters
   * @param type $str
   */
  private function formatS(&$str) {
    if (strstr($str, '%S'))
      $str = str_replace('%S', static::ordinal($this->day), $str);
  }

  /**
   * Numeric representation of the day of the week 0=Sun, 6=Sat
   * @param type $str
   */
  private function format_w(&$str) {
    if (strstr($str, '%w'))
      $str = str_replace('%w', $this->weekDayNum(), $str);
  }

  /**
   * The day of the year (starting from 1)
   * @param type $str
   */
  private function format_z(&$str) {
    if (strstr($str, '%z'))
      $str = str_replace('%z', $this->dayOfYear(), $str);
  }

  /**
   * ISO-8601 week number of year, weeks starting on Monday
   * @param type $str
   */
  private function formatW(&$str) {
    if (strstr($str, '%W'))
      $str = str_replace('%W', null, $str);
  }

  /**
   * A full textual representation of a month, such as January or March
   * @param type $str
   */
  private function formatF(&$str) {
    if (strstr($str, '%F'))
      $str = str_replace('%F', $this->monthName(true), $str);
  }

  /**
   * Numeric representation of a month, with leading zeros
   * @param type $str
   */
  private function format_m(&$str) {
    if (strstr($str, '%m'))
      $str = str_replace('%m', sprintf('%02d', $this->month), $str);
  }

  /**
   * A short textual representation of a month, three letters
   * @param type $str
   */
  private function formatM(&$str) {
    if (strstr($str, '%M'))
      $str = str_replace('%M', $this->monthName(), $str);
  }

  /**
   * Numeric representation of a month, without leading zeros
   * @param type $str
   */
  private function format_n(&$str) {
    if (strstr($str, '%n'))
      $str = str_replace('%n', sprintf('%01d', $this->month), $str);
  }

  /**
   * Number of days in the given month
   * @param type $str
   */
  private function format_t(&$str) {
    if (strstr($str, '%t'))
      $str = str_replace('%t', null, $str);
  }

  /**
   * A full numeric representation of a year, 4 digits
   * @param type $str
   */
  private function formatY(&$str) {
    if (strstr($str, '%Y'))
      $str = str_replace('%Y', $this->year, $str);
  }

  /**
   * A two digit representation of a year
   * @param type $str
   */
  private function format_y(&$str) {
    if (strstr($str, '%y'))
      $str = str_replace(
              '%y', substr($this->year, strlen($this->year) - 2, 2), $str);
  }

  /**
   * Lowercase Ante meridiem and Post meridiem
   * @param type $str
   */
  private function format_a(&$str) {
    if (strstr($str, '%a'))
      $str = str_replace('%a', $this->hour < 12 ? 'am' : 'pm', $str);
  }

  /**
   * Uppercase Ante meridiem and Post meridiem
   * @param type $str
   */
  private function formatA(&$str) {
    if (strstr($str, '%A'))
      $str = str_replace('%A', $this->hour < 12 ? 'AM' : 'PM', $str);
  }

  /**
   * 12-hour format of an hour without leading zeros
   * @param type $str
   */
  private function format_g(&$str) {
    if (strstr($str, '%g')) {
      $h   = $this->hour > 12 ? $this->hour - 12 : $this->hour;
      $str = str_replace('%g', sprintf('%1d', $h), $str);
    }
  }

  /**
   * 24-hour format of an hour without leading zeros
   * @param type $str
   */
  private function formatG(&$str) {
    if (strstr($str, '%G'))
      $str = str_replace('%G', sprintf('%1d', $this->hour), $str);
  }

  /**
   * 12-hour format of an hour with leading zeros
   * @param type $str
   */
  private function format_h(&$str) {
    if (strstr($str, '%h')) {
      $h   = $this->hour > 12 ? $this->hour - 12 : $this->hour;
      $str = str_replace('%h', sprintf('%02d', $h), $str);
    }
  }

  /**
   * 24-hour format of an hour with leading zeros
   * @param type $str
   */
  private function formatH(&$str) {
    if (strstr($str, '%H'))
      $str = str_replace('%H', sprintf('%02d', $this->hour), $str);
  }

  /**
   * Minutes with leading zeros
   * @param type $str
   */
  private function format_i(&$str) {
    if (strstr($str, '%i'))
      $str = str_replace('%i', sprintf('%02d', $this->min), $str);
  }

  /**
   * Seconds with leading zeros
   * @param type $str
   */
  private function format_s(&$str) {
    if (strstr($str, '%s'))
      $str = str_replace('%s', sprintf('%02d', $this->sec), $str);
  }

  /**
   * Milliseconds
   * @param type $str
   */
  private function format_u(&$str) {
    if (strstr($str, '%u')) {
      $u   = substr($this->micro, 0, 3);
      $str = str_replace('%u', str_pad($u, 3, '0', STR_PAD_LEFT), $str);
    }
  }

  /**
   * Timezone/Timescale identifier
   * @param type $str
   */
  private function format_e(&$str) {
    if (strstr($str, '%e') || strstr($str, '%T')) {
      $t   = $this->timescale == TimeScale::UTC() ? $this->timezone : $this->timescale;
      $str = str_replace('%e', $t, $str);
      $str = str_replace('%T', $t, $str);
    }
  }

  /**
   * Difference to UTC in hours
   * @param type $str
   */
  private function formatO(&$str) {
    if (strstr($str, '%O')) {
      $o   = $this->timezone->offset;
      $os  = $o >= 0 ? '+' : '-';
      $oh  = sprintf('%02d', abs(intval($o)));
      $om  = sprintf('%02d', abs($o - intval($o)) * 60);
      $ofs = "{$os}{$oh}{$om}";
      $str = str_replace('%O', $ofs, $str);
    }
  }

  /**
   * Difference to UTC in hours with colon
   * @param type $str
   */
  private function formatP(&$str) {
    if (strstr($str, '%P')) {
      $o   = $this->timezone->offset;
      $os  = $o >= 0 ? '+' : '-';
      $oh  = sprintf('%02d', abs(intval($o)));
      $om  = sprintf('%02d', abs($o - intval($o)) * 60);
      $ofs = "{$os}{$oh}:{$om}";
      $str = str_replace('%P', $ofs, $str);
    }
  }

  /**
   * Timezone offset in seconds
   * @param type $str
   */
  private function formatZ(&$str) {
    if (strstr($str, '%Z'))
      $str = str_replace('%Z', $this->timezone->offset * 3600, $str);
  }

  /**
   * Era, A.D. or B.C. (added for AstroDate)
   * @param type $str
   */
  private function format_r(&$str) {
    if (strstr($str, '%r'))
      $str = str_replace('%r', $this->era, $str);
  }

  /**
   * Day with fraction (added for AstroDate)
   * @param type $str
   */
  private function format_c(&$str) {
    if (strstr($str, '%c'))
      if ($this->dayFrac == 0)
        $str = str_replace('%c', "{$this->day}.0", $str);
      else
        $str = str_replace('%c', round($this->day + $this->dayFrac, 7), $str);
  }

}
