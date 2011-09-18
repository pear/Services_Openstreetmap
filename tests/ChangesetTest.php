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


class ChangesetTest extends PHPUnit_Framework_TestCase
{
    public function testGetChangeset()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen('./responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset.xml', 'rb'));

        $cId = 2217466;

        $config = array('adapter' => $mock, 'server' => 'http://www.openstreetmap.org');
        $osm = new Services_Openstreetmap($config);
        $changeset = $osm->getChangeSet($cId);
        $this->assertEquals($cId, (int) $changeset->getId());
        $this->assertEquals("2009-08-20T22:31:06Z", $changeset->getCreatedAt());
        $this->assertEquals("2009-08-20T22:31:08Z", $changeset->getClosedAt());
        $this->assertEquals(false, $changeset->isOpen());
        $this->assertEquals("-8.2205445", $changeset->getMinLon());
        $this->assertEquals("52.857758", $changeset->getMinLat());
        $this->assertEquals("-8.2055278", $changeset->getMaxLon());
        $this->assertEquals("52.8634333", $changeset->getMaxLat());
    }

    public function testChange() {
        $wayId = 30357328;
        $way2Id = 30357329;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen('./responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset_id', 'rb'));
        $mock->addResponse(fopen('./responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen('./responses/way_30357329.xml', 'rb'));
        $mock->addResponse(fopen('./responses/diff_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset_closed', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => './credentials'
        );
        $osm = new Services_Openstreetmap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_Openstreetmap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $this->assertEquals(false, $changeset->isOpen());
        $changeset->begin("Undo accidental highway change from residential to service");
        $ways = $osm->getWays($wayId, $way2Id);
        foreach ($ways as $way) {
            $way->setTag('highway', 'residential');
            $way->setTag('lit', 'yes');
            $changeset->add($way);
        }
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
    }

    /**
     * Test that the same object can not be added to the same changeset.
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Object added to changeset already
     *
     * @return void
     */
    public function testSameObjectAddedToChangeset() {
        $wayId = 30357328;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen('./responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen('./responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen('./responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset_id', 'rb'));
        $mock->addResponse(fopen('./responses/diff_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset_closed', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => './credentials'
        );
        $osm = new Services_Openstreetmap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_Openstreetmap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $way = $osm->getWay($wayId);
        $way->setTag('highway', 'residential');
        $way->setTag('lit', 'yes');
        $way2 = $osm->getWay($wayId);
        $way2->setTag('highway', 'residential');
        $way2->setTag('lit', 'yes');
        $this->assertEquals(false, $changeset->isOpen());
        $changeset->begin("Undo accidental highway change from residential to service");
        $changeset->add($way);
        $changeset->add($way2);
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
    }

    public function testDeleteNode() {
        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen('./responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen('./responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset_id', 'rb'));
        $mock->addResponse(fopen('./responses/diff_1436433375_deleted.xml', 'rb'));
        $mock->addResponse(fopen('./responses/changeset_closed', 'rb'));
        $mock->addResponse(fopen('./responses/410', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => './credentials',
        );
        $osm = new Services_Openstreetmap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_Openstreetmap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $node = $osm->getNode($nodeID);
        $this->assertTrue($node != false);
        $changeset->begin("Delete unrequired node.");
        $changeset->add($node->delete());
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
        $node = $osm->getNode($nodeID);
        $this->assertFalse($node);
    }
}
// vim:set et ts=4 sw=4:
?>
