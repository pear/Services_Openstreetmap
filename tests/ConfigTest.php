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
 * @link     ConfigTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';

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
        );

        $osm = new Services_Openstreetmap($config);

        $this->assertEquals(
            $osm->getConfig(),
            array (
                'api_version' => '0.6',
                'User-Agent' => 'Services_Openstreetmap',
                'adapter' => $mock,
                'server' => 'http://www.openstreetmap.org/',
                'verbose' => false,
                'user' => null,
                'password' => null,
                'passwordfile' => null,
            )
        );
        $this->assertEquals('0.6', $osm->getConfig('api_version'));
        $osm->setConfig('User-Agent', 'Acme 1.2');
        $this->assertEquals($osm->getConfig('User-Agent'), 'Acme 1.2');
        $osm->setConfig('api_version', '0.5');
        $this->assertEquals($osm->getConfig('api_version'), '0.5');
    }

    /**
     * Test unknown config detection
     *
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'api'
     *
     * @return void
     */
    public function testConfig2()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array('adapter' => $mock);
        $osm = new Services_Openstreetmap($config);

        $osm->setConfig('api', '0.5');
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

        $osm->getConfig('api');
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'UserAgent'
     *
     * @return void
     */
    public function testUnrecognisedConfig()
    {
        $osm = new Services_Openstreetmap();
        $osm->setConfig('UserAgent', 'Acme/1.2');
    }

    /**
     * @expectedException Services_Openstreetmap_Exception
     * @expectedExceptionMessage Unknown config parameter 'UserAgent'
     *
     * @return void
     */
    public function testUnrecognisedConfigByArray()
    {
        $osm = new Services_Openstreetmap(array ('UserAgent'=> 'Acme/1.2'));
    }

    public function testSetServer()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/404', 'rb'));
        $osm = new Services_Openstreetmap(array('adapter' => $mock));
        try {
            $osm->setConfig('server', 'http://example.com');
        } catch (Services_Openstreetmap_Exception $ex) {
            $this->assertEquals(
                $ex->getMessage(),
                'Could not get a valid response from server'
            );
            $this->assertEquals($ex->getCode(), 404);
        }
        $config = $osm->getConfig('server');
        $this->assertEquals($config, 'http://example.com');
    }

    public function testSetPasswordFile()
    {
        $osm = new Services_Openstreetmap();
        $osm->setConfig('passwordfile', __DIR__ . '/files/pwd_1line');
        $config = $osm->getConfig('passwordfile');
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
        $osm = new Services_Openstreetmap();
        $osm->setConfig('passwordfile', __DIR__ . '/files/credentels');
    }

    public function testEmptyPasswordFile()
    {
        $osm = new Services_Openstreetmap();
        $osm->setConfig('passwordfile', __DIR__ . '/files/pwd_empty');
        $config = $osm->getConfig('passwordfile');
        $this->assertEquals($config, __DIR__ . '/files/pwd_empty');
        $this->assertNull($osm->getConfig('user'));
        $this->assertNull($osm->getConfig('password'));
    }

    public function test1LinePasswordFile()
    {
        $osm = new Services_Openstreetmap();
        $osm->setConfig('passwordfile', __DIR__ . '/files/pwd_1line');
        $config = $osm->getConfig();
        $this->assertEquals($config['user'], 'fred@example.com');
        $this->assertEquals($config['password'], 'Wilma4evah');
    }

    public function testMultiLinedPasswordFile()
    {
        $osm = new Services_Openstreetmap(array('user' => 'fred@example.com'));
        $config = $osm->getConfig();
        $this->assertEquals($config['password'], null);
        $osm->setConfig('passwordfile', __DIR__ . '/files/pwd_multi');
        $config = $osm->getConfig();
        $this->assertEquals($config['password'], 'Wilma4evah');
    }
}
// vim:set et ts=4 sw=4:
?>
