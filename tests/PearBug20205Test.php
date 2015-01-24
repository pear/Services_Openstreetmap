<?php
/**
 * Unit/Regression test for pear bug #20205
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       PearBug20205Test.php
 */
$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';

/**
 * Unit test class regression testing re PEAR bug #20205
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       PearBug20205Test.php
 */
class PearBug20205Test extends PHPUnit_Framework_TestCase
{

    /**
     * Test20205
     *
     * @return void
     */
    public function test20205()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/PEARBug20205_moskau_ru.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/PEARBug20205_moskau_en.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/PEARBug20205_russia_en.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/PEARBug20205_russia_fr.xml', 'rb')
        );

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);

        $osm->getConfig()->setAcceptLanguage('ru,en-AU');
        $test = $osm->getPlace('Moskau');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $expected = "Москва, " .
                    "Центральный федеральный округ, " .
                    "Российская Федерация";
        $this->assertEquals($display, $expected);

        $osm->getConfig()->setAcceptLanguage('en');
        $test = $osm->getPlace('Moskau');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $expected = 'Moscow, Central Federal District, Russian Federation';
        $this->assertEquals($display, $expected);


        $test = $osm->getPlace('Russia');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $expected = 'Russian Federation';
        $this->assertEquals($display, $expected);

        $osm->getConfig()->setAcceptLanguage('fr');
        $test = $osm->getPlace('Russia');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $expected = 'Fédération de Russie';
        $this->assertEquals($display, $expected);
    }
}
