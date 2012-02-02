<?php
/**
 * Unit testing class for retrieving Openstreetmap user data.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_Openstreetmap
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

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Unit test class for user related functionality.
 *
 * @category   Services
 * @package    Services_Openstreetmap
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

        $osm = new Services_Openstreetmap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getDisplayName(), 'fredflintstone');
        $this->assertEquals($user->getId(), 124);
        $this->assertEquals(
            $user->getImage(),
            'http://www.openstreetmap.org/user/image/124/me.jpg'
        );
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

        $osm = new Services_Openstreetmap($config);
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

        $osm = new Services_Openstreetmap($config);
        $user = $osm->getUser();
        $this->assertEquals($user->getLat(), 1.234567);
        $this->assertEquals($user->getLon(), -1.234567);
    }
}
// vim:set et ts=4 sw=4:
?>
