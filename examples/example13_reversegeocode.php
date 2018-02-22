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
 * @link     example12_notes.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(
        dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path()
    );
}

require_once 'Services/OpenStreetMap.php';

$osm = new Services_OpenStreetMap();

try {
    $osm->getConfig()->setServer('https://api.openstreetmap.org/');
} catch (Exception $ex) {
    var_dump($ex->getMessage());
    // Fall back to default server...so carry on.
}

var_dump($osm->reverseGeocode("52.9158472", "-8.1755081", 0));
var_dump($osm->reverseGeocode("34.6863136", "94.7452061", 0));
