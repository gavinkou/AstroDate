<?php

namespace Marando\AstroDate;

use \Marando\Units\Time;
use \Marando\Units\Angle;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-21 at 03:18:18.
 */
class AstroDateTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers Marando\AstroDate\AstroDate::jd
   */
  public function testJD() {
    $dates = [
        // Date, JD
        ['2000-Jan-01 00:00:00', 2451544.500000],
        ['2100-Oct-12 18:20:04', 2488354.263935],
        ['2089-Nov-08 21:14:18', 2484364.3849301],
    ];

    foreach ($dates as $d) {
      $fromJD  = AstroDate::jd($d[1]);
      $fromStr = AstroDate::parse($d[0]);

      $this->assertEquals($fromStr, $fromJD, "to JD $fromJD", 1e-1);
    }

    foreach ($dates as $d) {
      $date = AstroDate::parse($d[0]);
      $this->assertEquals($d[1], $date->jd, "from JD $fromJD", 1e-1);
    }
  }

  /**
   * @covers Marando\AstroDate\AstroDate::parse
   * @todo   Implement testParse().
   */
  public function testParse() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Marando\AstroDate\AstroDate::now
   * @todo   Implement testNow().
   */
  public function testNow() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Marando\AstroDate\AstroDate::toUTC
   */
  public function testToUTC() {
    $d = AstroDate::jd(2456658.5004050927, TimeStandard::TAI());
    $this->assertEquals(2456658.5, $d->toUTC()->jd);
  }

  /**
   * @covers Marando\AstroDate\AstroDate::toTAI
   */
  public function testToTAI() {
    $d = AstroDate::jd(2456658.5, TimeStandard::UTC());
    $this->assertEquals(2456658.5004050927, $d->toTAI()->jd);
  }

  /**
   * @covers Marando\AstroDate\AstroDate::toTT
   */
  public function testToTT() {
    $d = AstroDate::jd(2456658.5, TimeStandard::UTC());
    $this->assertEquals(2456658.5007775929, $d->toTT()->jd, null, 1e-9);
  }

  /**
   * @covers Marando\AstroDate\AstroDate::toTDB
   */
  public function testToTDB() {
    $d = AstroDate::jd(2456658.5, TimeStandard::UTC());
    $this->assertEquals(2456658.500777592, $d->toTT()->jd, null, 1e-9);
  }

  /**
   * @covers Marando\AstroDate\AstroDate::diff
   */
  public function testDiff() {
    $a = AstroDate::parse('2015-Jan-01');
    $b = AstroDate::parse('2017-Feb-08');

    $this->assertEquals(-769, $a->diff($b)->days);
  }

  /**
   * @covers Marando\AstroDate\AstroDate::add
   */
  public function testAdd() {
    $d = AstroDate::jd(2457792.500000)->add(Time::days(15));
    $this->assertEquals(23, $d->day);
  }

  /**
   * @covers Marando\AstroDate\AstroDate::subtract
   */
  public function testSubtract() {
    $d = AstroDate::jd(2457792.500000)->subtract(Time::sec(83829));
    $this->assertEquals(51, round($d->sec, 0));
  }

  /**
   * @covers Marando\AstroDate\AstroDate::sinceMidnight
   * @todo   Implement testSinceMidnight().
   */
  public function testSinceMidnight() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Marando\AstroDate\AstroDate::untilMidnight
   * @todo   Implement testUntilMidnight().
   */
  public function testUntilMidnight() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Marando\AstroDate\AstroDate::formatDefault
   * @todo   Implement testFormatDefault().
   */
  public function testFormatDefault() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Marando\AstroDate\AstroDate::formatJPL
   * @todo   Implement testFormatJPL().
   */
  public function testFormatJPL() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Marando\AstroDate\AstroDate::__toString
   * @todo   Implement test__toString().
   */
  public function test__toString() {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
            'This test has not been implemented yet.'
    );
  }

  public function testBug() {
    // this returns feb-8
    echo "\n" . $b = AstroDate::parse('2017-Feb-08');

    // no leading 0 causes wrong parse
    // this returns feb-1
    echo "\n" . $b = AstroDate::parse('2017-Feb-8');

    // correct JD from USNO
    $this->assertEquals(2457792.5, $b->jd);
  }

  public function testSidereal() {

  }

}
