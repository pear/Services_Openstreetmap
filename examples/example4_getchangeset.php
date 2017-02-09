<?php
/**
 * example4_getchangeset.php
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
try {
    var_dump($osm->getChangeset(2217466));
}
catch (Services_OpenStreetMap_Exception $e) {
    var_dump($e);
}

// vim:set et ts=4 sw=4:
?>
