<?php

use \Marando\AstroDate\AstroDate;
use \Marando\AstroDate\Epoch;
use \Marando\AstroDate\TimeScale;
use \Marando\AstroDate\TimeZone;
use \Marando\Units\Angle;
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

    echo "\n" . AstroDate::parse('2015-Dec-11 04:33:14 EST');
    echo "\n" . AstroDate::parse('2015-dec-11 04:33:14 TT');

    return;
    echo "\n" . $d = AstroDate::now()->setTimezone(TimeZone::UT(-7));

    echo "\n" .TimeScale::TT()->abr;
    echo "\n" .TimeScale::TT()->name;


    echo "\n" . Epoch::J2000();
    echo "\n" . Epoch::J1900();
    echo "\n" . Epoch::B1950();
    echo "\n" . Epoch::B1900();
    echo "\n" . Epoch::B(1954.4423);
    echo "\n" . Epoch::jd(2455200.5);

    echo "\n\n" . Epoch::J2000()->toDate();
    echo "\n" . Epoch::J1900()->toDate();
    echo "\n" . Epoch::B1950()->toDate();
    echo "\n" . Epoch::B1900()->toDate();
    echo "\n" . Epoch::B(1954.4423)->toDate();


    echo "\n\n" . AstroDate::now();
    echo "\n" . $d = AstroDate::now()->setTimezone(TimeZone::name('EST'));
    echo "\n" . $d->toTDB();
    echo "\n" . $d->toUTC();
    echo "\n" . $d = AstroDate::now()->setTimezone('MST');
    echo "\n" . $d = AstroDate::now()->setTimezone('PST');

    return;
    echo "\n" . AstroDate::parse('2015-Dec-10 6:00');
    echo "\n" . AstroDate::parse('2015-Dec-10 6:00')->setTimezone(TimeZone::name('EST'));


    echo "\n" . AstroDate::now();
    echo "\n" . AstroDate::now()->setTimezone(TimeZone::name('EST'));
    echo "\n" . AstroDate::now()->setTimezone(TimeZone::name('PST'));
    echo "\n" . AstroDate::now()->setTimezone(TimeZone::UT(6));

    echo "\n" . TimeZone::name('est')->offset;
    echo "\n" . TimeZone::name('est')->offset(2451545.5);
    echo "\n" . TimeZone::name('est')->offset(2451589.5);

    return;
    echo "\n" . TimeZone::UT(-2);
    echo "\n" . TimeZone::name('EST');

    echo "\n" . AstroDate::now()
            ->setTimezone(TimeZone::EST())
            ->format(AstroDate::FORMAT_GOOGLE);

    echo "\n" . AstroDate::now()
            ->setTimezone(TimeZone::UT(-10.5))
            ->format(AstroDate::FORMAT_GOOGLE);


    echo "\n" . AstroDate::now()
            ->setTimezone(TimeZone::UT(-10))
            ->format(AstroDate::FORMAT_GOOGLE);

    return;

    $t = timezone_abbreviations_list();
    $s = timezone_identifiers_list();
    $e = timezone_name_from_abbr('est');

    var_dump($t);
    return;


    $d = AstroDate::now()->setTimezone(TimeZone::EST());
    echo "\n" . $d->format('r Y-M-c h:i:s.u A T');
    echo "\n" . $d->sub(Time::days(15))->format(AstroDate::FORMAT_GENERIC);
    echo "\n" . $d->sidereal('a');
    echo "\n" . $d->sidereal('m');
    echo "\n" . $d->format(AstroDate::FORMAT_JPL_FRAC);

    echo "\n" . $d->sidereal('a', Angle::deg(-82.47));
    echo "\n" . $d->sidereal('m', Angle::deg(-82.47));

    echo "\n" . $d->sinceMidnight();
    echo "\n" . $d->untilMidnight();

    /**
     * TODO:
     *
     *    - Figure out formula for DST start and end
     *    - Figure out formula for Week # of year
     *
     *
     */
    return;
    echo "\n" . $d->format(DateTime::RSS);
    echo "\n" . $d;
    echo "\n" . $d->format(AstroDate::FORMAT_GENERIC);
    echo "\n" . $d->format(DateTime::ISO8601);

    return;


    $str = '2016-Nov-14 17:07:07.120';
    echo "\n" . $d   = AstroDate::parse($str);
    echo "\n" . $d   = AstroDate::parse($str)->setTimezone(TimeZone::EST());


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

    echo "\n\n" . $d = AstroDate::now(TimeZone::EST());
    echo "\n" . $d->toUT1();
    echo "\n" . $d->toTAI();




    return;
    $d = new AstroDate(2017, 11, 15, 7, 0, 0);
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(TimeZone::EST());
    //var_dump($d);

    $d = new AstroDate(2017, 6, 15, 7, 0, 0);
    echo "\n\n" . $d;
    echo "\n" . $d->setTimezone(TimeZone::EST());
    //var_dump($d);

    $d = new AstroDate(2017, 11, 15, 7, 0, 0);
    echo "\n\n" . $d;
    echo "\n" . $d->setTimezone(TimeZone::EST());
    //var_dump($d);

    $d = new AstroDate(2017, 6, 15, 6, 0, 0);
    echo "\n\n" . $d;
    echo "\n" . $d->setTimezone(TimeZone::EST());
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


    $d = new AstroDate(2015, 11, 15, 20, 23, 18, TimeZone::EST(),
            TimeScale::TT());
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(TimeZone::UTC());
    echo "\n" . $d->toUT1();
    echo "\n" . $d->setTimezone(TimeZone::EST());
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
    echo "\n" . $b->setTimezone(TimeZone::UTC());
    echo "\n" . $b->toTDB();
    echo "\n" . $b->setTimezone(TimeZone::EST());
    echo "\n" . $b->toTDB();

    echo "\n\n\n";

    $d = new AstroDate(2015, 11, 15, 20, 23, 18.454334);
    echo "\n" . $d;
    echo "\n" . $d->setTimezone(TimeZone::EST());
    echo "\n" . $d->toUTC();
    echo "\n" . $d->toTAI();
    echo "\n" . $d->setTimezone(TimeZone::EST());
    echo "\n" . $d->toTT();




    return;
    $d = new AstroDate(2015, 11, 15, 20, 23, 18.454334);
    echo "\n" . $d;

    $d = new AstroDate(2015, 11, 15, 20, 23, 18.454334, TimeZone::EST());
    echo "\n" . $d;
    echo "\n" . $d->setDate(2020, 10, 2);
    echo "\n" . $d->setTime(23, 59, 1);
    echo "\n" . $d->setTimezone(TimeZone::UTC());
    echo "\n" . $d->setTimezone(TimeZone::EST());
  }

}
