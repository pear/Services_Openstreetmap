<?php

set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());

$config = array(
    'server' => 'http://open.mapquestapi.com/nominatim/v1/'
);
require_once 'Services/OpenStreetMap.php';
$osm = new Services_OpenStreetMap();
$nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
$nominatim->setServer('mapquest');
$xml = $nominatim
    ->setFormat('xml')
    ->reverseGeocode("53.3459641", "-6.2548149");
var_dump($xml);

$nominatim->setCountryCodes('ie');
$res = $nominatim->search('churchtown');
var_dump($res);

?>
