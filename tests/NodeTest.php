<?php
/**
 * NodeTest.php
 * 29-Sep-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       NodeTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
// don't pull in file if using phpunit installed as a PHAR
if (stream_resolve_include_path('PHPUnit/Framework/TestCase.php')) {
    include_once 'PHPUnit/Framework/TestCase.php';
}

/**
 * Unit tests for retrieving and manipulating nodes.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       NodeTest.php
 */
class NodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the getNode method.
     *
     * @return void
     */
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
        $osm = new Services_OpenStreetMap($config);
        $node = $osm->getNode($id);
        $getTags = $node->getTags();

        $this->assertEquals($id, $node->getId());
        $this->assertEquals($getTags['name'], 'Nenagh Bridge');
        $this->assertEquals("52.881667", $node->getLat());
        $this->assertEquals("-8.195833", $node->getLon());
    }

    /**
     * Test retrieving a specific version of an identified node.
     *
     * @return void
     */
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
        $osm = new Services_OpenStreetMap($config);
        $node = $osm->getNode($id, 2);
        $getTags = $node->getTags();

        $this->assertEquals($id, $node->getId());
        $this->assertEquals($getTags['name'], 'Nenagh Bridge');
        $this->assertEquals("52.881667", $node->getLat());
        $this->assertEquals("-8.195833", $node->getLon());
    }

    /**
     * When a 404 response is issued by the server, the getNode method
     * should return the boolean false value.
     *
     * @return void
     */
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
        $osm = new Services_OpenStreetMap($config);
        $node = $osm->getNode($id);
        $this->assertFalse($node);
    }

    /**
     * When a 'GONE' response is issued by the server, the getNode method
     * should return the boolean false value.
     *
     * @return void
     */
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
        $osm = new Services_OpenStreetMap($config);
        $node = $osm->getNode($id);
        $this->assertFalse($node);
    }

    /**
     * Test how a 500 status code is handled.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Unexpected HTTP status: 500 Internal Server Error
     *
     * @return void
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
        $osm = new Services_OpenStreetMap($config);
        $node = $osm->getNode($id);
    }

    /**
     * Test creating a node with the createNode method, including default and
     * explicitly set values.
     *
     * @return void
     */
    public function testCreateNode()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = -8.1983611;
        $node = $osm->createNode(
            $lat,
            $lon,
            array(
                'building' => 'yes',
                'amenity' => 'vet'
            )
        );
        $this->assertEquals('', ($node->getUser()));
        $this->assertEquals(1, $node->getVersion());
        $this->assertEquals(-1, $node->getId());
        $this->assertEquals(
            $node->getTags(),
            array(
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
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Latitude can't be greater than 90
     *
     * @return void
     */
    public function testCreateNodeInvalidLatitude()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = 252.8638729;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test invalid latitude value in constructor and that value can't be
     * less than -90 degrees.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Latitude can't be less than -90
     *
     * @return void
     */
    public function testCreateNodeInvalidLessThanMinus90()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = -90.000010123;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Check that -90 degrees is accepted as a latitude value
     *
     * @return void
     */
    public function testCreateNodeLatMinus90()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = -90.0000;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
        $this->assertEquals($node->getLat(), -90);
    }

    /**
     * Check that 90 degrees is accepted as a latitude value.
     *
     * @return void
     */
    public function testCreateNodeLat90()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = 90;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
        $this->assertEquals($node->getLat(), 90);
    }

    /**
     * Test invalid latitude value in constructor
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Latitude must be numeric
     *
     * @return void
     */
    public function testCreateNodeNonnumericLatInConstructor()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = 'ArticCircle';
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon);
    }


    /**
     * Test invalid longitude value in constructor and that value greater than
     * 180 degrees causes an exception.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Longitude can't be greater than 180
     *
     * @return void
     */
    public function testCreateNodeInvalidLongitude()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = 180.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test invalid longitude value in constructor
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Longitude can't be less than -180
     *
     * @return void
     */
    public function testCreateNodeInvalidLongitudeLessThanMinus180()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = -180.1983611;
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Check 180 is accepted as a longitude value.
     *
     * @return void
     */
    public function testCreateNodeLon180()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = 180;
        $node = $osm->createNode($lat, $lon);
        $this->assertEquals($lon, $node->getLon());
    }

    /**
     * Check -180 is accepted as a longitude value.
     *
     * @return void
     */
    public function testCreateNodeLonMinus180()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = -180;
        $node = $osm->createNode($lat, $lon);
        $this->assertEquals($lon, $node->getLon());
    }

    /**
     * Test invalid longitude value in constructor
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Longitude must be numeric
     *
     * @return void
     */
    public function testCreateNodeNonnumericLonInConstructor()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
                'adapter' => $mock,
                'server'  => 'http://api06.dev.openstreetmap.org/',
                );
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = 'TheBlessing';
        $node = $osm->createNode($lat, $lon);
    }

    /**
     * Test retrieving a number of nodes simultaneously with the getNodes
     * method.
     *
     * @return void
     */
    public function testGetNodes()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/nodes_621953926_621953928_621953939.xml',
                'rb'
            )
        );

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $nodes = $osm->getNodes(array(621953926, 621953928, 621953939));
        $this->assertEquals(3, sizeof($nodes));

        $nodesInfo = array(
            array('id' => 621953926, 'source' => 'survey', 'address' => null),
            array('id' => 621953928, 'source' => 'survey', 'address' => null),
            array(
                'id' => 621953939,
                'source' => 'survey',
                'address' => array (
                    'addr_housename' => null,
                    'addr_housenumber' => '5',
                    'addr_street' => 'Castle Street',
                    'addr_city' => 'Cahir',
                    'addr_country' => 'IE'
                )
            )
        );
        foreach ($nodes as $key => $node) {
            $tags = $node->getTags();
            $this->assertEquals($node->getId(), $nodesInfo[$key]['id']);
            $this->assertEquals($tags['source'], $nodesInfo[$key]['source']);
            $this->assertEquals($node->getAddress(), $nodesInfo[$key]['address']);
        }
    }

    /**
     * When an 'UNAUTHORISED' response is issued by the server, the getNodes method
     * should return the boolean false value.
     *
     * @return void
     */
    public function testGetNodes401()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/401', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $nodes = $osm->getNodes(array(621953926, 621953928, 621953939));
        $this->assertFalse($nodes);
    }

    /**
     * When a 404 response is issued by the server, the getNodes method
     * should return the boolean false value.
     *
     * @return void
     */
    public function testGetNodes404()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $nodes = $osm->getNodes(array(621953926, 621953928, 621953939));
        $this->assertFalse($nodes);
    }

    /**
     * When a 'GONE' response is issued by the server, the getNodes method
     * should return the boolean false value.
     *
     * @return void
     */
    public function testGetNodes410()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/410', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $nodes = $osm->getNodes(array(621953926, 621953928, 621953939));
        $this->assertFalse($nodes);
    }

    /**
     * Test how a 500 status code is handled.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Unexpected HTTP status: 500 Internal Server Error
     *
     * @return void
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
        $osm = new Services_OpenStreetMap($config);
        $nodes = $osm->getNodes(array(621953926, 621953928, 621953939));
    }

    /**
     * Test retrieving all versions, current and past, of a specified node.
     *
     * @return void
     */
    public function testGetNodesHistory()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/nodes_621953926_621953928_621953939.xml',
                'rb'
            )
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/node_621953926.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_621953928.xml', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/node_621953939_history.xml', 'rb')
        );

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $nodes = $osm->getNodes(array(621953926, 621953928, 621953939));
        $versions = array(
            621953926 => array(1),
            621953928 => array(1),
            621953939 => array(1, 2)
        );
        foreach ($nodes as $node) {
            $history = $node->history();
            $id = $node->getId();
            foreach ($history as $item) {
                $version = $item->getVersion();
                $this->assertEquals(true, in_array($version, $versions[$id]));
            }
        }
    }

    /**
     * Test retrieving way back references - i.e. retrieving all ways that a
     * specific node is connected to.
     *
     * @return void
     */
    public function testGetWayBackRef()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_248081837.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_23010474.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
        );

        $osm = new Services_OpenStreetMap($config);

        $ways = $osm->getNode(248081837)->getWays();
        $this->assertInstanceOf('Services_OpenStreetMap_Ways', $ways);
        $this->assertEquals(sizeof($ways), 1);
        $this->assertEquals(
            $ways[0]->getTags(),
            array (
                'highway' => 'residential',
                'maxspeed' => '50',
                'name' => 'Kingston Park',
                'name:en' => 'Kingston Park',
                'name:ga' => 'Páirc Kingston',
            )
        );
    }

    /**
     * Test retrieving relations that refer to a specific node.
     *
     * @return void
     */
    public function testGetRelations()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_597697114.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/relation_405053.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
        );

        $osm = new Services_OpenStreetMap($config);

        $relations = $osm->getNode(597697114)->getRelations();
        $this->assertInstanceOf('Services_OpenStreetMap_Relations', $relations);
        $this->assertEquals(sizeof($relations), 1);
        $this->assertInstanceOf('Services_OpenStreetMap_Relation', $relations[0]);
        $this->assertEquals(
            $relations[0]->getTags(),
            array (
                'complete'=> 'no',
                'name'=> 'Dublin Bus route 75',
                'operator'=> 'Dublin Bus',
                'ref'=> '75',
                'route'=> 'bus',
                'type'=> 'route',
            )
        );
    }
}

?>
