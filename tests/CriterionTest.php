<?php
/**
 * Unit test class for Searching through changesets with Criterion objects.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_Openstreetmap
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

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * Unit test class for searching through changesets using Criterion objects.
 *
 * @category   Services
 * @package    Services_Openstreetmap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       CriterionTest.php
 */
class CriterionTest extends PHPUnit_Framework_TestCase
{
    public function testSearchByUser()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_Openstreetmap_Criterion('user', 11324))
        );
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
        $diff = false;
        foreach($changesets as $changeset) {
            if ($changeset->getUid() != 11324) {
                $diff = true;
            }
        }
        $this->assertFalse($diff, 'Unexpected UID present in changeset data');
    }

    /**
     * Search by an unrecognised constraint type ('uid').
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown constraint type
     */
    public function testSearchInvalid()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_Openstreetmap_Criterion('uid', 11324))
        );
    }

    public function testSearchByDisplayName()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $changesets = $osm->searchChangesets(
            array(new Services_Openstreetmap_Criterion('display_name', 'kenguest'))
        );
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
    }

    /**
     * Check that an exception is thrown if attempting to search by both
     * user id and display_name
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Can't supply both user and display_name criteria
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
        $osm = new Services_Openstreetmap($config);
        $display_name = new Services_Openstreetmap_Criterion('display_name', 'kenguest');
        $this->assertEquals($display_name->type(), 'display_name');
        $user = new Services_Openstreetmap_Criterion('user', 11324);
        $changesets = $osm->searchChangesets(array($display_name, $user));
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
    }

    public function testSearchByBbox()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);
        $changesets = $osm->searchChangesets(
            array(
                new Services_Openstreetmap_Criterion('bbox', -8.0590275, 52.9347449, -7.9966939, 52.9611999),
            )
        );
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
    }

    public function testSearchByMultipleCriteria()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changesets_11324.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);

        $user = new Services_Openstreetmap_Criterion('user', 11324);
        $this->assertEquals($user->type(), 'user');
        $this->assertEquals($user->query(), 'user=11324');

        $bbox = new Services_Openstreetmap_Criterion('bbox', -8.0590275, 52.9347449, -7.9966939, 52.9611999);
        $this->assertEquals($bbox->type(), 'bbox');
        $this->assertEquals($bbox->query(), 'bbox=-8.0590275,52.9347449,-7.9966939,52.9611999');

        $closed = new Services_Openstreetmap_Criterion('closed');
        $this->assertEquals($closed->type(), 'closed');
        $this->assertEquals($closed->query(), 'closed');

        $changesets = $osm->searchChangesets( array($user, $bbox, $closed));
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
    }

    public function testTimeAfterOnly()
    {

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/changeset_search_timespan.xml', 'rb'));

        $config = array(
            'adapter'  => $mock,
            'server'   => 'http://api06.dev.openstreetmap.org/',
        );
        $osm = new Services_Openstreetmap($config);

        $time = '17 November 2011';
        $time2 = '29 November 2011';
        $display_name = new Services_Openstreetmap_Criterion('display_name', 'kenguest');
        $c = new Services_Openstreetmap_Criterion('time', $time, $time2);
        $this->assertEquals($c->query(), "time=2011-11-17T00%3A00%3A00Z%2C2011-11-29T00%3A00%3A00Z");
        $changesets = $osm->searchChangesets(array($display_name, $c));
        $this->assertInstanceOf('Services_Openstreetmap_Changesets', $changesets);
   }
}
// vim:set et ts=4 sw=4:
?>
