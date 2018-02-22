<?php
require_once 'Services/OpenStreetMap.php';

$id = 2781513612;
$config = array(
        'server'   => 'https://api.openstreetmap.org/',
        'passwordfile' => 'credentials_'
);
try {

$osm = new Services_OpenStreetMap($config);
} catch (Exception $ex) {
        die ($ex->getMessage());
}
try {
    $w = $osm->getNode($id);
}
catch (Services_OpenStreetMap_Exception $e) {
    var_dump($e);
}
$changeset = $osm->createChangeset();
$changeset->begin('Add building=yes to Sentinal Vaults POI');
$result = $w->setTag('building', 'yes');
$changeset->add($result);
try {
$changeset->commit();

} catch (Exception $ex) {
    var_dump($ex);
    echo $ex->xdebug_message;
}
