<?php
/**
 * Unit test class for Searching through changesets with Criterion objects.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       CriterionTest.php
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
 * Unit test class for searching through changesets using Criterion objects.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       CriterionTest.php
 */
class CriterionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Search by user id.
     *
     * @return void
     */
    public function testSearchByUser()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_OpenStreetMap_Criterion('user', 11324))
        );
        $this->assertInstanceOf('Services_OpenStreetMap_Changesets', $changesets);
        $diff = false;
        foreach ($changesets as $changeset) {
            if ($changeset->getUid() != 11324) {
                $diff = true;
            }
        }
        $this->assertFalse($diff, 'Unexpected UID present in changeset data');
    }

    /**
     * Searching by an unrecognised constraint type ('uid') should throw an
     * exception.
     *
     * @expectedException        Services_OpenStreetMap_InvalidArgumentException
     * @expectedExceptionMessage Unknown constraint type
     *
     * @return void
     */
    public function testSearchInvalid()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_OpenStreetMap_Criterion('uid', 11324))
        );
    }

    /**
     * Search by a user's display_name.
     *
     * @return void
     */
    public function testSearchByDisplayName()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_OpenStreetMap_Criterion('display_name', 'kenguest'))
        );
        $this->assertInstanceOf('Services_OpenStreetMap_Changesets', $changesets);
    }

    /**
     * Check that an exception is thrown if attempting to search by both
     * user id and display_name
     *
     * @expectedException        Services_OpenStreetMap_RuntimeException
     * @expectedExceptionMessage Can't supply both user and display_name criteria
     *
     * @return void
     */
    public function testSearchByDisplayNameAndUser()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $displayName = new Services_OpenStreetMap_Criterion(
            'display_name',
            'kenguest'
        );
        $this->assertEquals($displayName->type(), 'display_name');
        $user = new Services_OpenStreetMap_Criterion('user', 11324);
        $changesets = $osm->searchChangesets(array($displayName, $user));
        $this->assertInstanceOf('Services_OpenStreetMap_Changesets', $changesets);
    }

    /**
     * Search for changesets focused on a specific area/bounding box.
     *
     * @return void
     */
    public function testSearchByBbox()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);
        $changesets = $osm->searchChangesets(
            array(
                new Services_OpenStreetMap_Criterion(
                    'bbox',
                    -8.0590275,
                    52.9347449,
                    -7.9966939,
                    52.9611999
                )
            )
        );
        $this->assertInstanceOf('Services_OpenStreetMap_Changesets', $changesets);
    }

    /**
     * Test searching on multiple criteria: changesets by specific user id,
     * focused on a certain area (bbox) which have been closed.
     *
     * @return void
     */
    public function testSearchByMultipleCriteria()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);

        $user = new Services_OpenStreetMap_Criterion('user', 11324);
        $this->assertEquals($user->type(), 'user');
        $this->assertEquals($user->query(), 'user=11324');

        $bbox = new Services_OpenStreetMap_Criterion(
            'bbox',
            -8.0590275,
            52.9347449,
            -7.9966939,
            52.9611999
        );
        $this->assertEquals($bbox->type(), 'bbox');
        $this->assertEquals(
            $bbox->query(),
            'bbox=-8.0590275,52.9347449,-7.9966939,52.9611999'
        );

        $closed = new Services_OpenStreetMap_Criterion('closed');
        $this->assertEquals($closed->type(), 'closed');
        $this->assertEquals($closed->query(), 'closed');

        $changesets = $osm->searchChangesets(array($user, $bbox, $closed));
        $this->assertInstanceOf('Services_OpenStreetMap_Changesets', $changesets);
    }

    /**
     * Test searching changesets for those by a specific person via
     * display_name and created within a certain timespan.
     *
     * @return void
     */
    public function testTimeAfterOnly()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(
            fopen(
                __DIR__ . '/responses/changeset_search_timespan.xml',
                'rb'
            )
        );

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);

        $time = '17 November 2011';
        $time2 = '29 November 2011';

        $displayName = new Services_OpenStreetMap_Criterion(
            'display_name',
            'kenguest'
        );
        $this->assertEquals($displayName->query(), 'display_name=kenguest');
        $this->assertEquals($displayName->type(), 'display_name');

        $c = new Services_OpenStreetMap_Criterion('time', $time, $time2);
        $this->assertEquals(
            $c->query(),
            'time=2011-11-17T00%3A00%3A00Z%2C2011-11-29T00%3A00%3A00Z'
        );
        $this->assertEquals($c->type(), 'time');

        $changesets = $osm->searchChangesets(array($displayName, $c));
        $this->assertInstanceOf('Services_OpenStreetMap_Changesets', $changesets);
    }

    /**
     * User value must be numeric.
     *
     * @expectedException        Services_OpenStreetMap_InvalidArgumentException
     * @expectedExceptionMessage User UID must be numeric
     *
     * @return void
     */
    public function testInvalidUserValue()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_OpenStreetMap($config);

        $user = new Services_OpenStreetMap_Criterion('user', 'mustbenumeric');
    }
}
// vim:set et ts=4 sw=4:
?>
