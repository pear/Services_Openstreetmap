<?php
/**
 * RelationTest.php
 * 29-Sep-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     RelationTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';


class RelationTest extends PHPUnit_Framework_TestCase
{
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
            'server' => 'http://api.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $relation = $osm->getRelation($id);
        $this->assertEquals($id, $relation->getId());
        $changeset_id = (int) $relation->getAttributes()->changeset;
        $getTags = $relation->getTags();
        $this->assertEquals($getTags['name'], 'Mitchell Street');
        $this->assertEquals($getTags['type'], 'associatedStreet');

        $changeset = $osm->getChangeset($changeset_id);
        $this->assertEquals($changeset_id, $changeset->getId());
        $getTags = $changeset->getTags();
        $this->assertEquals($getTags['comment'], 'IE. Nenagh. Mitchell Street POIs');
        $members = $relation->getMembers();

        $this->assertEquals(18, sizeof($members));
        $this->assertEquals('house', $members[0]['role']);
        $this->assertEquals('way', $members[0]['type']);
    }


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
            'server' => 'http://api.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $relations = $osm->getRelations(array(917266,20645,2740));

        $this->assertEquals(3, sizeof($relations));
        $relations_info = array(
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
            $this->assertEquals($relation->getId(), $relations_info[$key]['id']);
            $this->assertEquals($tags['name'], $relations_info[$key]['name']);
            $this->assertEquals($tags['type'], $relations_info[$key]['type']);
            $this->assertEquals(
                sizeof($members),
                $relations_info[$key]['members']['count']
            );
            $this->assertEquals(
                $members[0]['type'],
                $relations_info[$key]['members']['type']
            );
            $this->assertEquals(
                $members[0]['role'],
                $relations_info[$key]['members']['role']
            );
        }
    }

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
            'server' => 'http://api.openstreetmap.org/'
        );
        $osm = new Services_Openstreetmap($config);
        $relations = $osm->getRelations(917266, 20645, 2740);

        $this->assertEquals(3, sizeof($relations));
        $relations_info = array(
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
            $this->assertEquals($tags['name'], $relations_info[$key]['name']);
            $this->assertEquals($tags['type'], $relations_info[$key]['type']);
            $this->assertEquals(
                $relation->getId(),
                $relations_info[$key]['id']
            );
            $this->assertEquals(
                sizeof($members),
                $relations_info[$key]['members']['count']
            );
            $this->assertEquals(
                $members[0]['type'],
                $relations_info[$key]['members']['type']
            );
            $this->assertEquals(
                $members[0]['role'],
                $relations_info[$key]['members']['role']
            );
        }
    }
}

?>
