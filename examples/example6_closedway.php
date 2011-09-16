<?php
/**
 * example6_closedway.php
 * 21-Aug-2011
 *
 * PHP Version 5
 *
 * @category example6_closedway
 * @package  example6_closedway
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     example6_closedway.php
 * @todo
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';
$id = 18197393;
$osm = new Services_Openstreetmap();
try {
    $w = $osm->getWay($id);
}
catch (Services_Openstreetmap_Exception $e) {
    var_dump($e);
}
$h = $w->isClosed();
echo "Way $id is ", $h ? 'closed' : 'not closed', "\n";
?>
