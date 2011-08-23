<?php
/**
 * example3_genericdemo.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  CVS: <cvs_id>
 * @link     osmx.php
 * @todo
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

$osm = new Services_Openstreetmap();

// var_dump($osm->getCoordsOfPlace("Nenagh, Ireland"));
var_dump($osm->getNode(52245107));

// vim:set et ts=4 sw=4:
?>
