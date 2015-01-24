<?php
/**
 * Unit testing class for retrieving OpenStreetMap permission data.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       PermissionsTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
// Don't pull in file if using phpunit installed as a PHAR
if (stream_resolve_include_path('PHPUnit/Framework/TestCase.php')) {
    include_once 'PHPUnit/Framework/TestCase.php';
}

/**
 * Unit test class for permission based functionality.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       PermissionsTest.php
 */
class PermissionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that permission data is parsed correctly.
     *
     * @return void
     */
    public function testPermissions()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/permissions.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'wilma4evah'
        );

        $osm = new Services_OpenStreetMap($config);
        $permissions = $osm->getPermissions();
        $expected = array (
            0 => 'allow_read_prefs',
            1 => 'allow_write_prefs',
            2 => 'allow_write_diary',
            3 => 'allow_write_api',
            4 => 'allow_read_gpx',
            5 => 'allow_write_gpx',
            6 => 'allow_write_notes',
        );
        $this->assertEquals($permissions, $expected);
    }

    /**
     * Should get an empty array (unauthorised connection)
     *
     * @return void
     */
    public function testPermissionsUnauthorised()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/permissionsUnauthorised.xml', 'rb')
        );
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'wilmaAevah'
        );

        $osm = new Services_OpenStreetMap($config);
        $permissions = $osm->getPermissions();
        $expected = array();
        $this->assertEquals($permissions, $expected);
    }

}
// vim:set et ts=4 sw=4:
?>
