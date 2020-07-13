<?php
/**
 * Unit test class for parsing of opening_hours data.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       OpeningHoursTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

/**
 * Unit test class for parsing of opening_hours data.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       OpeningHoursTest.php
 */
class OpeningHoursTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test 24/7 syntax, should evaluate to true whenever.
     *
     * @return void
     */
    public function test247()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('24/7');
        $this->assertTrue($oh->isOpen(time()));
    }

    /**
     * Test 24/7 syntax with a null value, should always evaluate to true.
     *
     * @return void
     */
    public function test247againstNullValue()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('24/7');
        $this->assertTrue($oh->isOpen());
    }

    /**
     * Test null value
     *
     * @return void
     */
    public function testNull()
    {

        $oh = new Services_OpenStreetMap_OpeningHours(null);
        $this->assertNull($oh->isOpen(time()));
    }

    /**
     * Test Sunrise-Sunset syntax
     *
     * @return void
     */
    public function testSunriseSunset()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('mo-su: sunrise-sunset');
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 23:00')));
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 03:00')));
    }


    /**
     * Sunrise-Sunset every day
     *
     * @return void
     */
    public function testSunriseSunsetEveryDay()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('sunrise-sunset');
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 23:00')));
    }

    /**
     * Test sunrise-sunset with calculated offsets
     *
     * @return void
     */
    public function testSunriseSunsetWithCalculatedOffsets()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('mo-su: (sunrise + 00:45) - (sunset - 01:30)');
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 23:00')));
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 03:00')));
    }
    public function testSunriseSunsetWithCalculatedOffsetsd()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('mo-su: (sunrise - 00:45) - (sunset + 01:30)');
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 23:00')));
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 03:00')));
    }

    /**
     * Test general opening hours syntax
     *
     * @return void
     */
    public function testGeneralSyntax()
    {

        $oh = new Services_OpenStreetMap_OpeningHours(null);
        $this->assertNull($oh->isOpen(time()));

        $oh->setValue(
            "Mo 08:00-24:00; Tu-Fr 00:00-24:00; Sa 00:00-22:00; Su 10:00-20:00"
        );
        /*
           October 2012
           Su Mo Tu We Th Fr Sa
              1  2  3  4  5  6
           7  8  9  10 11 12 13
           14 15 16 17 18 19 20
           21 22 23 24 25 26 27
           28 29 30 31
        */
        // Sunday...
        $this->assertFalse($oh->isOpen(strtotime('October 28 2012 21:00')));
        $this->assertTrue($oh->isOpen(strtotime('October 28 2012 19:55')));
        // Saturday...
        $this->assertFalse($oh->isOpen(strtotime('October 27 2012 23:00')));
        // Friday (edge case) ...
        $this->assertTrue($oh->isOpen(strtotime('October 26 2012 23:00')));
        // Wednesday...
        $this->assertTrue($oh->isOpen(strtotime('October 24 2012 23:00')));
        // Monday...
        $this->assertFalse($oh->isOpen(strtotime('October 22 2012 07:00')));
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 08:00')));
    }

    /**
     * Test off syntax, including precedence and priority of same.
     *
     * @return void
     */
    public function testOff()
    {

        // Check precedence/priority
        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("Tu off; Mo-Sa 10:00-20:00");
        $this->assertFalse($oh->isOpen(strtotime('last tuesday 12:00')));

        $oh->setValue("Mo-Sa 10:00-20:00; Tu off");
        $this->assertFalse($oh->isOpen(strtotime('last tuesday 12:00')));
    }

    /**
     * Test month off syntax
     *
     * @return void
     */
    public function testMonthOff()
    {
        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("24/7; Aug off");
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 07:00')));
        $this->assertFalse($oh->isOpen(strtotime('August 22 2012 07:00')));
        $oh->setValue("24/7; Aug 10:00-14:00");
        /*
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 07:00')));
        $this->assertTrue($oh->isOpen(strtotime('August 22 2012 13:00')));

           $oh->setValue("mo-fr 9:00-13:00, 14:00-17:30; sa 9:00-13:00");
           $oh->setValue("Mo-Su 08:00-18:00; Apr 10-15 off; "
            . "Jun 08:00-14:00; Aug off; Dec 25 off");
           $oh->setValue("Mo-Sa 10:00-20:00; Tu 10:00-14:00");
           $oh->setValue("");
         */
    }

    /**
     * Test multiple times specified for days
     *
     * @return void
     */
    public function testMultipleTimesSpecifiedForDays()
    {

        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("mo-fr 9:00-13:00, 14:00-17:30; sa 9:00-13:00");
        /*
           October 2012
           Su Mo Tu We Th Fr Sa
              1  2  3  4  5  6
           7  8  9  10 11 12 13
           14 15 16 17 18 19 20
           21 22 23 24 25 26 27
           28 29 30 31
        */
        // Monday...
        $this->assertFalse($oh->isOpen(strtotime('October 22 2012 07:00')));
        $this->assertFalse($oh->isOpen(strtotime('October 22 2012 13:30')));
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 10:30')));
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 14:30')));
        // Saturday...
        $this->assertFalse($oh->isOpen(strtotime('October 27 2012 14:30')));
        $this->assertTrue($oh->isOpen(strtotime('October 27 2012 11:30')));
    }

    /**
     * Test for being open on what's normally a day off
     * E.g. "Mo-Sa 10:00-18:00; Jun 23 11:15-13:30"
     *
     * @return void
     */
    public function testOpeningOnSpecificSunday()
    {
        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("Mo-Sa 10:00-18:00; Jun 23 11:15-13:30");
        $this->assertFalse($oh->isOpen(strtotime('June 23 2013 11:00')));
        $this->assertTrue($oh->isOpen(strtotime('June 23 2013 11:45')));
        $this->assertFalse($oh->isOpen(strtotime('June 23 2013 13:35')));
    }

}
