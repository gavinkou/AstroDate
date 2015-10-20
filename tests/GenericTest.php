<?php

use \Marando\AstroDate\AstroDate;

class GenericTest extends \PHPUnit_Framework_TestCase {

  public function test() {



    echo "\n" . $j = new AstroDate(2014, 1, 1, 0, 0, 0);
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
