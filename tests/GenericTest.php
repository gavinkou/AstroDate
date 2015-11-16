<?php

use \Marando\AstroDate\AstroDate;
use \Marando\AstroDate\TimeScale;
use \Marando\AstroDate\Timezone;
use \Marando\Units\Time;

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

class GenericTest extends PHPUnit_Framework_TestCase {

  public function test() {

    $d = new AstroDate(2015, 11, 15, 20, 23, 18, Timezone::EST(),
            TimeScale::TT());
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::UTC());
    echo "\n" . $d->toUT1();
    echo "\n" . $d->setTimezone(Timezone::EST());


    return;
    $d = new AstroDate(2015, 11, 15, 20, 23, 18.454334);
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::EST());
    echo "\n" . $d->toUTC();
    echo "\n" . $d->toTAI();
    echo "\n" . $d->setTimezone(Timezone::EST());
    echo "\n" . $d->toTT();




    return;
    $d = new AstroDate(2015, 11, 15, 20, 23, 18.454334);
    echo "\n" . $d;

    $d = new AstroDate(2015, 11, 15, 20, 23, 18.454334, Timezone::EST());
    echo "\n" . $d;
    echo "\n" . $d->setDate(2020, 10, 2);
    echo "\n" . $d->setTime(23, 59, 1);
    echo "\n" . $d->setTimezone(Timezone::UTC());
    echo "\n" . $d->setTimezone(Timezone::EST());
  }

}
