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

     echo "\n" . $d = AstroDate::now()->setTimezone(Timezone::EST());
     echo "\n" . $d->format('');
     return;


     $str = '2016-Nov-14 17:07:07.120';
    echo "\n" . $d   = AstroDate::parse($str);
    echo "\n" . $d   = AstroDate::parse($str)->setTimezone(Timezone::EST());


    echo "\n" . $d->format('Z T P O e u s i h H G g a A y Y n M m F W z D Y-m-d j');


        $str = '2016-Nov-14 17:07:07.120 TAI';
    echo "\n" . $d   = AstroDate::parse($str);
    echo "\n" . $d->format('O e u s i h H G g a A y Y n M m F W z D Y-m-d j');



    $str = '2015-Nov-1 17:07:07.120 UTC';
    $d   = AstroDate::parse($str);
    echo "\n" . $d->format('z w S N l L D Y-m-d j');

    return;
    echo "\n\n" . $str = '2015-Nov-16 17:07:07.120 TT';
    echo "\n" . AstroDate::parse($str);


    echo "\n\n" . $str = '-1950-1-16 17:07:07 UTC';
    echo "\n" . $d   = AstroDate::parse($str);
    echo "\n" . $d->format('Y-m-d');






    return;
    echo "\n" . $d = AstroDate::now();
    echo "\n" . $d->toUT1();
    echo "\n" . $d->toTAI();

    echo "\n\n" . $d = AstroDate::now(Timezone::EST());
    echo "\n" . $d->toUT1();
    echo "\n" . $d->toTAI();




    return;
    $d = new AstroDate(2017, 11, 15, 7, 0, 0);
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::EST());
    //var_dump($d);

    $d = new AstroDate(2017, 6, 15, 7, 0, 0);
    echo "\n\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::EST());
    //var_dump($d);

    $d = new AstroDate(2017, 11, 15, 7, 0, 0);
    echo "\n\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::EST());
    //var_dump($d);

    $d = new AstroDate(2017, 6, 15, 6, 0, 0);
    echo "\n\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::EST());
    //var_dump($d);


    return;
    $d = new AstroDate(2017, 11, 15, 0, 0, 0);
    echo "\n" . $d;
    echo "\n" . $d->jd();
    echo "\n" . $d->jd(12);

    echo "\n" . $d->toTT();
    echo "\n" . $d->toJD();
    echo "\n" . $d->jd(12);




    echo "\n";


    $d = new AstroDate(2015, 11, 15, 20, 23, 18, Timezone::EST(),
            TimeScale::TT());
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(Timezone::UTC());
    echo "\n" . $d->toUT1();
    echo "\n" . $d->setTimezone(Timezone::EST());
    echo "\n" . $d->jd();
    echo "\n" . $d->jd(12);
    echo "\n" . $d->mjd();
    echo "\n" . $d->mjd(12);
    echo "\n" . $d->monthName();

    echo "\n" . $d->mjd();

    echo "\n\n" . $d->toTAI();
    echo "\n" . $d->year;
    echo "\n" . $d->month;
    echo "\n" . $d->day;
    echo "\n" . $d->hour;
    echo "\n" . $d->min;
    echo "\n" . $d->sec;
    echo "\n" . $d->micro;
    echo "\n" . $d->timezone;
    echo "\n" . $d->timescale;

    $d->year  = 2020;
    echo "\n" . $d;
    $d->month = 12.13432;
    echo "\n" . $d;

    $d->sec = 12;
    echo "\n" . $d;

    echo "\n" . $d->add(Time::sec(0.5));


    echo "\n" . $d->setDateTime(2017, 9, 4, 18, 34, 34.234);
    var_dump($d->isLeapYear());

    echo "\n" . $d->setDateTime(2016, 9, 4, 18, 34, 34.234);
    var_dump($d->isLeapYear());

    echo "\n" . $d->dayName();



    $a = new AstroDate(2016, 12, 25, 19, 10, 2);
    $b = new AstroDate(2015, 12, 25, 19, 10, 2);
    echo "\n" . $a->diff($b);


    echo "\n" . $a = new AstroDate(2016, 12, 31, 19, 10, 2);
    echo "\n" . $b = new AstroDate(2015, 12, 31, 19, 10, 2);
    echo "\n" . $a->dayOfYear();
    echo "\n" . $b->dayOfYear();



    echo "\n\n" . $b;
    echo "\n" . $b->toTDB();
    echo "\n" . $b->setTimezone(Timezone::UTC());
    echo "\n" . $b->toTDB();
    echo "\n" . $b->setTimezone(Timezone::EST());
    echo "\n" . $b->toTDB();

    echo "\n\n\n";

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
