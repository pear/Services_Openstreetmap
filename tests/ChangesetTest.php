<?php
/**
 * Unit test class for Changeset related functionality.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_Openstreetmap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       ChangesetTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * Unit test class for Changeset related functionality.
 *
 * @category   Services
 * @package    Services_Openstreetmap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       ChangesetTest.php
 */
class ChangesetTest extends PHPUnit_Framework_TestCase
{
    public function testGetChangeset()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset.xml', 'rb'));

        $cId = 2217466;

        $config = array('adapter' => $mock, 'server' => 'http://api06.dev.openstreetmap.org');
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

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Password must be set
     */
    public function testPasswordNotSet()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'user' => 'fred@example.com'
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
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage User must be set
     */
    public function testUserNotSet()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'password' => 'wilma4evah'
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

    }

    public function testChange() {
        $wayId = 30357328;
        $way2Id = 30357329;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
        );
        $osm = new Services_Openstreetmap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_Openstreetmap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $this->assertEquals(false, $changeset->isOpen());
        $ways = $osm->getWays($wayId, $way2Id);
        foreach($ways as $way) {
            $tags = $way->getTags();
            if ($tags['highway'] == 'residential') {
                return;
            }
        }
        $this->assertEquals(2, count($ways));
        $changeset->begin("Undo accidental highway change from residential to service");
        foreach ($ways as $way) {
            $way->setTag('highway', 'residential');
            $way->setTag('lit', 'yes');
            $changeset->add($way);
        }
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
    }

    /**
     * Test that an object can not be added to a closed changeset.
     * A changeset is closed after it has been committed.
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Object added to closed changeset
     *
     * @return void
     */
    public function testObjectAddedToChangesetAfterCommit() {
        $wayId = 30357328;
        $way2Id = 30357329;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
        );
        $osm = new Services_Openstreetmap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_Openstreetmap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $this->assertEquals(false, $changeset->isOpen());
        $ways = $osm->getWays($wayId, $way2Id);
        $changeset->begin("Undo accidental highway change from residential to service");
        foreach ($ways as $way) {
            $way->setTag('highway', 'residential');
            $way->setTag('lit', 'yes');
            $changeset->add($way);
        }
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
        $lat = 52.8638729;
        $lon = -8.1983611;
        $node = $osm->createNode($lat, $lon, array(
                    'building' => 'yes',
                    'amenity' => 'vet')
                );
        $changeset->add($node);
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
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_30357328_30357329.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
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
        $this->assertNotEquals('' . $way, '');
        $way2 = $osm->getWay($wayId);
        $way2->setTag('highway', 'residential');
        $way2->setTag('lit', 'yes');
        $this->assertNotEquals('' . $way2, '');
        $this->assertEquals(false, $changeset->isOpen());
        $changeset->begin("Undo accidental highway change from residential to service");
        $changeset->add($way);
        $changeset->add($way2);
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
    }

    /**
     * Test deleting a node - including an 'accidental' second commit...
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Attempt to commit a closed changeset
     */
    public function testDeleteNode() {
        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_1436433375_deleted.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/410', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
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
        $changeset->commit();
        $node = $osm->getNode($nodeID);
        $this->assertFalse($node);
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Error closing changeset
     */
    public function testDeleteNodeClosingError404() {
        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_1436433375_deleted.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
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
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @xpectedExceptionMessage Error closing changeset
     */
    public function testDeleteNodeClosingError400() {
        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_1436433375_deleted.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/400', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
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
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Error posting changeset
     */
    public function testDeleteNodeDiffError400() {
        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/400', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => __DIR__ . '/credentials'
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
    }

    public function testSaveNode()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_create_node.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));
        $config = array(
                'adapter'  => $mock,
                'server'   => 'http://api06.dev.openstreetmap.org/',
                'passwordfile' => __DIR__ . '/credentials',
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
        $node->setTag('amenity', 'veterinary')->setTag('name', 'O\'Kennedy');
        $this->assertEquals(
                $node->getTags(),
                array(
                    'created_by' => 'Services_Openstreetmap',
                    'building' => 'yes',
                    'amenity' => 'veterinary',
                    'name' => 'O\'Kennedy'
                    )
                );
        $this->assertEquals(-1, $node->getId());

        $changeset = $osm->createChangeset();
        $changeset->begin("Add O'Kennedy vets in Nenagh");
        $changeset->add($node);
        $changeset->commit();
        $this->assertEquals($node->getId(), 1448499623);
    }

    public function testSearch()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_Openstreetmap_Criterion('user', 11324))
        );
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
        $diff = false;
        foreach($changesets as $changeset) {
            if ($changeset->getUid() != 11324) {
                $diff = true;
            }
        }
        $this->assertFalse($diff, 'Unexpected UID present in changeset data');
    }
}
// vim:set et ts=4 sw=4:
?>
