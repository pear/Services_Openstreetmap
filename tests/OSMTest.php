<?php
/**
 * OSMTest.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     OSMTest.php
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


class OSMTest extends PHPUnit_Framework_TestCase
{
    public function testCapabilities()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $this->assertEquals($osm->getTimeout(), 300);
    }

    public function testCapabilities2()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities2.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $this->assertEquals($osm->getMinVersion(), 0.5);
        $this->assertEquals($osm->getMaxVersion(), 0.6);
    }


    public function testGetNode()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $node = $osm->getNode($id);
        $getTags = $node->getTags();

        $this->assertEquals($id, $node->getId());
        $this->assertEquals($getTags['name'], 'Nenagh Bridge');
        $this->assertEquals("52.881667", $node->getLat());
        $this->assertEquals("-8.195833", $node->getLon());
    }

    public function testGetWay()
    {
        $id = 25978036;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertEquals($getTags['highway'], 'service');
        $this->assertEquals($way->getUid(), 1379);
        $this->assertEquals($way->getVersion(), 1);
        $this->assertEquals($way->getUser(), "AndrewMcCarthy");
        $this->assertEquals($way->getNodes(), array("283393706","283393707"));
    }

    public function testGetClosedWay()
    {
        $id = 18197393;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_closed.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertEquals($getTags['building'], 'yes');
        $this->assertTrue($way->isClosed());
    }

    public function testOpenWay()
    {
        $id = 23010474;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_open.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertFalse($way->isClosed());
    }

    public function testWayWithOneNode()
    {
        $id = 23010475;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_one_node.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $way = $osm->getWay($id);
        $getTags = $way->getTags();
        $this->assertEquals($id, (int) $way->getAttributes()->id);
        $this->assertFalse($way->isClosed());
    }



    public function testGetHistory()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_history.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $history = $osm->getHistory('node', $id);
        $xml = simplexml_load_string($history);
        $n = $xml->xpath('//osm');
        $this->assertEquals($id, (int) ($n[0]->node->attributes()->id));
    }

    /**
     * Test that the getHistory method detects that it's been passed
     * an unsupported element type.
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Invalid Element Type
     *
     * @return void
     */
    public function testGetHistoryUnsupportedElement()
    {
        $id = 25978036;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_Openstreetmap($config);
        $history = $osm->getHistory('note', $id);
    }

    public function testGetRelation()
    {
        $id = 1152802;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/relation.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/relation_changeset.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org'
        );
        $osm = new Services_Openstreetmap($config);
        $relation = $osm->getRelation($id);
        $this->assertEquals($id, $relation->getId());
        $changeset_id = (int) $relation->getAttributes()->changeset;
        $getTags = $relation->getTags();
        $this->assertEquals($getTags['name'], 'Mitchell Street');
        $this->assertEquals($getTags['type'], 'associatedStreet');

        $changeset = $osm->getChangeset($changeset_id);
        $this->assertEquals($changeset_id, $changeset->getId());
        $getTags = $changeset->getTags();
        $this->assertEquals($getTags['comment'], 'IE. Nenagh. Mitchell Street POIs');
    }

    public function testBboxToMinMax()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_Openstreetmap($config);
        $this->assertEquals(
            $osm->bboxToMinMax(
                "0.0327873", "52.260074599999996",
                "0.0767326", "52.282047299999995"
            ),
            array(
                "52.260074599999996", "0.0327873",
                "52.282047299999995", "0.0767326",
            )
        );

    }

}
// vim:set et ts=4 sw=4:
?>
