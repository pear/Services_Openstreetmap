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
require_once 'PHPUnit/Framework/TestCase.php';


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
     * test the  getCoordsOfPlace method.
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
     * @expectedException Services_OpenStreetMap_Exception
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
     * test setFormat/getFormat methods w html value
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
     * test setFormat/getFormat methods w json value
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
     * test setFormat/getFormat methods w xml value
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
     * test setLimit/getLimit methods
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
     * test JSON search
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

    public function testJsonSearchNameGa()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/search.json', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm = new Services_OpenStreetMap();

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
     * test HTML search
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
     * test getServer/setServer methods
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
            "Москва, Центральный федеральный округ, Российская Федерация",
            $display
        );
    }
}
?>
