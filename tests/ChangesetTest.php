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
 * @link     OSMTest.php
 * @todo
 */

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
        $mock->addResponse(fopen('./responses/way_30357328.xml', 'rb'));
        $mock->addResponse(fopen('./responses/way_30357329.xml', 'rb'));
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
        $way2 = $osm->getWay($way2Id);
        $way2->setTag('highway', 'residential');
        $way2->setTag('lit', 'yes');
        $this->assertEquals(false, $changeset->isOpen());
        $changeset->begin("Undo accidental highway change from residential to service");
        $changeset->add($way);
        $changeset->add($way2);
        $user = $changeset->getUser();
        $this->assertEquals(true, $changeset->isOpen());
        $changeset->commit();
    }
}
// vim:set et ts=4 sw=4:
?>
