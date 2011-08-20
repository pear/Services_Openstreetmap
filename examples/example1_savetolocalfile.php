<?php
/**
 * example1_savetolocalfile.php
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

$osm->setServer('http://apidev2.openstreetmap.ie/');

$osm->get(
    52.84824191354071, -8.247245026639696,
    52.89957825532213, -8.174161478654796
);
// $osm->get(52.9208049, -8.1156559, 52.9695733, -8.0005314);

file_put_contents("osm.osm", $osm->getXML());
// vim:set et ts=4 sw=4:
?>
