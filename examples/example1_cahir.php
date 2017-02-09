<?php
/**
 * example1_cahir.php
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

$osm = new Services_OpenStreetMap();
$osm->getConfig()->setServer('http://api.openstreetmap.org/');

try {
    $osm->get(52.821022799999994,-8.3564758, 53.0428644, -7.7330017);

    file_put_contents("homelands.osm", $osm->getXml());
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
}
// vim:set et ts=4 sw=4:
?>
