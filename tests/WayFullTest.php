<?php
/**
 * Unit test class for Way related functionality.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       WayTest.php
 * @todo
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';

/**
 * Unit test class for manipulation of Services_OpenStreetMap_Way.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       WayTest.php
 */
class WayFullTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test retrieving a way and some tags and attributes of it too.
     *
     * @return void
     */
    public function testGetWayFull()
    {
        $id = 25978036;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        ];
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWayFull($id, 1);
        //var_dump($way);
        $way = $osm->getWay($id);
        //var_dump($way);
    }
}
