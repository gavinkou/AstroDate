<?php

use \Marando\AstroDate\AstroDate;
use \Marando\AstroDate\TimeStandard;
use \Marando\Units\Time;

class GenericTest extends \PHPUnit_Framework_TestCase {

  public function test() {

     echo "\n\n" . AstroDate::now()->formatJPL();
     echo "\n\n" . AstroDate::now()->formatJPL(true);


    return;
     echo "\n\n" .  $d;
     echo "\n\n" .  $d->formatJPL(true);
     echo "\n\n" .  $d->formatJPL(false);

    return;
    echo "\n\n" . $d = new AstroDate();
    echo "\n\n" . $d->jd;


    $d->month = 4;
    echo "\n\n" . $d;
    echo "\n\n" . $d->jd;

    echo "\n\n" . $d->toTDB();
    echo "\n\n" . $d->jd;


    //echo "\n\n" . $d;
    //echo "\n\n" . AstroDate::now();

    return;
    echo "\n" . $a = new AstroDate(2015, 10, 1, 11, 54, 1);
    echo "\n" . $b = new AstroDate(2017, 6, 14, 10, 45, 12);

    echo "\n" . $a->diff($b);
    echo "\n" . $a->diff($b)->hours;

    echo "\n" . $b->diff($a);
    echo "\n" . $b->diff($a)->hours;

    //echo "\n" . $d->subtract(10.53);




    echo "\n\n\n";

    return;
    $d     = new AstroDate();
    $d->jd = 19;

    echo "\n" . $d->toTDB()->formatJPL();
    echo "\n" . $d->toTDB()->formatJPL();


    echo "\n\n\n";

    //$d = AstroDate::jd(2451445, TimeStd::TDB());



    return;
    echo "\n";
    echo $d;
    echo "\n";
    echo $d->toUTC();
    echo "\n";


    return;
    echo "\n" . $j = new AstroDate(2015, 1, 1, 0, 0, 0);
    echo "\n" . $j->jd;

    echo "\n" . $j->toTAI();
    echo "\n" . $j->jd;

    echo "\n" . $j->toTT();
    echo "\n" . $j->jd;

    echo "\n" . $j->toTDB();
    echo "\n" . $j->jd;

    echo "\n" . $j->toUTC();
    echo "\n" . $j->jd;


    return;
    echo "\n" . $j = new AstroDate(2015, 10, 20, 22, 0, 10);


    echo "\n" . $j->toTAI();
    echo "\n" . $j->toTT();

    echo "\n" . $j->toUTC();
    echo "\n" . $j->toTT();

    echo "\n" . $j->toTDB();




    echo "\n" . $j->toTAI();
    echo "\n" . $j->toUTC();
    echo "\n" . $j->toTT();

    echo "\n" . $j->jd;








    echo 1;
  }

  public function testLeapSec() {
    return;
    $tests = [
        ['1990-01-23', 25],
        ['1987-05-12', 23],
    ];

    foreach ($tests as $t) {
      $this->assertEquals($t[1], AstroDate::parse($t[0])->leapSec);
    }
  }

}
