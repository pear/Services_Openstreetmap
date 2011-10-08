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
    public function testCreateObject()
    {
        $osm = new Services_Openstreetmap();
    }

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

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Specified API Version 0.6 not supported.
     *
     * @return void
     */
    public function testCapabilitiesMin()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities_min.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Specified API Version 0.6 not supported.
     *
     * @return void
     */
    public function testCapabilitiesMax()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities_max.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Problem checking server capabilities
     *
     * @return void
     */
    public function testCapabilitiesInvalid()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities_invalid.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
    }

    public function testGetArea()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/area.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $results = $osm->search(array("amenity" => "pharmacy"));
        $this->AssertTrue(empty($results));
        $osm->get(
            52.84824191354071, -8.247245026639696,
            52.89957825532213, -8.174161478654796
        );
        $results = $osm->search(array("amenity" => "pharmacy"));
        $this->assertEquals(
            $results,
            array (
                0 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housename' => '20-21',
                    'addr_street' => 'Pearse Street',
                    'amenity' => 'pharmacy',
                    'building' => 'yes',
                    'building_levels' => '3',
                    'building_use' => 'retail',
                    'dispensing' => 'yes',
                    'fax' => '+353 67 34540',
                    'name' => 'Ryans Pharmacy and Beauty Salon',
                    'phone' => '+353 67 31464',
                ),
                1 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housename' => '7',
                    'addr_street' => 'Pearse Street',
                    'amenity' => 'pharmacy',
                    'building' => 'yes',
                    'dispensing' => 'yes',
                    'name' => 'Ray Walsh',
                    'opening_hours' => 'Mo-Fr 09:30-19:00',
                    'phone' => '+353 67 31249',
                    'shop' => 'chemist',
                ),
                2 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housename' => '20-21',
                    'addr_street' => 'Pearse Street',
                    'amenity' => 'pharmacy',
                    'building' => 'yes',
                    'building_levels' => '3',
                    'building_use' => 'retail',
                    'dispensing' => 'yes',
                    'fax' => '+353 67 34540',
                    'name' => 'Ryans Pharmacy and Beauty Salon',
                    'phone' => '+353 67 31464',
                ),
                3 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housenumber' => 'Unit 1A',
                    'addr_street' => 'O\'Connors Shopping Centre',
                    'amenity' => 'pharmacy',
                    'name' => 'Ann Kelly\'s',
                    'opening_hours' =>
                        'Mo-Th 09:00-18:00; Fr 09:00-19:30; Sa 09:00-18:00',
                    'phone' => '+353 67 34244',
                ),
                4 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housename' => '7',
                    'addr_street' => 'Mitchell Street',
                    'amenity' => 'pharmacy',
                    'dispensing' => 'yes',
                    'name' => 'Guierins',
                    'phone' => '+353 67 31447',
                    ),
                5 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housenumber' => '69',
                    'addr_street' => 'Kenyon Street',
                    'amenity' => 'pharmacy',
                    'dispensing' => 'yes',
                    'name' => 'Finnerty\'s',
                    'phone' => '+353 67 34155',
                ),
                6 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_housenumber' => '67',
                    'addr_street' => 'Kenyon Street',
                    'amenity' => 'pharmacy',
                    'name' => 'Cuddys',
                    'phone' => '+353 67 31262',
                ),
                7 => array (
                    'addr_city' => 'Nenagh',
                    'addr_country' => 'IE',
                    'addr_street' => 'Clare Street',
                    'amenity' => 'pharmacy',
                    'dispensing' => 'yes',
                    'fax' => '+3536742775',
                    'name' => 'Clare Street Pharmacy',
                    'opening_hours' => 'Mo-Sa 09:15-18:00',
                    'phone' => '+3536742775',
                ),
            )
        );
    }

    public function testGetWay()
    {
        $id = 25978036;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/way.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server' => 'http://www.openstreetmap.org/'
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
            'server' => 'http://www.openstreetmap.org/'
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

    public function testGetCoordsOfPlace()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/nominatim_search_limerick.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $this->AssertEquals(
            $osm->getCoordsOfPlace("Limerick, Ireland"),
            array("lat"=> "52.6612577", "lon"=> "-8.6302084")
        );
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

    public function testUser()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://www.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_Openstreetmap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getDisplayName(), 'fredflintstone');
        $this->assertEquals($user->getId(), 124);
        $this->assertEquals($user->getImage(), 'http://www.openstreetmap.org/user/image/124/me.jpg');
        $this->assertEquals($user->getAccountCreated(), "2003-09-02T15:27:52Z");
        $this->assertEquals($user->getDescription(), "Yabba dabba do!");
        $this->assertEquals($user->getLon(), null);
        $this->assertEquals($user->getLat(), null);
        $this->assertEquals($user->getLanguages(), array('en-US','en'));
        $this->assertEquals(
            $user->getPreferences(),
            array( "diary.default_language" => "en")
        );
    }

    public function testUserNoImage()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_no_image.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://www.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_Openstreetmap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getImage(), null);
    }

    public function testUserHomeSet()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_home_set.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://www.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_Openstreetmap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getLat(), 1.234567);
        $this->assertEquals($user->getLon(), -1.234567);
    }
}
// vim:set et ts=4 sw=4:
?>
