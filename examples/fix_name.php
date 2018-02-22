<?php
require_once 'Services/OpenStreetMap.php';

$id = 148709920;
$config = array(
        'server'   => 'https://api.openstreetmap.org/',
        'passwordfile' => 'credentials_'
);
$osm = new Services_OpenStreetMap($config);
try {
    $w = $osm->getWay($id);
}
catch (Services_OpenStreetMap_Exception $e) {
    var_dump($e);
}
$changeset = $osm->createChangeset();
$changeset->begin('Fix typo');
$result = $w->setTag('name', 'New Sports Hall');
$changeset->add($result);
$changeset->commit();
