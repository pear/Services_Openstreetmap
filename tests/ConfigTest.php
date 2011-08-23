<?php
/**
 * OSMTest.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     ConfigTest.php
 * @todo
 */

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';


class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $osm = new Services_Openstreetmap();
        $this->assertEquals(
            $osm->getConfig(),
            array (
                'server' => 'http://www.openstreetmap.org/',
                'api_version' => '0.6',
                'User-Agent' => 'Services_Openstreetmap',
                'adapter' => 'HTTP_Request2_Adapter_Socket',
            )
        );
        $this->assertEquals('0.6', $osm->getConfig('api_version'));
        $osm->setConfig('User-Agent', 'Acme 1.2');
        $this->assertEquals($osm->getConfig('User-Agent'), 'Acme 1.2');
        $osm->setConfig('api_version', '0.5');
        $this->assertEquals($osm->getConfig('api_version'), '0.5');
    }

    /**
     * Test unknown config detection
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testConfig2()
    {
        $osm = new Services_Openstreetmap();
        $osm->setConfig('api', '0.5');
    }

    /**
     * Test unknown config detection
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testConfig3()
    {
        $osm = new Services_Openstreetmap();
        $osm->getConfig('api');
    }

}
// vim:set et ts=4 sw=4:
?>
