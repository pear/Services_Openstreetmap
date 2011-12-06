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
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id);
        $getTags = $node->getTags();

        $this->assertEquals($id, $node->getId());
        $this->assertEquals($getTags['name'], 'Nenagh Bridge');
        $this->assertEquals("52.881667", $node->getLat());
        $this->assertEquals("-8.195833", $node->getLon());
    }

    public function testGetSpecifiedVersionOfNode()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id, 2);
        $getTags = $node->getTags();

        $this->assertEquals($id, $node->getId());
        $this->assertEquals($getTags['name'], 'Nenagh Bridge');
        $this->assertEquals("52.881667", $node->getLat());
        $this->assertEquals("-8.195833", $node->getLon());
    }

    public function testGetNode404()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id);
        $this->assertFalse($node);
    }

    public function testGetNode410()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/410', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id);
        $this->assertFalse($node);
    }

    /**
     * Test how a 500 status code is handled.
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unexpected HTTP status: 500 Internal Server Error
     */
    public function testGetNode500()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/500', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id);
        $this->assertFalse($node);
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
        $this->assertEquals('', ($node->getUser()));
        $this->assertEquals(1, $node->getVersion());
        $this->assertEquals(-1, $node->getId());
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

    public function testGetNodes()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/nodes_621953926_621953928_621953939.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $nodes = $osm->getNodes(array(621953926,621953928,621953939));
        $this->assertEquals(3, sizeof($nodes));

        $nodes_info = array(
            array('id' => 621953926, 'source' => 'survey', 'address' => null),
            array('id' => 621953928, 'source' => 'survey', 'address' => null),
            array(
                'id' => 621953939,
                'source' => 'survey',
                'address' => array (
                    'addr_housename' => NULL,
                    'addr_housenumber' => '5',
                    'addr_street' => 'Castle Street',
                    'addr_city' => 'Cahir',
                    'addr_country' => 'IE'
                )
            )
        );
        foreach($nodes as $key=>$node) {
            $tags = $node->getTags();
            $this->assertEquals($node->getId(), $nodes_info[$key]['id']);
            $this->assertEquals($tags['source'], $nodes_info[$key]['source']);
            $this->assertEquals($node->getAddress(), $nodes_info[$key]['address']);
        }
    }

    public function testGetNodes401()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/401', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $nodes = $osm->getNodes(array(621953926,621953928,621953939));
        $this->assertFalse($nodes);
    }

    public function testGetNodes404()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $nodes = $osm->getNodes(array(621953926,621953928,621953939));
        $this->assertFalse($nodes);
    }

    public function testGetNodes410()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/410', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $nodes = $osm->getNodes(array(621953926,621953928,621953939));
        $this->assertFalse($nodes);
    }

    /**
     * Test how a 500 status code is handled.
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unexpected HTTP status: 500 Internal Server Error
     */
    public function testGetNodes500()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/500', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $nodes = $osm->getNodes(array(621953926,621953928,621953939));
        $this->assertFalse($nodes);
    }

    public function testGetWayBackRef()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_248081837.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_23010474.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );

        $osm = new Services_Openstreetmap($config);

        $ways = $osm->getNode(248081837)->getWays();
        $this->assertInstanceOf('Services_Openstreetmap_Ways', $ways);
        $this->assertEquals(sizeof($ways), 1);
        $this->assertEquals($ways[0]->getTags(), array (
            'highway' => 'residential',
            'maxspeed' => '50',
            'name' => 'Kingston Park',
            'name:en' => 'Kingston Park',
            'name:ga' => 'Páirc Kingston',
        ));
    }
}

?>
