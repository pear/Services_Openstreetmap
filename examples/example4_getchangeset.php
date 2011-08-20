<?php
/**
 * example4_getchangeset.php
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

require_once 'Services/Openstreetmap.php';

$osm = new Services_Openstreetmap();
try {
    var_dump($osm->getChangeset(2217466));
}
catch (Services_Openstreetmap_Exception $e) {
    var_dump($e);
}

// vim:set et ts=4 sw=4:
?>
