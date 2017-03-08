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
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
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
class TagsWithSameValueTest extends PHPUnit_Framework_TestCase
{

    /**
     * A successful run through making changes to some ways and committing
     * them.
     *
     * @return void
     */
    public function testChange()
    {
        $osm = new Services_OpenStreetMap();
        $node = $osm->createNode(
            0,
            0,
            [
                'name' => 'cafe',
                'amenity' => 'cafe',
                'name:en' => 'cafe',
                'name:uk' => 'cafe'
            ]
        );
        $xml = ($node->getOsmChangeXml());
        $expected = '<create>'
                  . '<node lat="0" lon="0" version="1" action="create" id="-1">'
                  . '<tag k="name" v="cafe"/><tag k="amenity" v="cafe"/>'
                  . '<tag k="name:en" v="cafe"/>'
                  . '<tag k="name:uk" v="cafe"/>'
                  . '</node>'
                  . '</create>';
        $this->assertEquals($xml, $expected);
    }
}
// vim:set et ts=4 sw=4:
?>
