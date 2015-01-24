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

require_once 'Log.php';
require_once 'Log/null.php';
require_once 'Log/observer.php';

/**
 * Log_Observer_Simple
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       NodeTest.php
 */
class Log_Observer_Simple extends Log_observer
{
    /**
     * Entries
     *
     * @var array
     */
    public $entries = array();

    /**
     * Notify event
     *
     * @param mixed $event Event
     *
     * @return void
     */
    public function notify($event)
    {
        $this->entries[] = $event;
    }
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
class NotesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test getNotesByBBox
     *
     * @return void
     */
    public function testGetNotesByBBox()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/get_notes.xml', 'rb'));

        $server = 'http://api06.dev.openstreetmap.org/';
        $config = array(
            'adapter' => $mock,
            'verbose' => true,
            'server' => $server,
        );
        $osm = new Services_OpenStreetMap($config);

        $log = new Log_null('null', 'null', array(), 7);
        $observer = new Log_Observer_Simple();
        $log->attach($observer);
        $osm->getTransport()->setLog($log);

        $minlon = "-8.2456593";
        $minlat = "52.8488977";
        $maxlon = "-8.1751247";
        $maxlat = "52.8839662";

        $notes = $osm->getNotesByBbox(
            $minlon, $minlat, $maxlon, $maxlat
        );
        $url = $observer->entries[0]['message'];
        $this->assertEquals(
            $url,
            "$server" . "api/0.6/notes.xml?" .
            "bbox=$minlon,$minlat,$maxlon,$maxlat&limit=100&closed=7"
        );
        $this->assertInstanceOf('Services_OpenStreetMap_Notes', $notes);
        $this->assertEquals(count($notes), 1);
    }

    /**
     * Test GetNotesByBBox - set limit
     *
     * @return void
     */
    public function testGetNotesByBBoxSetLimit()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/get_notes.xml', 'rb'));

        $server = 'http://api06.dev.openstreetmap.org/';
        $config = array(
            'adapter' => $mock,
            'verbose' => true,
            'server' => $server,
        );
        $osm = new Services_OpenStreetMap($config);

        $log = new Log_null('null', 'null', array(), 7);
        $observer = new Log_Observer_Simple();
        $log->attach($observer);
        $osm->getTransport()->setLog($log);

        $minlon = "-8.2456593";
        $minlat = "52.8488977";
        $maxlon = "-8.1751247";
        $maxlat = "52.8839662";

        $notes = $osm->getNotesByBbox(
            $minlon, $minlat, $maxlon, $maxlat, 200
        );
        $url = $observer->entries[0]['message'];
        $this->assertEquals(
            $url,
            "$server" . "api/0.6/notes.xml?" .
            "bbox=$minlon,$minlat,$maxlon,$maxlat&limit=200&closed=7"
        );
    }

    /**
     * Test getNotesByBBox - set limit and closed
     *
     * @return void
     */
    public function testGetNotesByBBoxSetLimitAndClosed()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/get_notes.xml', 'rb'));

        $server = 'http://api06.dev.openstreetmap.org/';
        $config = array(
            'adapter' => $mock,
            'verbose' => true,
            'server' => $server,
        );
        $osm = new Services_OpenStreetMap($config);

        $log = new Log_null('null', 'null', array(), 7);
        $observer = new Log_Observer_Simple();
        $log->attach($observer);
        $osm->getTransport()->setLog($log);

        $minlon = "-8.2456593";
        $minlat = "52.8488977";
        $maxlon = "-8.1751247";
        $maxlat = "52.8839662";

        $notes = $osm->getNotesByBbox(
            $minlon, $minlat, $maxlon, $maxlat, 200, 14
        );
        $url = $observer->entries[0]['message'];
        $this->assertEquals(
            $url,
            "$server" . "api/0.6/notes.xml?" .
            "bbox=$minlon,$minlat,$maxlon,$maxlat&limit=200&closed=14"
        );
    }

    /**
     * Testa
     *
     * @return void
     */
    public function testa()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/get_notes.xml', 'rb'));

        $server = 'http://api06.dev.openstreetmap.org/';
        $config = array(
            'adapter' => $mock,
            'server' => $server,
        );
        $osm = new Services_OpenStreetMap($config);


        $minlon = "-8.2456593";
        $minlat = "52.8488977";
        $maxlon = "-8.1751247";
        $maxlat = "52.8839662";

        $notes = $osm->getNotesByBbox(
            $minlon, $minlat, $maxlon, $maxlat, 200, 14
        );
        $note = $notes[0];
        $this->assertInstanceOf('Services_OpenStreetMap_Notes', $notes);
        $this->assertInstanceOf('Services_OpenStreetMap_Note', $note);
        $comments = $note->getComments();
        $comment = $comments[0];
        $this->assertInstanceOf('Services_OpenStreetMap_Comment', $comment);
        $this->assertInstanceOf('Services_OpenStreetMap_Comments', $comments);
    }
}

?>
