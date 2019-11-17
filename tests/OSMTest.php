<?php
/**
 * OSMTest.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       OSMTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
// Don't pull in file if using phpunit installed as a PHAR
if (stream_resolve_include_path('PHPUnit/Framework/TestCase.php')) {
    include_once 'PHPUnit/Framework/TestCase.php';
}

require_once 'Log.php';
require_once 'Log/null.php';
require_once 'Log/observer.php';

/**
 * Log_OSMTest_Observer
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       OSMTest.php
 */
class Log_OSMTest_Observer extends Log_observer
{
    /**
     * Entries
     *
     * @var array
     */
    public $entries = [];

    /**
     * Notify
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
 * Test Services_OpenStreetMap functionality specific only to that class.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       OSMTest.php
 */
class OSMTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check that a Services_OpenStreetMap object can be created ok.
     *
     * @return void
     */
    public function testCreateObject()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(['adapter' => $mock]);
        $this->assertInstanceOf('Services_OpenStreetMap', $osm);
    }

    /**
     * Test that an OpenStreetMap XML datafile can be loaded via the loadXml method.
     *
     * @return void
     */
    public function testLoadXml()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(['adapter' => $mock]);
        $this->assertEquals($osm->getXml(), null);
        $osm->loadXml(__DIR__ . '/files/osm.osm');
        $this->assertNotEquals($osm->getXml(), null);
    }

    /**
     * Test parsing of capability data.
     *
     * @return void
     */
    public function testCapabilities()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        ];
        $osm = new Services_OpenStreetMap($config);
        $this->assertEquals($osm->getTimeout(), 300);
    }

    /**
     * Test parsing of capability data.
     *
     * @return void
     */
    public function testCapabilities2()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities2.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        ];
        $osm = new Services_OpenStreetMap($config);
        $this->assertEquals($osm->getMinVersion(), 0.5);
        $this->assertEquals($osm->getMaxVersion(), 0.6);
        $this->assertEquals($osm->getMaxArea(), 0.25);
        $this->assertEquals($osm->getMaxNoteArea(), 10);
        $this->assertEquals($osm->getTracepointsPerPage(), 5000);
        $this->assertEquals($osm->getMaxNodes(), 2000);
        $this->assertEquals($osm->getMaxElements(), 50000);
        $this->assertEquals($osm->getDatabaseStatus(), 'online');
        $this->assertEquals($osm->getApiStatus(), 'readonly');
        $this->assertEquals($osm->getGpxStatus(), 'offline');
    }

    /**
     * Test parsing of capability data.
     *
     * @return void
     */
    public function testCapabilitiesNoStatus()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/capabilitiesNoStatus.xml', 'rb')
        );

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        ];
        $osm = new Services_OpenStreetMap($config);
        $this->assertEquals($osm->getDatabaseStatus(), null);
        $this->assertEquals($osm->getApiStatus(), null);
        $this->assertEquals($osm->getGpxStatus(), null);
    }

    /**
     * If the minimum version supported by the server is greater than what this
     * package supports then an exception should be thrown.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Specified API Version 0.6 not supported.
     *
     * @return void
     */
    public function testCapabilitiesMin()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities_min.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        ];
        $osm = new Services_OpenStreetMap($config);
    }

    /**
     * If the maximum version supported by the server is lower than a version
     * supported by this package, then an exception should be thrown.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Specified API Version 0.6 not supported.
     *
     * @return void
     */
    public function testCapabilitiesMax()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities_max.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        ];
        $osm = new Services_OpenStreetMap($config);
    }

    /**
     * If invalid/no capabilities are retrieving an exception should be thrown.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Problem checking server capabilities
     *
     * @return void
     */
    public function testCapabilitiesInvalid()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/capabilities_invalid.xml', 'rb')
        );

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/',
        ];
        $osm = new Services_OpenStreetMap($config);
    }

    /**
     * Test retrieving data covering an area.
     *
     * @return void
     */
    public function testGetArea()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/area.xml', 'rb'));

        $server = 'http://api06.dev.openstreetmap.org/';
        $config = [
            'adapter' => $mock,
            'verbose' => true,
            'server' => $server
        ];
        $osm = new Services_OpenStreetMap($config);
        $results = $osm->search(['amenity' => 'pharmacy']);
        $this->AssertTrue(empty($results));

        $minlon = "-8.247245026639696";
        $minlat = "52.84824191354071";
        $maxlat = "52.89957825532213";
        $maxlon = "-8.174161478654796";

        $log = new Log_null('null', 'null', [], 7);
        $observer = new Log_OSMTest_Observer();
        $log->attach($observer);
        $osm->getTransport()->setLog($log);
        $osm->get($minlon, $minlat, $maxlon, $maxlat);
        $entry = $observer->entries[0];
        $this->assertEquals(
            $entry['message'],
            "$server" . "api/0.6/map?bbox=$minlon,$minlat,$maxlon,$maxlat"
        );

        $obj = simplexml_load_string($osm->getXml())->xpath('//bounds');
        $attribs = $obj[0]->attributes();

        $pxminlon = sprintf("%2.7f", (string) $attribs['minlon']);
        $pxminlat = sprintf("%2.7f", (string) $attribs['minlat']);
        $pxmaxlat = sprintf("%2.7f", (string) $attribs['maxlat']);
        $pxmaxlon = sprintf("%2.7f", (string) $attribs['maxlon']);

        $this->assertEquals($pxminlon, sprintf("%2.7f", $minlon));
        $this->assertEquals($pxminlat, sprintf("%2.7f", $minlat));
        $this->assertEquals($pxmaxlat, sprintf("%2.7f", $maxlat));
        $this->assertEquals($pxmaxlon, sprintf("%2.7f", $maxlon));

        $results = $osm->search(['amenity' => 'pharmacy']);

        $tags = [];
        foreach ($results as $result) {
            $tags[] = $result->getTags();
        }

        $this->assertEquals(
            $tags,
            [
                0 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housename' => '20-21',
                    'addr:street' => 'Pearse Street',
                    'amenity' => 'pharmacy',
                    'building' => 'yes',
                    'building:levels' => '3',
                    'building:use' => 'retail',
                    'dispensing' => 'yes',
                    'fax' => '+353 67 34540',
                    'name' => 'Ryans Pharmacy and Beauty Salon',
                    'phone' => '+353 67 31464',
                ],
                1 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housename' => '7',
                    'addr:street' => 'Pearse Street',
                    'amenity' => 'pharmacy',
                    'building' => 'yes',
                    'dispensing' => 'yes',
                    'name' => 'Ray Walsh',
                    'opening_hours' => 'Mo-Fr 09:30-19:00',
                    'phone' => '+353 67 31249',
                    'shop' => 'chemist',
                ],
                2 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housename' => '20-21',
                    'addr:street' => 'Pearse Street',
                    'amenity' => 'pharmacy',
                    'building' => 'yes',
                    'building:levels' => '3',
                    'building:use' => 'retail',
                    'dispensing' => 'yes',
                    'fax' => '+353 67 34540',
                    'name' => 'Ryans Pharmacy and Beauty Salon',
                    'phone' => '+353 67 31464',
                ],
                3 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => 'Unit 1A',
                    'addr:street' => 'O\'Connors Shopping Centre',
                    'amenity' => 'pharmacy',
                    'name' => 'Ann Kelly\'s',
                    'opening_hours' =>
                        'Mo-Th 09:00-18:00; Fr 09:00-19:30; Sa 09:00-18:00',
                    'phone' => '+353 67 34244',
                ],
                4 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housename' => '7',
                    'addr:street' => 'Mitchell Street',
                    'amenity' => 'pharmacy',
                    'dispensing' => 'yes',
                    'name' => 'Guierins',
                    'phone' => '+353 67 31447',
                    ],
                5 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => '69',
                    'addr:street' => 'Kenyon Street',
                    'amenity' => 'pharmacy',
                    'dispensing' => 'yes',
                    'name' => 'Finnerty\'s',
                    'phone' => '+353 67 34155',
                ],
                6 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => '67',
                    'addr:street' => 'Kenyon Street',
                    'amenity' => 'pharmacy',
                    'name' => 'Cuddys',
                    'phone' => '+353 67 31262',
                ],
                7 => [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:street' => 'Clare Street',
                    'amenity' => 'pharmacy',
                    'dispensing' => 'yes',
                    'fax' => '+3536742775',
                    'name' => 'Clare Street Pharmacy',
                    'opening_hours' => 'Mo-Sa 09:15-18:00',
                    'phone' => '+3536742775',
                ],
            ]
        );
    }

    /**
     * Test getXML's return value
     *
     * @return void
     */
    public function testGetReturnValue()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/area.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        ];
        $osm = new Services_OpenStreetMap($config);
        $results = $osm->search(['amenity' => 'pharmacy']);
        $this->AssertTrue(empty($results));
        $xml = $osm->get(
            52.84824191354071, -8.247245026639696,
            52.89957825532213, -8.174161478654796
        );
        $xml1 = $osm->getXml();
        $this->assertEquals($xml, $xml1);
    }

    /**
     * Test searching for a value where it is part of a semicolon delimited
     * string.
     *
     * @return void
     */
    public function testSearchDelimited()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/area.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        ];
        $osm = new Services_OpenStreetMap($config);
        $results = $osm->search(['amenity' => 'pharmacy']);
        $this->AssertTrue(empty($results));
        $osm->get(
            52.84824191354071, -8.247245026639696,
            52.89957825532213, -8.174161478654796
        );
        $results = $osm->search(['amenity' => 'restaurant']);

        $tags = [];
        foreach ($results as $result) {
            $tags[] = $result->getTags();
        }

        $this->assertEquals(
            $tags,
            [
                0 =>
                [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => '19',
                    'addr:street' => 'Pearse Street',
                    'amenity' => 'restaurant',
                    'building' => 'yes',
                    'building:levels' => '3',
                ],
                1 =>
                [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => '26',
                    'addr:street' => 'Kenyon Street',
                    'amenity' => 'restaurant',
                    'name' => 'The Peppermill',
                ],
                2 =>
                [
                    'amenity' => 'restaurant',
                    'cuisine' => 'italian',
                    'name' => 'Pepe\'s Restaurant',
                ],
                3 =>
                [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => '19',
                    'addr:street' => 'Kenyon Street',
                    'amenity' => 'restaurant',
                    'name' => 'Simply Food',
                ],
                4 =>
                [
                    'amenity' => 'restaurant',
                    'cuisine' => 'chinese',
                    'name' => 'Jin\'s',
                ],
                5 =>
                [
                    'addr:city' => 'Nenagh',
                    'addr:country' => 'IE',
                    'addr:housenumber' => '23',
                    'addr:street' => 'Sarsfield Street',
                    'amenity' => 'pub;restaurant',
                    'name' => 'Andy\'s',
                    'phone' => '+353 67 32494',
                    'tourism' => 'guest_house',
                    'website' => 'http://www.andysnenagh.com',
                ],
                6 =>
                [
                    'amenity' => 'restaurant',
                    'cuisine' => 'chinese',
                    'name' => 'Golden Star',
                    'opening_hours' => 'Mo-Su 17:00-24:00',
                ],
                7 =>
                [
                    'amenity' => 'restaurant',
                    'cuisine' => 'indian',
                    'email' => 'turbanrest@gmail.com',
                    'name' => 'Turban',
                    'opening_hours' => 'Mo-Su 16:30-23:00; Fr,Sa 16:30-23:30',
                    'phone' => '+353 67 42794',
                ],
            ]
        );
    }

    /**
     * Test the getCoordsOfPlace method.
     *
     * @return void
     */
    public function testGetCoordsOfPlace()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/nominatim_search_limerick.xml', 'rb')
        );

        $osm = new Services_OpenStreetMap(['adapter' => $mock]);
        $this->AssertEquals(
            $osm->getCoordsOfPlace('Limerick, Ireland'),
            ['lat'=> '52.6612577', 'lon'=> '-8.6302084']
        );
    }

    /**
     * An exception should be thrown if the place of interest can not be
     * found.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Could not get coords for Neeenaaa, Ireland
     *
     * @return void
     */
    public function testGetCoordsOfNonExistentPlace()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/nominatim_search_neeenaaa.xml',
                'rb'
            )
        );

        $osm = new Services_OpenStreetMap(['adapter' => $mock]);
        $osm->getCoordsOfPlace('Neeenaaa, Ireland');
    }

    /**
     * Test retrieving the history of an object.
     *
     * @return void
     */
    public function testGetHistory()
    {
        $id = 52245107;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_history.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org'
        ];
        $osm = new Services_OpenStreetMap($config);
        $node = $osm->getNode($id);
        $history = $node->history();
        foreach ($history as $key=>$version) {
            $this->assertEquals($version, $history[$key]);
            $this->assertEquals($id, $version->getId());
        }
    }

    /**
     * Test the bboxToMinMax method
     *
     * @return void
     */
    public function testBboxToMinMax()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = ['adapter' => $mock];
        $osm = new Services_OpenStreetMap($config);
        $this->assertEquals(
            $osm->bboxToMinMax(
                '0.0327873', '52.260074599999996',
                '0.0767326', '52.282047299999995'
            ),
            [
                '52.260074599999996', '0.0327873',
                '52.282047299999995', '0.0767326',
            ]
        );
    }


    /**
     * Test default value of attributes when creating an object.
     *
     * @return void
     */
    public function testAttribsNotSet()
    {
        $node = new Services_OpenStreetMap_Node();
        $this->assertEquals($node->getVersion(), null);
        $this->assertEquals($node->getUser(), null);
        $this->assertEquals($node->getUid(), null);
        $this->assertEquals($node->getId(), null);
        $this->assertEquals('' . $node, '');
    }
}
// vim:set et ts=4 sw=4:
?>
