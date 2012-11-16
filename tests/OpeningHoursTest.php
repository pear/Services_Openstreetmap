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

class OpeningHoursTest extends PHPUnit_Framework_TestCase
{
    public function test24_7()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('24/7');
        $this->assertTrue($oh->isOpen(time()));

    }

	public function testNull() {

        $oh = new Services_OpenStreetMap_OpeningHours(null);
        $this->assertNull($oh->isOpen(time()));
	}

	public function testSunriseSunset() {
        $oh = new Services_OpenStreetMap_OpeningHours('mo-su: sunrise-sunset');
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 23:00')));
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 03:00')));
	}

	public function test() {

        $oh = new Services_OpenStreetMap_OpeningHours(null);
        $this->assertNull($oh->isOpen(time()));

        $oh->setValue("Mo 08:00-24:00; Tu-Fr 00:00-24:00;Sa 00:00-22:00; Su 10:00-20:00");
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

	public function testOff() {

        // Check precedence/priority
        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("Tu off; Mo-Sa 10:00-20:00");
        $this->assertFalse($oh->isOpen(strtotime('last tuesday 12:00')));

        $oh->setValue("Mo-Sa 10:00-20:00; Tu off");
        $this->assertFalse($oh->isOpen(strtotime('last tuesday 12:00')));
	}

	public function testMonthOff() {
        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("24/7; Aug off");
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 07:00')));
        $this->assertFalse($oh->isOpen(strtotime('August 22 2012 07:00')));
        $oh->setValue("24/7; Aug 10:00-14:00");
#        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 07:00')));
 #       $this->assertTrue($oh->isOpen(strtotime('August 22 2012 13:00')));

		/*
		   $oh->setValue("mo-fr 9:00-13:00, 14:00-17:30; sa 9:00-13:00");
		   $oh->setValue("Mo-Su 08:00-18:00; Apr 10-15 off; Jun 08:00-14:00; Aug off; Dec 25 off");
		   $oh->setValue("Mo-Sa 10:00-20:00; Tu 10:00-14:00");
		   $oh->setValue("");
		 */
	}
    public function testMultipleTimesSpecifiedForDays() {

        $oh = new Services_OpenStreetMap_OpeningHours();
        $oh->setValue("mo-fr 9:00-13:00, 14:00-17:30; sa 9:00-13:00");
        // Monday...
        $this->assertFalse($oh->isOpen(strtotime('October 22 2012 07:00')));
        $this->assertFalse($oh->isOpen(strtotime('October 22 2012 13:30')));
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 10:30')));
        $this->assertTrue($oh->isOpen(strtotime('October 22 2012 14:30')));
        // Saturday...
        $this->assertFalse($oh->isOpen(strtotime('October 27 2012 14:30')));
        $this->assertTrue($oh->isOpen(strtotime('October 27 2012 11:30')));
    }
}

?>
