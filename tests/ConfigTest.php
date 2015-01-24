<?php
/**
 * Unit testing for Services_OpenStreetMap_Config class.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       ConfigTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
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
class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * General test of the Config class
     *
     * @return void
     */
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
            'User-Agent' => 'Services_OpenStreetMap',
            'server' => 'http://api06.dev.openstreetmap.org/'
        );

        $osm = new Services_OpenStreetMap($config);

        $this->assertEquals(
            $osm->getConfig()->asArray(),
            array (
                'accept-language' => 'en',
                'api_version' => '0.6',
                'User-Agent' => 'Services_OpenStreetMap',
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
     * Test unknown config detection.
     *
     * @expectedException        Services_OpenStreetMap_InvalidArgumentException
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testUnknownConfig()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_OpenStreetMap($config);

        $osm->getConfig()->setValue('api', '0.5');
    }

    /**
     * Test unknown config detection.
     *
     * @expectedException        Services_OpenStreetMap_InvalidArgumentException
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testConfig3()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_OpenStreetMap($config);

        $osm->getConfig()->getValue('api');
    }

    /**
     * Test getValue method with an empty parameter
     *
     * @return void
     */
    public function testGetValueEmptyParameter()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_OpenStreetMap($config);

        $configValues = $osm->getConfig()->getValue();
        $this->assertEquals(
            $configValues['server'],
            'http://api.openstreetmap.org/'
        );
        $this->assertEquals($configValues['api_version'], '0.6');
        $this->assertEquals($configValues['User-Agent'], 'Services_OpenStreetMap');
        $this->assertNull($configValues['user']);
        $this->assertNull($configValues['password']);
        $this->assertNull($configValues['passwordfile']);
    }

    /**
     * Setting an unrecognised config setting should raise an exception.
     *
     * @expectedException        Services_OpenStreetMap_InvalidArgumentException
     * @expectedExceptionMessage Unknown config parameter 'UserAgent'
     *
     * @return void
     */
    public function testUnrecognisedConfig()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setValue('UserAgent', 'Acme/1.2');
    }

    /**
     * Try the same, this time with the config settings in an array.
     *
     * @expectedException        Services_OpenStreetMap_InvalidArgumentException
     * @expectedExceptionMessage Unknown config parameter 'UserAgent'
     *
     * @return void
     */
    public function testUnrecognisedConfigByArray()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(
            array(
                'adapter' => $mock,
                'UserAgent'=> 'Acme/1.2'
            )
        );
    }

    /**
     * Set server value via the setValue method - with scenario of
     * something wrong with the API server.
     *
     * @return void
     */
    public function testSetServer()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));
        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        try {
            $osm->getConfig()->setValue('server', 'http://example.com');
        } catch (Services_OpenStreetMap_Exception $ex) {
            $this->assertEquals(
                $ex->getMessage(),
                'Could not get a valid response from server'
            );
            $this->assertEquals($ex->getCode(), 404);
        }
        $config = $osm->getConfig()->getValue('server');
        $this->assertEquals($config, 'http://example.com');
    }

    /**
     * Set server value via the explicit setServer method.
     *
     * @return void
     */
    public function testSetServerExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
            $osm->getConfig()->setServer('http://example.com');
        $config = $osm->getConfig()->getValue('server');
        $this->assertEquals($config, 'http://example.com');
    }

    /**
     * Set passwordfile value using the setValue method.
     *
     * @return void
     */
    public function testSetPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm  = new Services_OpenStreetMap(array('adapter' => $mock));
        $cobj = $osm->getConfig();
        $cobj->setValue('passwordfile', __DIR__ . '/files/pwd_1line');
        $config = $cobj->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_1line');
    }

    /**
     * Set passwordfile value using the setPasswordfile method.
     *
     * @return void
     */
    public function testSetPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm  = new Services_OpenStreetMap(array('adapter' => $mock));
        $cobj = $osm->getConfig();
        $cobj->setPasswordfile(__DIR__ . '/files/pwd_1line');
        $config = $cobj->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_1line');
    }

    /**
     * Exception should be thrown if the password file being set doesn't exist.
     * Do this via the setValue method.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Could not read password file
     *
     * @return void
     */
    public function testSetNonExistingPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setValue('passwordfile', __DIR__ . '/files/credentels');
    }

    /**
     * Exception should be thrown if the password file being set doesn't exist
     * Do this via the explicit setPasswordfile method.
     *
     * @expectedException        Services_OpenStreetMap_Exception
     * @expectedExceptionMessage Could not read password file
     *
     * @return void
     */
    public function testSetNonExistingPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setPasswordfile(__DIR__ . '/files/credentels');
    }

    /**
     * Empty password file - set with setValue
     *
     * @return void
     */
    public function testEmptyPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setValue('passwordfile', __DIR__ . '/files/pwd_empty');
        $config = $osm->getConfig()->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_empty');
        $this->assertNull($osm->getConfig()->getValue('user'));
        $this->assertNull($osm->getConfig()->getValue('password'));
    }

    /**
     * Empty password file - set with setPasswordfile
     *
     * @return void
     */
    public function testEmptyPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setPasswordfile(__DIR__ . '/files/pwd_empty');
        $config = $osm->getConfig()->getValue('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_empty');
        $this->assertNull($osm->getConfig()->getValue('user'));
        $this->assertNull($osm->getConfig()->getValue('password'));
    }

    /**
     * One line password file - set with setValue
     *
     * @return void
     */
    public function test1LinePasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setValue('passwordfile', __DIR__ . '/files/pwd_1line');
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('user'), 'fred@example.com');
        $this->assertEquals($config->getValue('password'), 'Wilma4evah');
    }

    /**
     * One line password file - set with setPasswordfile
     *
     * @return void
     */
    public function test1LinePasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(array('adapter' => $mock));
        $osm->getConfig()->setPasswordfile(__DIR__ . '/files/pwd_1line');
        $config = $osm->getConfig();
        $this->assertEquals($config->getValue('user'), 'fred@example.com');
        $this->assertEquals($config->getValue('password'), 'Wilma4evah');
    }

    /**
     * One line password file - set with setValue
     *
     * @return void
     */
    public function testMultiLinedPasswordFile()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(
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

    /**
     * One line password file - set with setPasswordfile
     *
     * @return void
     */
    public function testMultiLinedPasswordFileExplicitMethod()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $osm = new Services_OpenStreetMap(
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

    /**
     * Test generator
     *
     * @return void
     */
    public function testGenerator()
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
            'User-Agent' => 'Services_OpenStreetMap',
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $generator = $osm->getConfig()->getGenerator();
        $this->assertEquals($generator, 'OpenStreetMap server');

        $mock2 = new HTTP_Request2_Adapter_Mock();
        $mock2->addResponse(
            fopen(__DIR__ . '/responses/capabilities_jxapi.xml', 'rb')
        );

        $config['adapter'] = $mock2;
        $osm = new Services_OpenStreetMap($config);
        $generator = $osm->getConfig()->getGenerator();
        $this->assertEquals($generator, 'Java XAPI Server');
    }
}
// vim:set et ts=4 sw=4:
?>
