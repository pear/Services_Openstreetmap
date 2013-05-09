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
class WayTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test retrieving a way and some tags and attributes of it too.
     *
     * @return void
     */
    public function testGetWay()
    {
        $id = 25978036;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertEquals($getTags['highway'], 'service');
        $this->assertEquals($way->getUid(), 1379);
        $this->assertEquals($way->getVersion(), 1);
        $this->assertEquals($way->getUser(), 'AndrewMcCarthy');
        $this->assertEquals($way->getNodes(), array('283393706','283393707'));
    }

    /**
     * Test setting multiple tags to a way (or any other object)
     *
     * @return void
     */
    public function testGetAddMultipleTagsToWay()
    {
        $id = 25978036;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);

        $getTags = $way->getTags();
        $this->assertEquals($getTags['highway'], 'service');
        $this->assertEquals($getTags, array ('highway' => 'service'));

        $way->setTags(array('service' => 'driveway' , 'surface' => 'gravel'));
        $this->assertEquals(
            $way->getTags(),
            array (
            'highway' => 'service',
            'service' => 'driveway',
            'surface' => 'gravel',
            )
        );

    }

    /**
     * Test the isClosed method against a closed way.
     *
     * Check the 'building' tag, and id attribute too.
     *
     * @return void
     */
    public function testGetClosedWay()
    {
        $id = 18197393;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_closed.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $tags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertEquals($tags['building'], 'yes');
        $this->assertTrue($way->isClosed());
    }

    /**
     * Test the isClosed method against an open way.
     *
     * @return void
     */
    public function testOpenWay()
    {
        $id = 23010474;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_open.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertFalse($way->isClosed());
    }

    /**
     * A way with just one node can't be deemed closed.
     *
     * @return void
     */
    public function testWayWithOneNode()
    {
        $id = 23010475;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_one_node.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertFalse($way->isClosed());
    }

    /**
     * Test adding nodes to a way.
     *
     * @return void
     */
    public function testAddNodeToWay()
    {
        $id = 23010474;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_open.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);

        $lat = 52.8638729;
        $lon = -8.1983611;
        $nodes = $way->getNodes();
        $node = $osm->createNode($lat, $lon);
        $way->addNode($node);
        $lat = $lat + 0.00002;
        $node = $osm->createNode($lat, $lon);
        $way->addNode($node);
        $this->assertEquals(sizeof($nodes) + 2, sizeof($way->getNodes()));
    }

    /**
     * Check that an exception is thrown when an incorrect identifier is used
     * to specify a node to remove from a way.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage $node must be either an instance of
     *                           Services_OpenStreetMap_Node or a numeric id
     *
     * @return void
     */
    public function testIncorrectTypeToRemoveNode()
    {
        $id = 23010474;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_open.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $way->removeNode('way5432456');
    }

    /**
     * Remove a node from a way.
     *
     * @return void
     */
    public function testRemoveNode()
    {
        $id = 23010474;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_open.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $nb = count($way->getNodes());
        $way->removeNode(248081798);
        $way->setTag('note', 'testing...');
        $na = count($way->getNodes());
        $this->assertEquals($na, $nb - 1);
    }

    /**
     * Retrieve multiple (2) ways.
     *
     * @return void
     */
    public function testGetWays()
    {
        $wayId = 30357328;
        $way2Id = 30357329;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/way_30357328_30357329.xml', 'rb')
        );
        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $ways = $osm->getWays($wayId, $way2Id);
        foreach ($ways as $key=>$way) {
            $this->assertEquals($way, $ways[$key]);
        }
    }

    /**
     * Test retrieving address tags from a way.
     *
     * @return void
     */
    public function testWayWithAddressSet()
    {
        $id = 75490756;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_75490756.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );
        $osm = new Services_OpenStreetMap($config);
        $way = $osm->getWay($id);
        $address = array(
            'addr_housename' => null,
            'addr_housenumber' => '20-21',
            'addr_street' => 'Pearse Street',
            'addr_city' => 'Nenagh',
            'addr_country' => 'IE',
        );
        $this->assertEquals($address, $way->getAddress());
    }

    /**
     * Test retrieving relations which refer to a specific way.
     *
     * @return void
     */
    public function testWayBackRelations()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_5850969.xml', 'rb'));
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/way_5850969_relations.xml',
                'rb'
            )
        );

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        );

        $osm = new Services_OpenStreetMap($config);

        $relations = $osm->getWay(5850969)->getRelations();
        $this->assertInstanceOf('Services_OpenStreetMap_Relations', $relations);
        $this->assertEquals(2, sizeof($relations));
        $this->assertEquals($relations[0]->getTag('name'), 'Dublin Bus route 14');
        $this->assertEquals($relations[1]->getTag('name'), 'Dublin Bus route 75');
    }
}

// vim:set et ts=4 sw=4:
?>
