<?php
/**
 * Unit test class for Changeset related functionality.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       ChangesetTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(
        dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path()
    );
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
if (stream_resolve_include_path('PHPUnit/Framework/TestCase.php')) {
    include_once 'PHPUnit/Framework/TestCase.php';
}

/**
 * Unit test class for Changeset related functionality.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       ChangesetTest.php
 */
class ChangesetTest extends PHPUnit_Framework_TestCase
{
    protected $credentialsFile = '/credentials';

    /**
     * Setup - if there is a 'credentials' file, use it. Otherwise fall back to
     * using the credentials.dist file.
     *
     * @return void
     */
    public function setup()
    {
        if (file_exists(__DIR__ . $this->credentialsFile)) {
            $this->credentialsFile = __DIR__ . $this->credentialsFile;
        } else {
            $this->credentialsFile = __DIR__ . '/credentials.dist';
        }
    }

    /**
     * Retrieve a changeset and check its attributes are as expected.
     *
     * @return void
     */
    public function testGetChangeset()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset.xml', 'rb'));

        $cId = 2217466;

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        ];
        $osm = new Services_OpenStreetMap($config);
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
     * An exception should be thrown when starting a changeset without having a
     * password set.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Password must be set
     *
     * @return void
     */
    public function testPasswordNotSet()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'user' => 'fred@example.com'
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
        }
        $this->assertEquals(false, $changeset->isOpen());
        $changeset->begin('Start a changeset');
    }

    /**
     * An exception should be thrown when starting a changeset without having a
     * [valid] username set.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage User must be set
     *
     * @return void
     */
    public function testUserNotSet()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'password' => 'wilma4evah'
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
        }
        $this->assertEquals(false, $changeset->isOpen());
        $changeset->begin('Undo accidental highway change');
    }

    /**
     * A successful run through making changes to some ways and committing
     * them.
     *
     * @return void
     */
    public function testChange()
    {
        $wayId = 30357328;
        $way2Id = 30357329;

        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/way_30357328_30357329.xml', 'rb')
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/diff_30357328_30357329.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/changeset_closed', 'rb')
        );

        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
        }
        $this->assertEquals(false, $changeset->isOpen());
        $ways = $osm->getWays($wayId, $way2Id);
        foreach ($ways as $way) {
            $tags = $way->getTags();
            if ($tags['highway'] == 'residential') {
                return;
            }
        }
        $this->assertEquals(2, count($ways));
        $changeset->begin(
            'Undo accidental highway change from residential to service'
        );
        foreach ($ways as $way) {
            $way->setTag('highway', 'residential');
            $way->setTag('lit', 'yes');
            $changeset->add($way);
        }
        $this->assertEquals(true, $changeset->isOpen());
        $success = $changeset->commit();
        $this->assetEquals(true, $success);
    }

    /**
     * Test that an object can not be added to a closed changeset.
     * A changeset is closed after it has been committed.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Object added to closed changeset
     *
     * @return void
     */
    public function testObjectAddedToChangesetAfterCommit()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $wayId = 30357328;
        $way2Id = 30357329;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/way_30357328_30357329.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/diff_30357328_30357329.xml', 'rb')
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));

        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
        }
        $this->assertEquals(false, $changeset->isOpen());
        $ways = $osm->getWays($wayId, $way2Id);
        $changeset->begin(
            'Undo accidental highway change from residential to service'
        );
        foreach ($ways as $way) {
            $way->setTag('highway', 'residential');
            $way->setTag('lit', 'yes');
            $changeset->add($way);
        }
        $this->assertEquals(true, $changeset->isOpen());
        $success = $changeset->commit();
        $this->assertEquals(true, $success);
        $lat = 52.8638729;
        $lon = -8.1983611;
        $tags = ['building' => 'yes', 'amenity' => 'vet'];
        $node = $osm->createNode($lat, $lon, $tags);
        $changeset->add($node);
    }

    /**
     * Test that the same object can not be added to the same changeset.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Object added to changeset already
     *
     * @return void
     */
    public function testSameObjectAddedToChangeset()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $wayId = 30357328;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/diff_30357328_30357329.xml', 'rb')
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));

        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
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
        $changeset->begin(
            'Undo accidental highway change from residential to service'
        );
        $changeset->add($way);
        $changeset->add($way2);
    }

    /**
     * Test deleting a node - including an 'accidental' second commit...
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Attempt to commit a closed changeset
     *
     * @return void
     */
    public function testDeleteNode()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $nodeID = "1436433375";

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/diff_1436433375_deleted.xml', 'rb')
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/410', 'rb'));

        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $node = $osm->getNode($nodeID);
        $this->assertTrue($node != false);
        $changeset->begin("Delete unrequired node.");
        $changeset->add($node->delete());
        $this->assertEquals(true, $changeset->isOpen());
        $success = $changeset->commit();
        $success = $changeset->commit();
        $node = $osm->getNode($nodeID);
        $this->assertFalse($node);
    }

    /**
     * If a 404 error occurs while closing a changeset [during a commit] then
     * an exception should be thrown. Test for this.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Error closing changeset
     *
     * @return void
     */
    public function testDeleteNodeClosingError404()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/diff_1436433375_deleted.xml', 'rb')
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));

        $config = [
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $node = $osm->getNode($nodeID);
        $this->assertTrue($node != false);
        $changeset->begin("Delete unrequired node.");
        $changeset->add($node->delete());
        $this->assertEquals(true, $changeset->isOpen());
        $success = $changeset->commit();
        $this->assertEquals(true, $success);
    }

    /**
     * If an error occurs while closing a changeset [during a commit] then
     * an exception should be thrown. Test for this.
     *
     * @expectedException       Services_OpenStreetMap_Exception
     * @xpectedExceptionMessage Error closing changeset
     *
     * @return void
     */
    public function testDeleteNodeClosingError400()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/node_1436433375.xml',
                'rb'
            )
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/diff_1436433375_deleted.xml',
                'rb'
            )
        );
        $mock->addResponse(fopen(__DIR__ . '/responses/400', 'rb'));

        $config = [
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $node = $osm->getNode($nodeID);
        $this->assertTrue($node != false);
        $changeset->begin("Delete unrequired node.");
        $changeset->add($node->delete());
        $this->assertEquals(true, $changeset->isOpen());
        $success = $changeset->commit();
        $this->assertEquals(true, $success);
    }

    /**
     * If an error occurs while posting changeset information to the server an
     * exception should be thrown.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Error posting changeset
     *
     * @return void
     */
    public function testDeleteNodeDiffError400()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $nodeID = 1436433375;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_1436433375.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/400', 'rb'));

        $config = [
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        try {
            $changeset = $osm->createChangeset();
        } catch (Services_OpenStreetMap_Exception $e) {
            echo  $e->getMessage();
            return;
        }
        $node = $osm->getNode($nodeID);
        $this->assertTrue($node != false);
        $changeset->begin("Delete unrequired node.");
        $changeset->add($node->delete());
        $this->assertEquals(true, $changeset->isOpen());
        $success = $changeset->commit();
        $this->assertEquals(true, $success);
    }

    /**
     * Open a new changeset, create a node and save it by associating it with
     * that changeset and then committing.
     * The id of the node should change from -1 to a positive integer/double.
     *
     * @return void
     */
    public function testSaveNode()
    {
        if (!file_exists($this->credentialsFile)) {
            $this->markTestSkipped('Credentials file does not exist.');
        }

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_id', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/diff_create_node.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_closed', 'rb'));
        $config = [
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
            'passwordfile' => $this->credentialsFile
        ];
        $osm = new Services_OpenStreetMap($config);
        $lat = 52.8638729;
        $lon = -8.1983611;
        $node = $osm->createNode(
            $lat,
            $lon,
            [
                'building' => 'yes',
                'amenity' => 'vet'
            ]
        );
        $this->assertEquals(
            $node->getTags(),
            [
                'building' => 'yes',
                'amenity' => 'vet',
            ]
        );
        $this->assertEquals($lat, $node->getlat());
        $this->assertEquals($lon, $node->getlon());
        $node->setTag('amenity', 'veterinary')->setTag('name', 'O\'Kennedy');
        $this->assertEquals(
            $node->getTags(),
            [
                'building' => 'yes',
                'amenity' => 'veterinary',
                'name' => 'O\'Kennedy'
            ]
        );
        $this->assertEquals(-1, $node->getId());

        $changeset = $osm->createChangeset();
        $changeset->begin("Add O'Kennedy vets in Nenagh");
        $changeset->add($node);
        $success = $changeset->commit();
        $this->assertEquals(true, $success);
        $this->assertEquals($node->getId(), 1448499623);
    }
}
// vim:set et ts=4 sw=4:
?>
