<?php
/**
 * UserTest.php
 * 10-Oct-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     UserTest.php
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api.openstreetmap.org/',
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
            'server'   => 'http://api.openstreetmap.org/',
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
            'server'   => 'http://api.openstreetmap.org/',
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
