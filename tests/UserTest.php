<?php
/**
 * Unit testing class for retrieving OpenStreetMap user data.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       UserTest.php
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
 * Unit test class for user related functionality.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       UserTest.php
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that user data is parsed correctly.
     *
     * @return void
     */
    public function testUser()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_OpenStreetMap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getDisplayName(), 'fredflintstone');
        $this->assertEquals($user->getId(), 124);
        $this->assertEquals(
            $user->getImage(),
            'http://www.openstreetmap.org/user/image/124/me.jpg'
        );
        $this->assertEquals($user->getAccountCreated(), "2003-09-02T15:27:52Z");
        $this->assertEquals($user->getDescription(), "Yabba dabba do!");
        $this->assertEquals($user->getLon(), -8.2284600830085);
        $this->assertEquals($user->getLat(), 52.222687925572);
        $this->assertEquals($user->getZoom(), 3);
        $this->assertEquals($user->getChangesets(), 1910);
        $this->assertEquals($user->getTraces(), 115);
        $this->assertEquals($user->getBlocksReceived(), 1);
        $this->assertEquals($user->getBlocksIssued(), 15);
        $this->assertEquals($user->getActiveBlocksReceived(), 0);
        $this->assertEquals($user->getActiveBlocksIssued(), 4);
        $this->assertEquals($user->getLanguages(), array('en-US','en'));
        $this->assertEquals($user->getRoles(), array('moderator'));
        $this->assertEquals(
            $user->getPreferences(),
            array("diary.default_language" => "en")
        );
    }

    /**
     * If there is no image set for a user, then getImage should return null.
     *
     * @return void
     */
    public function testUserNoImage()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_no_image.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_OpenStreetMap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getImage(), null);
    }

    /**
     * Test the getLat and getLon methods of the User object.
     *
     * @return void
     */
    public function testUserHomeSet()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_home_set.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user_preferences.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_OpenStreetMap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getLat(), 1.234567);
        $this->assertEquals($user->getLon(), -1.234567);
    }

    /**
     * Test getting user info for user other than authorised user
     *
     * @return void
     */
    public function testUser11324()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/user11324.xml', 'rb'));
        $config = array(
            'adapter' => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
            'user' => 'fred@example.com',
            'password' => 'w1lma4evah'
        );

        $osm = new Services_OpenStreetMap($config);
        $user = $osm->getUserById(6367);
        $this->assertEquals($user->getDisplayName(), 'kenguest');
        $this->assertEquals($user->getId(), 11324);
        $this->assertEquals($user->getChangesets(), 1910);
        $this->assertEquals($user->getTraces(), 115);
        $this->assertEquals($user->getBlocksReceived(), 0);
        $this->assertEquals($user->getActiveBlocksReceived(), 0);
        $this->assertNull($user->getBlocksIssued());
        $this->assertNull($user->getActiveBlocksIssued());
        $this->assertNull($user->getLanguages());
        $this->assertEquals($user->getRoles(), array());
    }
}
// vim:set et ts=4 sw=4:
?>
