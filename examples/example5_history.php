<?php
/**
 * example5_history.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     example5_history.php
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

$osm = new Services_OpenStreetMap();
try {
    $w = $osm->getWay(118063652);
    $w = $osm->getWay(24443279);
}
catch (Services_OpenStreetMap_Exception $e) {
    var_dump($e);
}

$all_versions = $w->history();
foreach ($all_versions as $way) {
    var_dump($way->getVersion());
}


// vim:set et ts=4 sw=4:
?>
