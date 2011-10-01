<?php
/**
 * NodeTest.php
 * 29-Sep-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     NodeTest.php
 * @todo
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';


class NodeTest extends PHPUnit_Framework_TestCase
{
    public function testGetNode()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node.xml', 'rb'));

        $config = array(
            #'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id);
        $getTags = $node->getTags();

        $this->assertEquals($id, $node->getId());
        $this->assertEquals($getTags['name'], 'Nenagh Bridge');
        $this->assertEquals("52.881667", $node->getLat());
        $this->assertEquals("-8.195833", $node->getLon());
    }

    public function testCreateNode()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = 52.8638729;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon, array(
                    'building' => 'yes',
                    'amenity' => 'vet')
                );
        $this->assertEquals(
                $node->getTags(),
                array(
                    'created_by' => 'Services_Openstreetmap',
                    'building' => 'yes',
                    'amenity' => 'vet',
                    )
                );
        $this->assertEquals($lat, $node->getlat());
        $this->assertEquals($lon, $node->getlon());
        $this->assertEquals(-1, $node->getId());
    }

    /**
     * Test invalid latitude value in constructor
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Latitude can't be greater than 90
     */
    public function testCreateNodeInvalidLatitude()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = 152.8638729;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test invalid latitude value in constructor
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Latitude can't be less than -90
     */
    public function testCreateNodeInvalidLessThanMinus90()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = -90.000010123;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test invalid latitude value in constructor
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Latitude must be numeric
     */
    public function testCreateNodeNonnumericLatInConstructor()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = 'ArticCircle';
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
    }


    /**
     * Test invalid longitude value in constructor
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Longitude can't be greater than 90
     */
    public function testCreateNodeInvalidLongitude()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = 52.8638729;
        $lon = 90.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test invalid longitude value in constructor
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Longitude can't be less than -90
     */
    public function testCreateNodeInvalidLongitudeLessThanMinus90()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = 52.8638729;
        $lon = -90.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test invalid longitude value in constructor
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Longitude must be numeric
     */
    public function testCreateNodeNonnumericLonInConstructor()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_Openstreetmap($config);
        $lat = 52.8638729;
        $lon = 'TheBlessing';
        $node = $osm->createNode($lat, $lon);
    }
}

?>
