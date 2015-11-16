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

  public function format($format) {
    $str = preg_replace('/([a-zA-Z])/', '%$1', $format);

    // Day of the month, 2 digits with leading zeros
    if (strstr($str, '%d'))
      $str = str_replace('%d', sprintf('%02d', $this->day), $str);

    // A textual representation of a day, three letters
    if (strstr($str, '%D'))
      $str = str_replace('%D', $this->dayName(false), $str);

    // Day of the month without leading zeros
    if (strstr($str, '%j'))
      $str = str_replace('%j', sprintf('%01d', $this->day), $str);

    // A full textual uppercase representation of the day of the week
    if (strstr($str, '%L'))
      $str = str_replace('%L', $this->dayName(true), $str);

    // A full textual lowercase representation of the day of the week
    if (strstr($str, '%l'))
      $str = str_replace('%l', strtolower($this->dayName(true)), $str);

    // ISO-8601 numeric representation of the day of the week 1=Mon, 7=Sun
    if (strstr($str, '%N')) {
      $wdn = $this->weekDayNum();  // Convert 0=Mon 6=Sun to above format
      $str = str_replace('%N', $wdn == 0 ? 7 : $wdn, $str);
    }

    // English ordinal suffix for the day of the month, 2 characters
    if (strstr($str, '%S'))
      $str = str_replace('%S', static::ordinal($this->day), $str);

    // Numeric representation of the day of the week 0=Sun, 6=Sat
    if (strstr($str, '%w'))
      $str = str_replace('%w', $this->weekDayNum(), $str);

    // The day of the year (starting from 1)
    if (strstr($str, '%z'))
      $str = str_replace('%z', $this->dayOfYear(), $str);

    // ISO-8601 week number of year, weeks starting on Monday
    if (strstr($str, '%W'))
      $str = str_replace('%W', null, $str);

    //A full textual representation of a month, such as January or March
    if (strstr($str, '%F'))
      $str = str_replace('%F', $this->monthName(true), $str);

    // Numeric representation of a month, with leading zeros
    if (strstr($str, '%m'))
      $str = str_replace('%m', sprintf('%02d', $this->month), $str);

    // A short textual representation of a month, three letters
    if (strstr($str, '%M'))
      $str = str_replace('%M', $this->monthName(), $str);

    // Numeric representation of a month, without leading zeros
    if (strstr($str, '%n'))
      $str = str_replace('%n', sprintf('%01d', $this->month), $str);

    // Number of days in the given month
    if (strstr($str, '%t'))
      $str = str_replace('%t', null, $str);

    // A full numeric representation of a year, 4 digits
    if (strstr($str, '%Y'))
      $str = str_replace('%Y', $this->year, $str);

    // A two digit representation of a year
    if (strstr($str, '%y'))
      $str = str_replace('%y', substr($this->year, strlen($this->year) - 2, 2),
              $str);

    // Lowercase Ante meridiem and Post meridiem
    if (strstr($str, '%a'))
      $str = str_replace('%a', $this->hour < 12 ? 'am' : 'pm', $str);

    // Uppercase Ante meridiem and Post meridiem
    if (strstr($str, '%A'))
      $str = str_replace('%A', $this->hour < 12 ? 'AM' : 'PM', $str);

    // 12-hour format of an hour without leading zeros
    if (strstr($str, '%g')) {
      $h   = $this->hour > 12 ? $this->hour - 12 : $this->hour;
      $str = str_replace('%g', sprintf('%1d', $h), $str);
    }

    // 24-hour format of an hour without leading zeros
    if (strstr($str, '%G'))
      $str = str_replace('%G', sprintf('%1d', $this->hour), $str);

    // 12-hour format of an hour with leading zeros
    if (strstr($str, '%h')) {
      $h   = $this->hour > 12 ? $this->hour - 12 : $this->hour;
      $str = str_replace('%h', sprintf('%02d', $h), $str);
    }

    // 24-hour format of an hour with leading zeros
    if (strstr($str, '%H'))
      $str = str_replace('%H', sprintf('%02d', $this->hour), $str);

    // Minutes with leading zeros
    if (strstr($str, '%i'))
      $str = str_replace('%i', sprintf('%02d', $this->min), $str);

    // Seconds with leading zeros
    if (strstr($str, '%s'))
      $str = str_replace('%s', sprintf('%02d', $this->sec), $str);

    // Milliseconds
    if (strstr($str, '%u'))
      $str = str_replace('%u', substr($this->micro, 0, 3), $str);

    // Timezone identifier
    if (strstr($str, '%e') || strstr($str, '%T')) {
      $t   = $this->timescale == TimeScale::UTC() ? $this->timezone : $this->timescale;
      $str = str_replace('%e', $t, $str);
      $str = str_replace('%T', $t, $str);
    }

    // Difference to UTC in hours
    if (strstr($str, '%O')) {
      $o   = $this->timezone->offset;
      $os  = $o >= 0 ? '+' : '-';
      $oh  = sprintf('%02d', abs(intval($o)));
      $om  = sprintf('%02d', abs($o - intval($o)) * 60);
      $ofs = "{$os}{$oh}{$om}";
      $str = str_replace('%O', $ofs, $str);
    }

    // Difference to UTC in hours with colon
    if (strstr($str, '%P')) {
      $o   = $this->timezone->offset;
      $os  = $o >= 0 ? '+' : '-';
      $oh  = sprintf('%02d', abs(intval($o)));
      $om  = sprintf('%02d', abs($o - intval($o)) * 60);
      $ofs = "{$os}{$oh}:{$om}";
      $str = str_replace('%P', $ofs, $str);
    }

    // Timezone offset in seconds
    if (strstr($str, '%Z'))
      $str = str_replace('%Z', $this->timezone->offset * 3600, $str);

    return $str;
  }

}
