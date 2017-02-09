<?php
/**
 * example1_savetolocalfile.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     osmx.php
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

$osm = new Services_OpenStreetMap(array('verbose' => true));
var_dump($osm->getConfig());

try {
    $osm->getConfig()->setServer('http://api.openstreetmap.org/');
} catch (Exception $ex) {
    var_dump($ex->getMessage());
    // Fall back to default server...so carry on.
}

$osm->get(
    52.84824191354071, -8.247245026639696,
    52.89957825532213, -8.174161478654796
);
// $osm->get(52.9208049, -8.1156559, 52.9695733, -8.0005314);

file_put_contents("osm.osm", $osm->getXml());
// vim:set et ts=4 sw=4:
?>
