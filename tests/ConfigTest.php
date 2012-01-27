<?php
/**
 * Unit testing for Services_Openstreetmap_Config class.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_Openstreetmap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       ConfigTest.php
 * @todo       update docblocks.
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
 * Test Services_Openstreetmap_Config functionality and how it's used
 * throughout the Services_Openstreetmap package.
 *
 * @category   Services
 * @package    Services_Openstreetmap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       ConfigTest.php
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'api_version' => '0.6',
            'adapter' => $mock,
            'password' => null,
            'passwordfile' => null,
            'user' => null,
            'verbose' => false,
            'User-Agent' => 'Services_Openstreetmap',
            'server' => 'http://api06.dev.openstreetmap.org/'
        );

        $osm = new Services_Openstreetmap($config);

        $this->assertEquals(
            $osm->getConfig()->asArray(),
            array (
                'api_version' => '0.6',
                'User-Agent' => 'Services_Openstreetmap',
                'adapter' => $mock,
                'server' => 'http://api06.dev.openstreetmap.org/',
                'verbose' => false,
                'user' => null,
                'password' => null,
                'passwordfile' => null,
            )
        );
        $this->assertEquals('0.6', $osm->getConfig()->getValue('api_version'));
        $osm->getConfig()->setValue('User-Agent', 'Acme 1.2');
        $this->assertEquals($osm->getConfig()->getValue('User-Agent'), 'Acme 1.2');
        $osm->getConfig()->setValue('api_version', '0.5');
        $this->assertEquals($osm->getConfig()->getValue('api_version'), '0.5');
    }

    /**
     * Test unknown config detection
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testUnknownConfig()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_Openstreetmap($config);

        $osm->getConfig()->setValue('api', '0.5');
    }

    /**
     * Test unknown config detection
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testConfig3()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_Openstreetmap($config);

        $osm->getConfig()->getValue('api');
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'UserAgent'
     *
     * @return void
     */
    public function testUnrecognisedConfig()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setValue('UserAgent', 'Acme/1.2');
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'UserAgent'
     *
     * @return void
     */
    public function testUnrecognisedConfigByArray()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(
            array(
                'adapter' => $mock,
                'UserAgent'=> 'Acme/1.2'
            )
        );
    }

    public function testSetServer()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));
        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        try {
            $osm->getConfig()->setValue('server', 'http://example.com');
        } catch (Services_Openstreetmap_Exception $ex) {
            $this->assertEquals(
                $ex->getMessage(),
                'Could not get a valid response from server'
            );
            $this->assertEquals($ex->getCode(), 404);
        }
        $config = $osm->getConfig()->getValue('server');
        $this->assertEquals($config, 'http://example.com');
    }

    public function testSetServerExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $osm = new Services_Openstreetmap(array('adapter' => $mock));
            $osm->getConfig()->setServer('http://example.com');
        $config = $osm->getConfig()->getValue('server');
        $this->assertEquals($config, 'http://example.com');
    }

    public function testSetPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm  = new Services_Openstreetmap(array('adapter' => $mock));
        $cobj = $osm->getConfig();
        $cobj->setValue('passwordfile', __DIR__ . '/files/pwd_1line');
        $config = $cobj->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_1line');
    }

    public function testSetPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm  = new Services_Openstreetmap(array('adapter' => $mock));
        $cobj = $osm->getConfig();
        $cobj->setPasswordfile( __DIR__ . '/files/pwd_1line');
        $config = $cobj->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_1line');
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Could not read password file
     *
     * @return void
     */
    public function testSetNonExistingPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setValue('passwordfile', __DIR__ . '/files/credentels');
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Could not read password file
     *
     * @return void
     */
    public function testSetNonExistingPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setPasswordfile(__DIR__ . '/files/credentels');
    }

    public function testEmptyPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setValue('passwordfile', __DIR__ . '/files/pwd_empty');
        $config = $osm->getConfig()->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_empty');
        $this->assertNull($osm->getConfig()->getValue('user'));
        $this->assertNull($osm->getConfig()->getValue('password'));
    }

    public function testEmptyPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setPasswordfile(__DIR__ . '/files/pwd_empty');
        $config = $osm->getConfig()->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_empty');
        $this->assertNull($osm->getConfig()->getValue('user'));
        $this->assertNull($osm->getConfig()->getValue('password'));
    }

    public function test1LinePasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setValue('passwordfile', __DIR__ . '/files/pwd_1line');
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('user'), 'fred@example.com');
        $this->assertEquals($config->getValue('password'), 'Wilma4evah');
    }

    public function test1LinePasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        $osm->getConfig()->setPasswordfile(__DIR__ . '/files/pwd_1line');
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('user'), 'fred@example.com');
        $this->assertEquals($config->getValue('password'), 'Wilma4evah');
    }

    public function testMultiLinedPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(
            array(
                'adapter' => $mock,
                'user' => 'fred@example.com'
            )
        );
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('password'), null);
        $config->setValue('passwordfile', __DIR__ . '/files/pwd_multi');
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('password'), 'Wilma4evah');
    }

    public function testMultiLinedPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_Openstreetmap(
            array(
                'adapter' => $mock,
                'user' => 'fred@example.com'
            )
        );
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('password'), null);
        $config->setPasswordfile(__DIR__ . '/files/pwd_multi');
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('password'), 'Wilma4evah');
    }
}
// vim:set et ts=4 sw=4:
?>
