<?php
/**
 * Unit test class for Relation related functionality.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       RelationTest.php
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
 * Test retrieving relations.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       RelationTest.php
 */
class RelationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test retrieving just one relation.
     *
     * @return void
     */
    public function testGetRelation()
    {
        $id = 1152802;

        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/relation.xml', 'rb'));
        $mock->addResponse(
            fopen(__DIR__ . '/responses/relation_changeset.xml', 'rb')
        );

        $config = array(
            'adapter' => $mock,
            'server'  => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $relation = $osm->getRelation($id);
        $this->assertEquals($id, $relation->getId());
        $changesetId = (int) $relation->getAttributes()->changeset;
        $getTags = $relation->getTags();
        $this->assertEquals($getTags['name'], 'Mitchell Street');
        $this->assertEquals($getTags['type'], 'associatedStreet');

        $changeset = $osm->getChangeset($changesetId);
        $this->assertEquals($changesetId, $changeset->getId());
        $getTags = $changeset->getTags();
        $this->assertEquals($getTags['comment'], 'IE. Nenagh. Mitchell Street POIs');
        $members = $relation->getMembers();

        $this->assertEquals(18, sizeof($members));
        foreach ($members as $member) {
            $this->assertEquals('house', $member['role']);
            $this->assertEquals('way', $member['type']);
            $this->assertTrue(is_numeric($member['ref']));
        }
    }


    /**
     * Test getRelations with ids specified in one array.
     *
     * @return void
     */
    public function testGetRelationsViaArray()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/capabilities.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/relations_917266_20645_2740.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/relation_changeset.xml', 'rb')
        );

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $relations = $osm->getRelations(array(917266,20645,2740));

        $this->assertEquals(3, sizeof($relations));
        $relationsInfo = array(
            array(
                'id' => 2740,
                'name' => 'The Wicklow Way',
                'type' => 'route',
                'members' => array(
                    'role' => '',
                    'count' => 113,
                    'type' => 'node'
                )
            ),

            array(
                'id' => 20645,
                'name' => 'International E-road network',
                'type' => 'network',
                'members' => array(
                    'role' => '',
                    'type'=>'relation',
                    'count' => 48
                )
            ),

            array(
                'id' => 917266,
                'name' => 'Dublin Bus route 14',
                'type' => 'route',
                'members' => array(
                    'role' => 'forward',
                    'type'=> 'way',
                    'count'=>112
                )
            ),
        );
        foreach ($relations as $key=>$relation) {
            $tags = $relation->getTags();
            $members = $relation->getMembers();
            $this->assertEquals($relation->getId(), $relationsInfo[$key]['id']);
            $this->assertEquals($tags['name'], $relationsInfo[$key]['name']);
            $this->assertEquals($tags['type'], $relationsInfo[$key]['type']);
            $this->assertEquals(
                sizeof($members),
                $relationsInfo[$key]['members']['count']
            );
            $this->assertEquals(
                $members[0]['type'],
                $relationsInfo[$key]['members']['type']
            );
            $this->assertEquals(
                $members[0]['role'],
                $relationsInfo[$key]['members']['role']
            );
        }
    }

    /**
     * Test getRelations called with more than one argument/parameter.
     *
     * @return void
     */
    public function testGetRelationsManyArgs()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            fopen(__DIR__ . '/responses/capabilities.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/relations_917266_20645_2740.xml', 'rb')
        );
        $mock->addResponse(
            fopen(__DIR__ . '/responses/relation_changeset.xml', 'rb')
        );

        $config = array(
            'adapter' => $mock,
            'server' => 'http://api06.dev.openstreetmap.org/'
        );
        $osm = new Services_OpenStreetMap($config);
        $relations = $osm->getRelations(917266, 20645, 2740);

        $this->assertEquals(3, sizeof($relations));
        $relationsInfo = array(
            array(
                'id' => 2740,
                'name' => 'The Wicklow Way',
                'type' => 'route',
                'members' => array(
                    'role' => '',
                    'count' => 113,
                    'type' => 'node'
                )
            ),

            array(
                'id' => 20645,
                'name' => 'International E-road network',
                'type' => 'network',
                'members' => array(
                    'role' => '',
                    'type'=>'relation',
                    'count' => 48
                )
            ),

            array(
                'id' => 917266,
                'name' => 'Dublin Bus route 14',
                'type' => 'route',
                'members' => array(
                    'role' => 'forward',
                    'type'=> 'way',
                    'count'=>112
                )
            ),
        );
        foreach ($relations as $key=>$relation) {
            $tags = $relation->getTags();
            $members = $relation->getMembers();
            $this->assertEquals($tags['name'], $relationsInfo[$key]['name']);
            $this->assertEquals($tags['type'], $relationsInfo[$key]['type']);
            $this->assertEquals(
                $relation->getId(),
                $relationsInfo[$key]['id']
            );
            $this->assertEquals(
                sizeof($members),
                $relationsInfo[$key]['members']['count']
            );
            $this->assertEquals(
                $members[0]['type'],
                $relationsInfo[$key]['members']['type']
            );
            $this->assertEquals(
                $members[0]['role'],
                $relationsInfo[$key]['members']['role']
            );
        }
    }
}

?>
