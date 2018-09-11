<?php
/**
 * Unit test class for checking tags with same value.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       https://github.com/kenguest/Services_Openstreetmap/issues/12
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
if (stream_resolve_include_path('PHPUnit/Framework/TestCase.php')) {
    include_once 'PHPUnit/Framework/TestCase.php';
}

/**
 * Unit test class for checking tags with the same value.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       https://github.com/kenguest/Services_Openstreetmap/issues/12
 */
class RemoveTagsTest extends PHPUnit_Framework_TestCase
{

    /**
     * A successful run through making changes to some ways and committing
     * them.
     *
     * @return void
     */
    public function testRemoveOne()
    {
        $osm = new Services_OpenStreetMap();
        $createWith = [
            'name' => 'cafe',
            'amenity' => 'cafe',
            'name:en' => 'cafe',
            'name:uk' => 'cafe'
        ];
        $node = $osm->createNode(0, 0, $createWith);
        $tags = $node->getTags();
        $this->assertEquals($tags, $createWith);
        $node->removeTag("name:uk");
        $tagRemoved = $node->getTags();
        $this->assertEquals(
            $tagRemoved,
            [
            'name' => 'cafe',
            'amenity' => 'cafe',
            'name:en' => 'cafe',
            ]
        );
    }

    public function testRemoveMany()
    {
        $osm = new Services_OpenStreetMap();
        $createWith = [
            'name' => 'cafe',
            'amenity' => 'cafe',
            'name:en' => 'cafe',
            'name:uk' => 'cafe'
        ];
        $node = $osm->createNode(0, 0, $createWith);
        $tags = $node->getTags();
        $this->assertEquals($tags, $createWith);
        $node->removeTags(["name:en", "name:uk"]);
        $tagRemoved = $node->getTags();
        $this->assertEquals(
            $tagRemoved,
            [
                'name' => 'cafe',
                'amenity' => 'cafe',
            ]
        );
    }

    public function testRemoveManyFromNone()
    {
        $osm = new Services_OpenStreetMap();
        $createWith = [];
        $node = $osm->createNode(0, 0, $createWith);
        $tags = $node->getTags();
        $this->assertEquals($tags, $createWith);
        $node->removeTags(["name:en", "name:uk"]);
        $tagRemoved = $node->getTags();
        $this->assertEquals(
            $tagRemoved, []
        );
    }

    public function testRemovingTagViaSetAllTags()
    {
        $osm = new Services_OpenStreetMap();
        $createWith = [
            'name' => 'cafe',
            'amenity' => 'cafe',
            'name:en' => 'cafe',
            'name:uk' => 'cafe'
        ];
        $node = $osm->createNode(0, 0, $createWith);
        $tags = $node->getTags();
        $this->assertEquals($tags, $createWith);
        $newTags = [
            'name' => 'Stop 105',
            'amenity' => 'bus_stop'
        ];
        $node->setAllTags($newTags);
        $this->assertEquals($node->getTags(), $newTags);
    }

    public function testDirtyOrNotWhenTagIsRemovedOrNot()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/node_248081837.xml', 'rb'));

        $config = [
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/',
        ];

        $osm = new Services_OpenStreetMap($config);

        $node = $osm->getNode(248081837);
        $this->assertEquals($node->isDirty(), false);
        $node->removeTag("x-sosm-imaginary");
        $this->assertEquals($node->isDirty(), false);
        $node->removeTag("created_by");
        $this->assertEquals($node->isDirty(), true);
    }
}
// vim:set et ts=4 sw=4:
?>
