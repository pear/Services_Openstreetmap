<?php
require_once 'Services/OpenStreetMap.php';

$id = 1707362;
$osm = new Services_OpenStreetMap();
$relation = $osm->getRelation($id);
file_put_contents("relation.$id.xml", $relation->getXml());

?>
