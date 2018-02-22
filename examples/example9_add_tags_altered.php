<?php
/**
 * example9_add_tags
 * 28 May 2012
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     osmx.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';
require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';

$osm = new Services_OpenStreetMap();

$mock = new HTTP_Request2_Adapter_Mock();
$mock->addResponse(fopen(__DIR__ . '/../tests/responses/capabilities.xml', 'rb'));
$mock->addResponse(fopen(__DIR__ . '/../tests/responses/changeset.xml', 'rb'));
$mock->addResponse(fopen(__DIR__ . '/../tests/responses/changeset.xml', 'rb'));
$mock->addResponse(fopen(__DIR__ . '/../tests/responses/diff_create_node.xml', 'rb'));
$mock->addResponse(fopen(__DIR__ . '/../tests/responses/changeset_closed', 'rb'));

$config = array(
        'adapter'  => $mock,
        'server'   => 'https://api.openstreetmap.org/',
        'passwordfile' => __DIR__ . '/credentials'
);
$osm = new Services_OpenStreetMap($config);

$osm->loadXML("./ballinacurragardens.osm");
$results = $osm->search(array("building" => "yes"));

$changeset = $osm->createChangeset();
$changeset->begin('Additional details for ballinacurra gardens.');
foreach ($results as $result) {
    $addrStreet = $result->getTag('addr:street');
    if ($addrStreet != 'Oakview Drive') {
        continue;
    }
    $user = $result->getUser();
    $name = $result->getTag('name');
    if ('kenguest' == $user) {
        $tags = $result->getTags();
        if (isset($tags['building_roof'])) {
            continue;
        }
        try {
            $result->setTags(
                array(
                    'building' => 'house',
                    'building:cladding' => 'brick',
                    'building:levels' => '2',
                    'building:roof' => 'tile',
                    'building:roof:shape' => 'flat',
                    'source' => 'survey',
                    'source:geometry' => 'bing',
                    'freeform' => 'Oakview Drive',
                )
            );
            $changeset->add($result);
        } catch (Exception $e) {
            echo  $e->getMessage();
        }
    }
}
var_dump($changeset->getOsmChangeXml());
die;
$changeset->commit();
// vim:set et ts=4 sw=4:
?>
