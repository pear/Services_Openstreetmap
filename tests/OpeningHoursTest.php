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
    public function test()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('24/7');
        $this->assertTrue($oh->isOpen(time()));

        $oh = new Services_OpenStreetMap_OpeningHours(null);
        $this->assertNull($oh->isOpen(time()));

        $oh = new Services_OpenStreetMap_OpeningHours('mo-su: sunrise-sunset');
        $this->assertFalse($oh->isOpen(strtotime('October 24 2012 23:00')));

        $oh->setValue("Mo 08:00-24:00; Tu-Fr 00:00-24:00;Sa 00:00-22:00; Su 10:00-20:00");
        $this->assertFalse($oh->isOpen(strtotime('October 28 2012 21:00')));
        $this->assertTrue($oh->isOpen(strtotime('October 28 2012 19:55')));
        $this->assertFalse($oh->isOpen(strtotime('October 27 2012 23:00')));

    }
}

?>
