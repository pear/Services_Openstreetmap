<?php
/**
 * OpeningHoursWithComment.php
 * 25-Mar-2018
 *
 * PHP Version 5
 *
 * @category OpeningHoursWithComment
 * @package  OpeningHoursWithComment
 * @author   Ken Guest <ken@linux.ie>
 * @license  GPL (see http://www.gnu.org/licenses/gpl.txt)
 * @version  CVS: <cvs_id>
 * @link     OpeningHoursWithComment.php
 * @todo
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

class OpeningHoursWithCommentTest extends PHPUnit_Framework_TestCase
{
    public function testCommentIncluded()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('24/7 "All the time"');
        $this->assertTrue($oh->isOpen(time()));
    }

    public function testAnother()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('Mo 13:00-23:30 "ook!"');
        $this->assertTrue($oh->isOpen(strtotime("2018-08-27 21:58")));
    }


    public function testAnotherButFalse()
    {
        $oh = new Services_OpenStreetMap_OpeningHours('Mo 13:00-23:30 "ook!"');
        $this->assertFalse($oh->isOpen(strtotime("2018-08-27 23:58")));
    }

}
