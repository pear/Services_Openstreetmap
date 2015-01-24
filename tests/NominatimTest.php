<?php
/**
 * Unit testing for Services_OpenStreetMap_Nominatim class.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       NominatimTest.php
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
 * Test Services_OpenStreetMap_Config functionality and how it's used
 * throughout the Services_OpenStreetMap package.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       ConfigTest.php
 */
class NominatimTest extends PHPUnit_Framework_TestCase
{

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

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $this->AssertEquals(
            $osm->getCoordsOfPlace('Limerick, Ireland'),
            array('lat'=> '52.6612577', 'lon'=> '-8.6302084')
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

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getCoordsOfPlace('Neeenaaa, Ireland');
    }

    /**
     * Test setFormat/getFormat methods w html value
     *
     * @return void
     */
    public function testSetFormatHtml()
    {
        $osm = new Services_OpenStreetMap();
        $transport = $osm->getTransport();
        $nominatim = new Services_OpenStreetMap_Nominatim($transport);
        $nominatim->setFormat('html');
        $this->assertEquals($nominatim->getFormat(), 'html');
    }

    /**
     * Test setFormat/getFormat methods w json value
     *
     * @return void
     */
    public function testSetFormatJson()
    {
        $osm = new Services_OpenStreetMap();
        $transport = $osm->getTransport();
        $nominatim = new Services_OpenStreetMap_Nominatim($transport);
        $nominatim->setFormat('json');
        $this->assertEquals($nominatim->getFormat(), 'json');
    }

    /**
     * Test setFormat/getFormat methods w xml value
     *
     * @return void
     */
    public function testSetFormatXml()
    {
        $osm = new Services_OpenStreetMap();
        $transport = $osm->getTransport();
        $nominatim = new Services_OpenStreetMap_Nominatim($transport);
        $nominatim->setFormat('xml');
        $this->assertEquals($nominatim->getFormat(), 'xml');
    }

    /**
     * Check that an exception is thrown when attempting to set format to an
     * unrecognised value.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Unrecognised format (xhtml)
     *
     * @return void
     */
    public function testInvalidFormat()
    {
        $osm = new Services_OpenStreetMap();
        $transport = $osm->getTransport();
        $nominatim = new Services_OpenStreetMap_Nominatim($transport);
        $nominatim->setFormat('xhtml');
    }


    /**
     * Test setLimit/getLimit methods
     *
     * @return void
     */
    public function testSetLimit()
    {
        $osm = new Services_OpenStreetMap();
        $transport = $osm->getTransport();
        $nominatim = new Services_OpenStreetMap_Nominatim($transport);
        $nominatim->setLimit(1);
        $this->assertEquals($nominatim->getLimit(), 1);
    }

    /**
     * Check that an exception is thrown when attempting to set limit to an
     * unrecognised value.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Limit must be a numeric value
     *
     * @return void
     */
    public function testSetInvalidLimit()
    {
        $osm = new Services_OpenStreetMap();
        $transport = $osm->getTransport();
        $nominatim = new Services_OpenStreetMap_Nominatim($transport);
        $nominatim->setLimit('one');
    }

    /**
     * Test JSON search
     *
     * @return void
     */
    public function testJsonSearch()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/search.json', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));

        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $nominatim->setFormat('json');
        $place = $nominatim->search('Limerick, Ireland', 1);
        $this->assertEquals($place[0]->class, 'place');
        $this->assertEquals($place[0]->type, 'city');
        $this->assertEquals($place[0]->osm_type, 'node');
    }

    /**
     * Test searching for a placename as Gaeilge.
     *
     * @return void
     */
    public function testJsonSearchNameGa()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/search_ga.json', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));

        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $nominatim->setFormat('json');
        $nominatim->setAcceptLanguage('ga');
        $place = $nominatim->search('Limerick, Ireland', 1);
        $this->assertEquals($place[0]->class, 'place');
        $this->assertEquals($place[0]->type, 'city');
        $this->assertEquals($place[0]->osm_type, 'node');
        $display = $place[0]->display_name;
        $this->assertEquals(
            "Luimneach, Contae Luimnigh, Cúige Mumhan, Éire",
            $display
        );
    }

    /**
     * Test HTML search
     *
     * @return void
     */
    public function testHtmlSearch()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/search.html', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $nominatim->setFormat('html');
        $place = $nominatim->search('Limerick, Ireland', 1);
        $this->assertNotNull($place);
    }

    /**
     * Test getServer/setServer methods
     *
     * @return void
     */
    public function testSetServer()
    {
        $osm = new Services_OpenStreetMap();
        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());

        $this->assertEquals(
            $nominatim->getServer(),
            'http://nominatim.openstreetmap.org/'
        );
        $this->assertEquals(
            $nominatim->setServer('mapquest')->getServer(),
            'http://open.mapquestapi.com/nominatim/v1/'
        );
        $this->assertEquals(
            $nominatim->setServer('nominatim')->getServer(),
            'http://nominatim.openstreetmap.org/'
        );
        $this->assertEquals(
            $nominatim->setServer('http://nominatim.example.com/')->getServer(),
            'http://nominatim.example.com/'
        );
    }

    /**
     * Check that an exception is thrown when attempting to set limit to an
     * unrecognised value.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Server endpoint invalid
     *
     * @return void
     */
    public function testSetInvalidServerURL()
    {
        $osm = new Services_OpenStreetMap();
        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $nominatim->setServer('invalid');
    }

    /**
     * Test PEAR Bug 20205
     *
     * @return void
     */
    public function test20205()
    {

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/nominatim_search_20205_1.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/nominatim_search_20205_2.xml', 'rb')
        );
        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setAcceptLanguage('en');
        $test = $osm->getPlace('Moskau');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $this->assertEquals(
            "Moscow, Central Federal District, Russian Federation",
            $display
        );
        $osm->getConfig()->setAcceptLanguage('ru,en-AU');
        $test = $osm->getPlace('Moscow');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $this->assertEquals(
            "Москва, " .
            "Центральный федеральный округ, " .
            "Российская Федерация",
            $display
        );
    }

    /**
     * Check what happens when attempting to set an invalid email address.
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Email address 'test example.com' is not valid
     *
     * @return void
     */
    public function testSetInvalidEmailAddress()
    {
        $osm = new Services_OpenStreetMap();
        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $nominatim->setEmailAddress('test example.com');
    }

    /**
     * Test reverse geocode.
     *
     * This is also a good example of how to use Services_OpenStreetMap_Nominatim
     * separate from the core Services_OpenStreetMap object.
     *
     * @return void
     */
    public function testNomimatimReverseGeocode()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/nominatim_reverse_it.xml', 'rb')
        );

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $xml = $nominatim
            ->setFormat('xml')
            ->reverseGeocode("53.3459641", "-6.2548149");
        $this->AssertEquals(
            $xml[0]->result,
            "The Irish Times, 24-28, Tara Street, Dublin 2, Dublin, " .
            "County Dublin, Leinster, D02, Ireland"
        );
        $this->AssertEquals($xml[0]->addressparts->road, "Tara Street");
        $this->AssertEquals($xml[0]->addressparts->city, "Dublin");

    }
}
?>
